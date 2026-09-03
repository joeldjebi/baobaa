<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\CommissionRule;
use App\Models\OwnerDepositRule;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PortalAccessRequest;
use App\Models\SponsorshipCampaign;
use App\Models\SponsorshipPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SapDashboardController extends Controller
{
    public function overview(): View
    {
        return view('dashboards.sap.index', [
            ...$this->metrics(),
            'recentAccessRequests' => PortalAccessRequest::query()->with('user')->latest()->limit(6)->get(),
            'recentBookings' => Booking::query()->with(['venue', 'client'])->latest()->limit(6)->get(),
            'monthlyRevenue' => $this->monthlyRevenue(),
        ]);
    }

    public function owners(Request $request): View
    {
        return view('dashboards.sap.owners', [
            ...$this->metrics(),
            'owners' => OwnerProfile::query()
                ->with('user')
                ->withCount(['venues', 'sponsorshipCampaigns'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('verification_status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where('business_name', 'like', $search)
                        ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', $search)->orWhere('name', 'like', $search));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function clients(Request $request): View
    {
        return view('dashboards.sap.clients', [
            ...$this->metrics(),
            'clients' => User::query()
                ->withCount(['bookings', 'payments'])
                ->where(function (Builder $query): void {
                    $query->where('role', UserRole::Client)
                        ->orWhereJsonContains('portal_roles', UserRole::Client->value);
                })
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function venues(Request $request): View
    {
        return view('dashboards.sap.venues', [
            ...$this->metrics(),
            'venues' => Venue::query()
                ->with(['ownerProfile', 'category'])
                ->withCount(['bookings', 'sponsorshipCampaigns'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where('name', 'like', $search)
                        ->orWhereHas('ownerProfile', fn (Builder $query) => $query->where('business_name', 'like', $search));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function bookings(Request $request): View
    {
        return view('dashboards.sap.bookings', [
            ...$this->metrics(),
            'bookings' => Booking::query()
                ->with(['venue.ownerProfile', 'client', 'payments'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where('reference', 'like', $search)
                        ->orWhereHas('venue', fn (Builder $query) => $query->where('name', 'like', $search))
                        ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', $search));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function payments(Request $request): View
    {
        return view('dashboards.sap.payments', [
            ...$this->metrics(),
            'payments' => Payment::query()
                ->with(['booking.venue.ownerProfile', 'payer'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where('reference', 'like', $search)
                        ->orWhere('provider_reference', 'like', $search)
                        ->orWhereHas('booking', fn (Builder $query) => $query->where('reference', 'like', $search))
                        ->orWhereHas('booking.venue', fn (Builder $query) => $query->where('name', 'like', $search))
                        ->orWhereHas('payer', fn (Builder $query) => $query->where('name', 'like', $search));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function subscriptionPlans(Request $request): View
    {
        return view('dashboards.sap.subscription-plans', [
            ...$this->metrics(),
            'plans' => SubscriptionPlan::query()
                ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
                ->orderBy('sort_order')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeSubscriptionPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'billing_period' => ['required', 'in:monthly,yearly'],
            'active_venues_limit' => ['nullable', 'integer', 'min:1'],
            'reduced_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'visibility_boost_level' => ['required', 'integer', 'min:0', 'max:10'],
            'features' => ['nullable', 'string', 'max:1000'],
        ]);

        SubscriptionPlan::query()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'currency' => 'XOF',
            'features' => $this->linesToArray($validated['features'] ?? ''),
            'is_active' => true,
            'sort_order' => SubscriptionPlan::query()->count() + 1,
        ]);

        return back()->with('sap_status', 'Forfait abonnement ajouté.');
    }

    public function toggleSubscriptionPlan(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update(['is_active' => ! $subscriptionPlan->is_active]);

        return back()->with('sap_status', 'Forfait abonnement mis à jour.');
    }

    public function commissions(Request $request): View
    {
        return view('dashboards.sap.commissions', [
            ...$this->metrics(),
            'categories' => VenueCategory::query()->orderBy('name')->get(['id', 'name']),
            'owners' => OwnerProfile::query()->orderBy('business_name')->get(['id', 'business_name']),
            'rules' => CommissionRule::query()
                ->with(['venueCategory', 'ownerProfile'])
                ->when($request->filled('scope'), fn (Builder $query) => $query->where('scope', $request->string('scope')))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeCommission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'in:global,category,owner'],
            'venue_category_id' => ['nullable', 'integer'],
            'owner_profile_id' => ['nullable', 'integer'],
            'commission_type' => ['required', 'in:percentage,fixed,hybrid'],
            'percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        CommissionRule::query()->create([
            ...$validated,
            'currency' => 'XOF',
            'is_active' => true,
            'starts_at' => now(),
        ]);

        return back()->with('sap_status', 'Règle de commission ajoutée.');
    }

    public function toggleCommission(CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->update(['is_active' => ! $commissionRule->is_active]);

        return back()->with('sap_status', 'Règle de commission mise à jour.');
    }

    public function depositRules(Request $request): View
    {
        return view('dashboards.sap.deposit-rules', [
            ...$this->metrics(),
            'owners' => OwnerProfile::query()->orderBy('business_name')->get(['id', 'business_name', 'city']),
            'rules' => OwnerDepositRule::query()
                ->with('ownerProfile')
                ->when($request->filled('owner_profile_id'), fn (Builder $query) => $query->where('owner_profile_id', $request->integer('owner_profile_id')))
                ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeDepositRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_profile_id' => ['required', 'integer', 'exists:owner_profiles,id'],
            'name' => ['required', 'string', 'max:255'],
            'deposit_type' => ['required', 'in:percentage,fixed'],
            'percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:deposit_type,percentage'],
            'fixed_amount' => ['nullable', 'integer', 'min:0', 'required_if:deposit_type,fixed'],
            'minimum_amount' => ['nullable', 'integer', 'min:0'],
            'maximum_amount' => ['nullable', 'integer', 'min:0', 'gte:minimum_amount'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        OwnerDepositRule::query()
            ->where('owner_profile_id', $validated['owner_profile_id'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        OwnerDepositRule::query()->create([
            ...$validated,
            'minimum_amount' => $validated['minimum_amount'] ?? 0,
            'currency' => 'XOF',
            'is_active' => true,
            'starts_at' => $validated['starts_at'] ?? now(),
        ]);

        return back()->with('sap_status', 'Règle d’acompte ajoutée pour ce partenaire.');
    }

    public function toggleDepositRule(OwnerDepositRule $ownerDepositRule): RedirectResponse
    {
        if (! $ownerDepositRule->is_active) {
            OwnerDepositRule::query()
                ->where('owner_profile_id', $ownerDepositRule->owner_profile_id)
                ->where('is_active', true)
                ->whereKeyNot($ownerDepositRule->id)
                ->update(['is_active' => false]);
        }

        $ownerDepositRule->update(['is_active' => ! $ownerDepositRule->is_active]);

        return back()->with('sap_status', 'Règle d’acompte mise à jour.');
    }

    public function sponsorshipPlans(Request $request): View
    {
        return view('dashboards.sap.sponsorship-plans', [
            ...$this->metrics(),
            'plans' => SponsorshipPlan::query()
                ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
                ->orderBy('sort_order')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeSponsorshipPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'placement' => ['required', 'in:home_featured,catalog_top,category_boost'],
            'price' => ['required', 'integer', 'min:1'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:1000'],
            'features' => ['nullable', 'string', 'max:1000'],
        ]);

        SponsorshipPlan::query()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'currency' => 'XOF',
            'features' => $this->linesToArray($validated['features'] ?? ''),
            'is_active' => true,
            'sort_order' => SponsorshipPlan::query()->count() + 1,
        ]);

        return back()->with('sap_status', 'Forfait sponsoring ajouté.');
    }

    public function toggleSponsorshipPlan(SponsorshipPlan $sponsorshipPlan): RedirectResponse
    {
        $sponsorshipPlan->update(['is_active' => ! $sponsorshipPlan->is_active]);

        return back()->with('sap_status', 'Forfait sponsoring mis à jour.');
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,suspended'],
        ]);

        $user->update(['status' => UserStatus::from($validated['status'])]);

        return back()->with('sap_status', 'Statut utilisateur mis à jour.');
    }

    public function updateVenueStatus(Request $request, Venue $venue): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:published,suspended,rejected'],
        ]);

        $venue->update([
            'status' => VenueStatus::from($validated['status']),
            'approved_by' => $validated['status'] === 'published' ? $request->user()->id : $venue->approved_by,
            'approved_at' => $validated['status'] === 'published' ? now() : $venue->approved_at,
            'published_at' => $validated['status'] === 'published' ? now() : $venue->published_at,
        ]);

        return back()->with('sap_status', 'Statut de l’espace mis à jour.');
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled,completed,disputed'],
        ]);

        $booking->update([
            'status' => BookingStatus::from($validated['status']),
            'confirmed_at' => $validated['status'] === 'confirmed' ? now() : $booking->confirmed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : $booking->cancelled_at,
        ]);

        return back()->with('sap_status', 'Statut de la réservation mis à jour.');
    }

    public function updatePaymentStatus(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:succeeded,failed,refunded'],
        ]);

        $payment->update([
            'status' => PaymentStatus::from($validated['status']),
            'paid_at' => $validated['status'] === 'succeeded' ? now() : $payment->paid_at,
        ]);

        return back()->with('sap_status', 'Statut du paiement mis à jour.');
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

    /**
     * @return array<int, array{label: string, amount: int}>
     */
    private function monthlyRevenue(): array
    {
        return collect(range(5, 0))->map(function (int $monthsAgo): array {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => ucfirst($date->translatedFormat('M')),
                'amount' => (int) Payment::query()
                    ->where('status', PaymentStatus::Succeeded)
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->sum('amount'),
            ];
        })->values()->all();
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
