<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\ServiceProviderProfile;
use App\Services\LocationOptionsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderDeliveryZoneController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.delivery-zones', [
            ...app(ServiceProviderDashboardController::class)->metricsFor($profile),
            'profile' => $profile,
            'zones' => $profile->deliveryZones()->orderBy('city')->get(),
            'cityOptions' => app(LocationOptionsService::class)->cities(),
        ]);
    }

    /**
     * Create or update the flat delivery fee for one city.
     */
    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);

        $validated = $request->validate([
            'city' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'integer', 'min:0', 'max:10000000'],
        ]);

        $profile->deliveryZones()->updateOrCreate(
            ['city' => trim($validated['city'])],
            ['fee' => $validated['fee'], 'currency' => 'XOF', 'is_active' => true],
        );

        return back()->with('pse_status', 'Tarif de livraison enregistré pour '.trim($validated['city']).'.');
    }

    public function destroy(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        abort_unless((int) $deliveryZone->service_provider_profile_id === (int) $this->profile($request)->id, 404);

        $deliveryZone->delete();

        return back()->with('pse_status', 'Ville de livraison retirée.');
    }

    private function profile(Request $request): ServiceProviderProfile
    {
        return $request->user()->serviceProviderProfile()->firstOrFail();
    }
}
