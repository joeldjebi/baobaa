<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\ServiceProviderProfile;
use App\Services\EventProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventComposerController extends Controller
{
    public function __construct(private readonly EventProjectService $eventProjectService) {}

    public function create(): View
    {
        $providers = ServiceProviderProfile::query()
            ->with(['services' => fn ($query) => $query
                ->with('type')
                ->where('status', VenueStatus::Published)
                ->orderBy('starting_price')
                ->orderBy('name'),
            ])
            ->whereHas('services', fn ($query) => $query->where('status', VenueStatus::Published))
            ->orderBy('business_name')
            ->limit(12)
            ->get();

        return view('event-composer.create', [
            'providers' => $providers,
            'cities' => EventService::query()
                ->where('status', VenueStatus::Published)
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPortal(UserRole::Client), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:80'],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'client_notes' => ['nullable', 'string', 'max:2000'],
            'event_service_ids' => ['required_without:ticketing_requested', 'array', 'max:12'],
            'event_service_ids.*' => [
                'integer',
                Rule::exists('event_services', 'id')->where('status', VenueStatus::Published->value),
            ],
            'ticketing_requested' => ['nullable', 'boolean'],
        ]);

        $project = $this->eventProjectService->createStandaloneProject(
            $request->user(),
            $validated,
            array_map('intval', $validated['event_service_ids'] ?? []),
            $request->boolean('ticketing_requested'),
        );

        return redirect()
            ->route('client.projects')
            ->with('project_status', "Votre projet {$project->reference} est créé. Les prestataires peuvent maintenant être contactés.");
    }
}
