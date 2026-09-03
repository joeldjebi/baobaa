<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\OwnerProfile;
use App\Models\Venue;
use App\Models\VenueCategory;
use App\Services\VenueImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OwnerVenueDraftController extends Controller
{
    public function __construct(private readonly VenueImageService $venueImageService) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);
        $step = $request->string('step')->toString() ?: 'base';
        $venue = $this->draftVenue($request, $ownerProfile);

        match ($step) {
            'base' => $this->saveBase($request, $venue),
            'details' => $this->saveDetails($request, $venue),
            'inclusions' => $this->saveInclusions($request, $venue),
            'modules' => $this->saveModules($request, $venue),
            'localisation' => $this->saveLocalisation($request, $venue),
            'conditions' => $this->saveConditions($request, $venue),
            default => abort(404),
        };

        $nextUrl = route('owner.venues.edit', [
            'venue' => $venue,
            'step' => $this->nextStep($step),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->successMessage($step),
                'next_step' => $this->nextStep($step),
                'next_url' => $nextUrl,
                'venue_id' => $venue->id,
            ]);
        }

        return redirect()
            ->to($nextUrl)
            ->with('draft_status', $this->successMessage($step));
    }

    private function ownerProfile(Request $request): OwnerProfile
    {
        return $request->user()->ownerProfile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'owner_type' => 'company',
            'business_name' => $request->user()->name,
            'verification_status' => 'pending',
            'country_code' => 'CI',
            'city' => 'Abidjan',
        ]);
    }

    private function draftVenue(Request $request, OwnerProfile $ownerProfile): Venue
    {
        if ($request->filled('venue_id')) {
            return $ownerProfile->venues()->whereKey($request->integer('venue_id'))->firstOrFail();
        }

        $category = VenueCategory::query()->where('is_active', true)->orderBy('sort_order')->firstOrFail();

        return $ownerProfile->venues()->create([
            'venue_category_id' => $category->id,
            'name' => 'Nouvel espace',
            'slug' => 'nouvel-espace-'.Str::lower(Str::random(8)),
            'short_description' => '',
            'description' => '',
            'status' => VenueStatus::Draft,
            'verification_status' => 'pending',
            'booking_mode' => 'request',
            'country_code' => $ownerProfile->country_code,
            'city' => $ownerProfile->city,
            'min_capacity' => 1,
            'max_capacity' => 1,
            'currency' => 'XOF',
            'starting_price' => 0,
            'reservation_amount' => 0,
        ]);
    }

    private function saveBase(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'venue_category_id' => ['required', Rule::exists('venue_categories', 'id')],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'min_capacity' => ['required', 'integer', 'min:1'],
            'max_capacity' => ['required', 'integer', 'gte:min_capacity'],
            'surface_area' => ['nullable', 'integer', 'min:1'],
            'starting_price' => ['required', 'integer', 'min:0'],
            'reservation_amount' => ['nullable', 'integer', 'min:0'],
            'booking_mode' => ['required', Rule::in(['request', 'instant'])],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['string', Rule::in(['baobaa_checkout', 'wave', 'orange_money', 'mtn_money', 'moov_money', 'bank_transfer'])],
        ]);

        $venue->update(Arr::except($validated, ['payment_methods']) + [
            'payment_methods' => array_values(array_unique($validated['payment_methods'] ?? ['baobaa_checkout'])),
            'slug' => $venue->slug === 'nouvel-espace' || str_starts_with($venue->slug, 'nouvel-espace-')
                ? Str::slug($validated['name']).'-'.Str::lower(Str::random(5))
                : $venue->slug,
        ]);
    }

    private function saveDetails(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'highlights' => ['nullable', 'string', 'max:1200'],
            'configurations' => ['nullable', 'array'],
            'configurations.*.name' => ['nullable', 'string', 'max:80'],
            'configurations.*.capacity' => ['nullable', 'integer', 'min:1'],
            'media_images' => ['nullable', 'array', 'max:12'],
            'media_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'media_videos' => ['nullable', 'array', 'max:4'],
            'media_videos.*' => ['file', 'mimes:mp4,mov,webm', 'max:102400'],
        ]);

        $venue->update([
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'highlights' => $this->lines($validated['highlights'] ?? ''),
        ]);

        $venue->configurations()->delete();
        foreach ($validated['configurations'] ?? [] as $index => $configuration) {
            if (! filled($configuration['name'] ?? null)) {
                continue;
            }

            $venue->configurations()->create([
                'name' => $configuration['name'],
                'capacity' => $configuration['capacity'] ?? $venue->max_capacity,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }

        $hasPrimaryImage = $venue->media()
            ->where('type', 'image')
            ->where('is_primary', true)
            ->exists();

        foreach ($request->file('media_images', []) as $index => $image) {
            $this->venueImageService->storeImage(
                $venue,
                $image,
                ! $hasPrimaryImage && $index === 0,
                $venue->name,
            );
        }

        foreach ($request->file('media_videos', []) as $video) {
            $this->venueImageService->storeVideo($venue, $video, $venue->name);
        }
    }

    private function saveInclusions(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'included_items' => ['nullable', 'array'],
            'included_items.*' => ['nullable', 'string', 'max:120'],
            'amenities' => ['nullable', 'array'],
            'amenities.*.name' => ['nullable', 'string', 'max:120'],
            'amenities.*.detail' => ['nullable', 'string', 'max:500'],
        ]);

        $venue->update([
            'included_items' => $this->values($validated['included_items'] ?? []),
            'space_details' => [
                'amenities' => collect($validated['amenities'] ?? [])
                    ->filter(fn (array $amenity): bool => filled($amenity['name'] ?? null))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    private function saveModules(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'module_template_ids' => ['nullable', 'array'],
            'module_template_ids.*' => ['integer'],
        ]);
        $ownerProfile = $this->ownerProfile($request);
        $templates = $ownerProfile->moduleTemplates()
            ->whereIn('id', $validated['module_template_ids'] ?? [])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $venue->addOns()->delete();
        foreach ($templates as $index => $module) {
            $venue->addOns()->create([
                'name' => $module->name,
                'description' => $module->description,
                'price' => $module->price,
                'currency' => $venue->currency,
                'is_available' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function saveLocalisation(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'location_details' => ['nullable', 'string', 'max:1200'],
            'available_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
        ]);

        $venue->update([
            'address' => $validated['address'] ?? null,
            'location_details' => ['public_note' => $validated['location_details'] ?? ''],
        ]);

        if (filled($validated['available_date'] ?? null) && filled($validated['starts_at'] ?? null) && filled($validated['ends_at'] ?? null)) {
            $venue->availabilities()->updateOrCreate([
                'available_date' => $validated['available_date'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
            ], [
                'status' => 'available',
            ]);
        }
    }

    private function saveConditions(Request $request, Venue $venue): void
    {
        $validated = $request->validate([
            'house_rules' => ['nullable', 'array'],
            'house_rules.*' => ['nullable', 'string', 'max:180'],
            'policies' => ['nullable', 'array'],
            'policies.*.title' => ['nullable', 'string', 'max:120'],
            'policies.*.summary' => ['nullable', 'string', 'max:160'],
            'policies.*.content' => ['nullable', 'string', 'max:1200'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string', 'max:1200'],
        ]);

        $venue->update([
            'house_rules' => $this->values($validated['house_rules'] ?? []),
        ]);

        $venue->policies()->delete();
        foreach ($validated['policies'] ?? [] as $index => $policy) {
            if (! filled($policy['title'] ?? null)) {
                continue;
            }

            $venue->policies()->create([
                'policy_type' => Str::slug($policy['title']),
                'title' => $policy['title'],
                'summary' => $policy['summary'] ?? '',
                'content' => $policy['content'] ?? '',
                'is_highlighted' => $index < 3,
                'sort_order' => $index + 1,
            ]);
        }

        $venue->faqs()->delete();
        foreach ($validated['faqs'] ?? [] as $index => $faq) {
            if (! filled($faq['question'] ?? null)) {
                continue;
            }

            $venue->faqs()->create([
                'question' => $faq['question'],
                'answer' => $faq['answer'] ?? '',
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string|null>  $values
     * @return array<int, string>
     */
    private function values(array $values): array
    {
        return collect($values)
            ->map(fn (?string $value): string => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function nextStep(string $step): string
    {
        return Arr::get([
            'base' => 'details',
            'details' => 'inclusions',
            'inclusions' => 'modules',
            'modules' => 'localisation',
            'localisation' => 'conditions',
            'conditions' => 'conditions',
        ], $step, 'base');
    }

    private function successMessage(string $step): string
    {
        return $step === 'conditions'
            ? 'Espace enregistré avec succès.'
            : 'Étape enregistrée avec succès. Vous pouvez continuer.';
    }
}
