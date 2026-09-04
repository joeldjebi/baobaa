<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\OwnerProfile;
use App\Models\PortalAccessRequest;
use App\Models\ServiceProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalAccessController extends Controller
{
    public function becomeClient(Request $request): RedirectResponse
    {
        if ($request->user()->hasPortal(UserRole::Client)) {
            return redirect()->route('client.dashboard');
        }

        $this->createPendingRequest($request, UserRole::Client);

        return back()->with('portal_status', 'Votre demande d’accès client a été transmise au SAP.');
    }

    public function becomeOwner(Request $request): RedirectResponse
    {
        if ($request->user()->hasPortal(UserRole::Owner)) {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->route('portals.owner.request.form');
    }

    public function becomeServiceProvider(Request $request): RedirectResponse
    {
        if ($request->user()->hasPortal(UserRole::ServiceProvider)) {
            return redirect()->route('service-provider.dashboard');
        }

        return redirect()->route('portals.service-provider.request.form');
    }

    public function ownerApplicationForm(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasPortal(UserRole::Owner)) {
            return redirect()->route('owner.dashboard');
        }

        return view('auth.owner-register', [
            'mode' => 'authenticated',
            'ownerProfile' => $request->user()->ownerProfile,
            'pendingRequest' => $request->user()->portalAccessRequests()
                ->where('requested_role', UserRole::Owner)
                ->where('status', 'pending')
                ->latest()
                ->first(),
        ]);
    }

    public function requestOwnerAccess(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasPortal(UserRole::Owner)) {
            return redirect()->route('owner.dashboard');
        }

        $validated = $request->validate([
            'applicant_type' => ['required', 'in:individual,company'],
            'business_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'whatsapp_phone' => ['required', 'string', 'max:32'],
            'motivation' => ['nullable', 'string', 'max:1200'],
        ]);

        OwnerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'owner_type' => $validated['applicant_type'],
                'business_name' => $validated['business_name'],
                'legal_name' => $validated['legal_name'] ?? $validated['business_name'],
                'tax_identifier' => $validated['tax_identifier'] ?? null,
                'verification_status' => VerificationStatus::Pending,
                'country_code' => strtoupper($validated['country_code']),
                'city' => $validated['city'],
                'whatsapp_phone' => $validated['whatsapp_phone'],
                'billing_preference' => 'commission',
            ],
        );

        $this->createPendingRequest($request, UserRole::Owner, $validated);

        return redirect()->route('client.dashboard')->with('portal_status', 'Votre dossier partenaire est envoyé au SAP pour validation.');
    }

    public function serviceProviderApplicationForm(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasPortal(UserRole::ServiceProvider)) {
            return redirect()->route('service-provider.dashboard');
        }

        return view('auth.service-provider-register', [
            'mode' => 'authenticated',
            'serviceProviderProfile' => $request->user()->serviceProviderProfile,
            'pendingRequest' => $request->user()->portalAccessRequests()
                ->where('requested_role', UserRole::ServiceProvider)
                ->where('status', 'pending')
                ->latest()
                ->first(),
        ]);
    }

    public function requestServiceProviderAccess(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasPortal(UserRole::ServiceProvider)) {
            return redirect()->route('service-provider.dashboard');
        }

        $validated = $this->validateServiceProviderPayload($request);

        ServiceProviderProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $validated['business_name'],
                'legal_name' => $validated['legal_name'] ?? $validated['business_name'],
                'tax_identifier' => $validated['tax_identifier'] ?? null,
                'verification_status' => VerificationStatus::Pending,
                'country_code' => strtoupper($validated['country_code']),
                'city' => $validated['city'],
                'district' => $validated['district'] ?? null,
                'whatsapp_phone' => $validated['whatsapp_phone'],
                'service_area' => $validated['service_area'],
                'description' => $validated['description'] ?? null,
                'billing_preference' => 'commission',
            ],
        );

        $this->createPendingRequest($request, UserRole::ServiceProvider, $validated);

        return redirect()->route('client.dashboard')->with('portal_status', 'Votre dossier prestataire événementiel est envoyé au SAP pour validation.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateServiceProviderPayload(Request $request): array
    {
        return $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone' => ['required', 'string', 'max:32'],
            'service_area' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1200'],
            'motivation' => ['nullable', 'string', 'max:1200'],
        ]);
    }

    private function createPendingRequest(Request $request, UserRole $role, array $payload = []): PortalAccessRequest
    {
        return PortalAccessRequest::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'requested_role' => $role,
                'status' => 'pending',
            ],
            [
                'applicant_type' => $payload['applicant_type'] ?? null,
                'business_name' => $payload['business_name'] ?? null,
                'legal_name' => $payload['legal_name'] ?? null,
                'tax_identifier' => $payload['tax_identifier'] ?? null,
                'country_code' => isset($payload['country_code']) ? strtoupper((string) $payload['country_code']) : null,
                'city' => $payload['city'] ?? null,
                'whatsapp_phone' => $payload['whatsapp_phone'] ?? null,
                'motivation' => $payload['motivation'] ?? null,
            ],
        );
    }
}
