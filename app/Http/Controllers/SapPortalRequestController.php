<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PortalAccessRequest;
use App\Models\SponsorshipCampaign;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SapPortalRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboards.sap.portal-requests', [
            ...$this->metrics(),
            'requests' => PortalAccessRequest::query()
                ->with(['user', 'reviewer'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('role'), fn ($query) => $query->where('requested_role', $request->string('role')))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'sponsorships' => SponsorshipCampaign::query()
                ->with(['venue', 'ownerProfile'])
                ->where('status', 'pending')
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    public function decide(Request $request, PortalAccessRequest $portalAccessRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($portalAccessRequest->status !== 'pending') {
            return back()->with('sap_status', 'Cette demande a déjà été traitée.');
        }

        $status = $validated['decision'] === 'approve' ? 'approved' : 'rejected';
        $portalAccessRequest->update([
            'status' => $status,
            'decision_note' => $validated['decision_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($status === 'approved') {
            $portalAccessRequest->user->grantPortal($portalAccessRequest->requested_role);

            if ($portalAccessRequest->requested_role === UserRole::Owner) {
                OwnerProfile::query()->updateOrCreate(
                    ['user_id' => $portalAccessRequest->user_id],
                    [
                        'owner_type' => $portalAccessRequest->applicant_type ?? 'company',
                        'business_name' => $portalAccessRequest->business_name ?? $portalAccessRequest->user->name,
                        'legal_name' => $portalAccessRequest->legal_name,
                        'tax_identifier' => $portalAccessRequest->tax_identifier,
                        'verification_status' => VerificationStatus::Verified,
                        'country_code' => $portalAccessRequest->country_code ?? 'CI',
                        'city' => $portalAccessRequest->city ?? 'Abidjan',
                        'whatsapp_phone' => $portalAccessRequest->whatsapp_phone,
                        'billing_preference' => 'commission',
                        'verified_by' => $request->user()->id,
                        'verified_at' => now(),
                    ],
                );
            }
        }

        return back()->with('sap_status', $status === 'approved' ? 'Accès approuvé.' : 'Demande refusée.');
    }

    public function decideSponsorship(Request $request, SponsorshipCampaign $sponsorshipCampaign): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
        ]);

        $approved = $validated['decision'] === 'approve';
        $sponsorshipCampaign->update([
            'status' => $approved ? 'active' : 'rejected',
            'approved_by' => $approved ? $request->user()->id : null,
            'approved_at' => $approved ? now() : null,
        ]);

        return back()->with('sap_status', 'Campagne sponsorisée mise à jour.');
    }

    /**
     * @return array<string, mixed>
     */
    private function metrics(): array
    {
        return [
            'ownersCount' => OwnerProfile::query()->count(),
            'clientsCount' => User::query()->where('role', UserRole::Client)->orWhereJsonContains('portal_roles', UserRole::Client->value)->count(),
            'publishedVenuesCount' => Venue::query()->where('status', VenueStatus::Published)->count(),
            'pendingAccessRequestsCount' => PortalAccessRequest::query()->where('status', 'pending')->count(),
            'pendingSponsorshipsCount' => SponsorshipCampaign::query()->where('status', 'pending')->count(),
            'grossPaymentsAmount' => (int) Payment::query()->where('status', PaymentStatus::Succeeded)->sum('amount'),
            'activeBookingsCount' => Booking::query()->whereIn('status', [BookingStatus::PendingOwner, BookingStatus::PendingPayment, BookingStatus::Confirmed])->count(),
        ];
    }
}
