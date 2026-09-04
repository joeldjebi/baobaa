<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use App\Services\PartnerLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class EventServiceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $validated = $this->validated($request);
        $type = EventServiceType::query()->where('is_active', true)->findOrFail($validated['event_service_type_id']);

        $service = $profile->services()->create([
            ...Arr::except($validated, ['attributes_text', 'availability_notes_text']),
            'event_service_type_id' => $type->id,
            'country_code' => strtoupper($validated['country_code']),
            'status' => $validated['status'] === VenueStatus::Published->value ? VenueStatus::Published : VenueStatus::Draft,
            'published_at' => $validated['status'] === VenueStatus::Published->value ? now() : null,
            'attributes' => $this->linesToKeyValue($validated['attributes_text'] ?? ''),
            'availability_notes' => $this->linesToArray($validated['availability_notes_text'] ?? ''),
        ]);

        return redirect()->route('service-provider.services.edit', $service)->with('pse_status', 'Service enregistré.');
    }

    public function update(Request $request, EventService $eventService): RedirectResponse
    {
        $profile = $this->profile($request);
        abort_unless((int) $eventService->service_provider_profile_id === (int) $profile->id, 404);

        $validated = $this->validated($request);

        $eventService->update([
            ...Arr::except($validated, ['attributes_text', 'availability_notes_text']),
            'country_code' => strtoupper($validated['country_code']),
            'status' => $validated['status'] === VenueStatus::Published->value ? VenueStatus::Published : VenueStatus::Draft,
            'published_at' => $validated['status'] === VenueStatus::Published->value ? ($eventService->published_at ?: now()) : null,
            'attributes' => $this->linesToKeyValue($validated['attributes_text'] ?? ''),
            'availability_notes' => $this->linesToArray($validated['availability_notes_text'] ?? ''),
        ]);

        return back()->with('pse_status', 'Service mis à jour.');
    }

    public function toggle(Request $request, EventService $eventService): RedirectResponse
    {
        $profile = $this->profile($request);
        abort_unless((int) $eventService->service_provider_profile_id === (int) $profile->id, 404);

        $publishing = $eventService->status !== VenueStatus::Published;

        $eventService->update([
            'status' => $publishing ? VenueStatus::Published : VenueStatus::Draft,
            'published_at' => $publishing ? now() : null,
        ]);

        return back()->with('pse_status', $publishing ? 'Service publié.' : 'Service repassé en brouillon.');
    }

    public function destroy(Request $request, EventService $eventService): RedirectResponse
    {
        $profile = $this->profile($request);
        abort_unless((int) $eventService->service_provider_profile_id === (int) $profile->id, 404);

        $eventService->delete();

        return redirect()->route('service-provider.services')->with('pse_status', 'Service supprimé.');
    }

    public function updateSettings(Request $request, PartnerLogoService $logoService): RedirectResponse
    {
        $profile = $this->profile($request);
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'whatsapp_phone' => ['nullable', 'string', 'max:32'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1200'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $profile->update([
            ...Arr::except($validated, ['logo']),
            'country_code' => strtoupper($validated['country_code']),
        ]);

        if ($request->hasFile('logo')) {
            $logoService->store($profile, $request->file('logo'), $profile->business_name);
        }

        return back()->with('pse_status', 'Paramètres enregistrés.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'event_service_type_id' => ['required', 'integer', 'exists:event_service_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:4000'],
            'status' => ['required', Rule::in([VenueStatus::Draft->value, VenueStatus::Published->value])],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'pricing_unit' => ['required', Rule::in(['event', 'day', 'hour'])],
            'starting_price' => ['required', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'integer', 'min:0', 'lte:starting_price'],
            'attributes_text' => ['nullable', 'string', 'max:2000'],
            'availability_notes_text' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function profile(Request $request): ServiceProviderProfile
    {
        return $request->user()->serviceProviderProfile()->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    private function linesToKeyValue(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->mapWithKeys(function (string $line): array {
                [$key, $value] = array_pad(explode(':', $line, 2), 2, 'Oui');

                return [trim($key) => trim($value)];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function linesToArray(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
