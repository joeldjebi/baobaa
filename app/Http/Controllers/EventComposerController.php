<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\ManagedLocation;
use App\Models\PlatformSetting;
use App\Models\ServiceProviderProfile;
use App\Services\AvailabilityHoldService;
use App\Services\EventProjectService;
use App\Services\LocationOptionsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventComposerController extends Controller
{
    public function __construct(
        private readonly EventProjectService $eventProjectService,
        private readonly AvailabilityHoldService $availabilityHoldService,
        private readonly LocationOptionsService $locationOptionsService,
    ) {}

    public function create(Request $request): View
    {
        $eventTypeOptions = $this->eventTypeOptions();
        $countryOptions = $this->locationOptionsService->countries();
        $cityOptions = $this->locationOptionsService->cities();
        $districtOptions = $this->locationOptionsService->districts();

        $providers = ServiceProviderProfile::query()
            ->publiclyListed()
            ->with(['services' => fn ($query) => $query
                ->with(['type', 'media'])
                ->where('status', VenueStatus::Published)
                ->orderBy('starting_price')
                ->orderBy('name'),
            ])
            ->whereHas('services', fn ($query) => $query->where('status', VenueStatus::Published))
            ->orderBy('business_name')
            ->limit(12)
            ->get();

        $selectedProviderId = $providers->first()?->id;

        if ($request->filled('prestataire')) {
            $selectedProviderId = $providers
                ->firstWhere('public_uuid', $request->string('prestataire')->toString())
                ?->id ?? $selectedProviderId;
        }

        // Load SAP-managed platform settings for defaults
        $platformSettings = PlatformSetting::query()
            ->whereIn('key', ['platform.default_country', 'platform.default_city', 'platform.default_district'])
            ->get()
            ->pluck('value', 'key')
            ->all();

        return view('event-composer.create', [
            'providers' => $providers,
            'selectedProviderId' => $selectedProviderId,
            'eventTypeOptions' => $eventTypeOptions,
            'countryOptions' => $countryOptions,
            'cityOptions' => $cityOptions,
            'districtOptions' => $districtOptions,
            // allow prefilling from query params: event_type, country_code, city, district
            'defaultEventType' => $this->optionValue($request->input('event_type'), array_keys($eventTypeOptions)),
            'defaultCountry' => $this->optionValue($request->input('country_code') ?? ($platformSettings['platform.default_country'] ?? null), array_keys($countryOptions), 'CI'),
            'defaultCity' => $this->optionValue($request->input('city') ?? ($platformSettings['platform.default_city'] ?? null), $cityOptions),
            'defaultDistrict' => $this->optionValue($request->input('district') ?? ($platformSettings['platform.default_district'] ?? null), $districtOptions),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPortal(UserRole::Client), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'accept_terms' => ['accepted'],
            'event_type' => ['nullable', 'string', 'max:80', Rule::in(array_keys($this->eventTypeOptions()))],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'country_code' => ['required', 'string', 'size:2', Rule::in(array_keys($this->locationOptionsService->countries()))],
            'city' => [
                'nullable',
                'string',
                'max:255',
                Rule::in($this->activeCityNames($request->input('country_code'))),
            ],
            'district' => [
                'nullable',
                'string',
                'max:255',
                Rule::in($this->activeDistrictNames($request->input('city'))),
            ],
            'client_notes' => ['nullable', 'string', 'max:2000'],
            'event_service_ids' => ['required_without:ticketing_requested', 'array', 'max:12'],
            'event_service_ids.*' => [
                'integer',
                Rule::exists('event_services', 'id')
                    ->where('status', VenueStatus::Published->value)
                    ->whereIn('service_provider_profile_id', ServiceProviderProfile::query()->publiclyListed()->pluck('id')->all()),
            ],
            'ticketing_requested' => ['nullable', 'boolean'],
        ]);

        $eventServiceIds = array_map('intval', $validated['event_service_ids'] ?? []);
        [$slotStartsAt, $slotEndsAt] = filled($validated['event_date'] ?? null)
            ? $this->availabilityHoldService->slotFromDateAndTimes(
                $validated['event_date'],
                $validated['event_date'],
                $validated['starts_at'] ?? null,
                $validated['ends_at'] ?? null,
            )
            : [null, null];

        $project = $this->availabilityHoldService->reserveEventServiceSlots(
            $eventServiceIds,
            $request->user(),
            $slotStartsAt,
            $slotEndsAt,
            $request->session()->getId(),
            fn () => $this->eventProjectService->createStandaloneProject(
                $request->user(),
                $validated,
                $eventServiceIds,
                $request->boolean('ticketing_requested'),
            ),
        );

        return redirect()
            ->route('client.projects.show', $project)
            ->with('project_status', "Votre projet {$project->reference} est créé. Les prestataires vont vous envoyer leur devis : vous pouvez leur écrire ci-dessous.");
    }

    /**
     * @return array<string, string>
     */
    private function eventTypeOptions(): array
    {
        return [
            'conference' => 'Conférence / Séminaire',
            'mariage' => 'Mariage / Célébration',
            'concert' => 'Concert / Spectacle',
            'lancement' => 'Lancement de produit',
            'gala' => 'Gala / Soirée privée',
            'anniversaire' => 'Anniversaire',
            'formation' => 'Formation / Atelier',
            'salon' => 'Salon / Exposition',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function activeCityNames(?string $countryCode): array
    {
        return ManagedLocation::query()
            ->active()
            ->cities()
            ->whereHas('parent', fn (Builder $query) => $query->active()->countries())
            ->when(filled($countryCode), fn (Builder $query) => $query->where('country_code', strtoupper((string) $countryCode)))
            ->pluck('name')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function activeDistrictNames(?string $city): array
    {
        return ManagedLocation::query()
            ->active()
            ->districts()
            ->whereHas('parent', fn (Builder $query) => $query
                ->active()
                ->cities()
                ->when(filled($city), fn (Builder $query) => $query->where('name', (string) $city))
                ->whereHas('parent', fn (Builder $query) => $query->active()->countries()))
            ->pluck('name')
            ->all();
    }

    /**
     * @param  array<int|string, string|array<string, string>>  $allowedValues
     */
    private function optionValue(mixed $value, array $allowedValues, ?string $fallback = null): ?string
    {
        if (! is_string($value) || $value === '') {
            return $fallback;
        }

        if (array_is_list($allowedValues) && isset($allowedValues[0]) && is_array($allowedValues[0])) {
            $allowedValues = collect($allowedValues)->pluck('name')->all();
        }

        return in_array($value, $allowedValues, true) ? $value : $fallback;
    }
}
