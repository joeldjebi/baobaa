<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\SponsorshipCampaign;
use App\Models\SponsorshipPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class OwnerSponsorshipController extends Controller
{
    public function index(Request $request): View
    {
        $dashboard = app(DashboardController::class);
        $ownerProfile = $request->user()->ownerProfile()->firstOrFail();

        return view('dashboards.owner.sponsorships', [
            ...$dashboard->ownerMetricsFor($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'venuesForSponsoring' => $ownerProfile->venues()
                ->where('status', VenueStatus::Published)
                ->orderBy('name')
                ->get(['id', 'name', 'city', 'district', 'currency']),
            'sponsorshipPlans' => SponsorshipPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
            'campaigns' => $ownerProfile->sponsorshipCampaigns()
                ->with(['venue', 'sponsorshipPlan'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->latest()
                ->paginate(8)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile()->firstOrFail();
        $validated = $request->validate([
            'venue_id' => ['required', 'integer'],
            'sponsorship_plan_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['required', 'in:visibility,booking,launch'],
            'starts_on' => ['required', 'date', 'after_or_equal:today'],
            'target_cities' => ['nullable', 'string', 'max:500'],
        ]);

        $venue = $ownerProfile->venues()->whereKey($validated['venue_id'])->firstOrFail();
        $plan = SponsorshipPlan::query()->where('is_active', true)->whereKey($validated['sponsorship_plan_id'])->firstOrFail();
        $startsOn = Carbon::parse($validated['starts_on'])->startOfDay();

        SponsorshipCampaign::query()->create([
            'owner_profile_id' => $ownerProfile->id,
            'venue_id' => $venue->id,
            'sponsorship_plan_id' => $plan->id,
            'name' => $validated['name'],
            'goal' => $validated['goal'],
            'placement' => $plan->placement,
            'status' => 'pending',
            'starts_on' => $startsOn->toDateString(),
            'ends_on' => $startsOn->clone()->addDays($plan->duration_days - 1)->toDateString(),
            'budget_amount' => $plan->price,
            'daily_budget' => (int) ceil($plan->price / max(1, $plan->duration_days)),
            'currency' => $plan->currency,
            'target_cities' => collect(explode(',', (string) ($validated['target_cities'] ?? '')))
                ->map(fn (string $city): string => trim($city))
                ->filter()
                ->values()
                ->all(),
        ]);

        return back()->with('sponsorship_status', 'Campagne envoyée au SAP pour validation.');
    }

    public function updateStatus(Request $request, SponsorshipCampaign $sponsorshipCampaign): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile()->firstOrFail();

        abort_unless((int) $sponsorshipCampaign->owner_profile_id === (int) $ownerProfile->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:paused,cancelled'],
        ]);

        $sponsorshipCampaign->update(['status' => $validated['status']]);

        return back()->with('sponsorship_status', 'Campagne mise à jour.');
    }
}
