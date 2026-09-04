<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderDashboardController extends Controller
{
    public function overview(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.index', [
            ...$this->metrics($profile),
            'profile' => $profile,
            'recentServices' => $profile->services()->with('type')->latest()->limit(6)->get(),
            'serviceTypesCount' => EventServiceType::query()->where('is_active', true)->count(),
        ]);
    }

    public function services(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.services', [
            ...$this->metrics($profile),
            'profile' => $profile,
            'services' => $profile->services()
                ->with('type')
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('type_id'), fn (Builder $query) => $query->where('event_service_type_id', $request->integer('type_id')))
                ->when($request->filled('q'), fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'types' => EventServiceType::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function serviceForm(Request $request, ?EventService $eventService = null): View
    {
        $profile = $this->profile($request);

        if ($eventService) {
            abort_unless((int) $eventService->service_provider_profile_id === (int) $profile->id, 404);
        }

        return view('dashboards.service-provider.service-form', [
            ...$this->metrics($profile),
            'profile' => $profile,
            'service' => $eventService,
            'types' => EventServiceType::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function settings(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.settings', [
            ...$this->metrics($profile),
            'profile' => $profile,
        ]);
    }

    public function metricsFor(ServiceProviderProfile $profile): array
    {
        return $this->metrics($profile);
    }

    private function profile(Request $request): ServiceProviderProfile
    {
        return $request->user()->serviceProviderProfile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'business_name' => $request->user()->name,
            'verification_status' => 'pending',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'billing_preference' => 'commission',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function metrics(ServiceProviderProfile $profile): array
    {
        return [
            'activeServicesCount' => $profile->services()->where('status', VenueStatus::Published)->count(),
            'draftServicesCount' => $profile->services()->where('status', VenueStatus::Draft)->count(),
            'requestsCount' => 0,
            'grossRevenue' => 0,
        ];
    }
}
