<?php

namespace App\Http\Controllers;

use App\Enums\EventProjectItemStatus;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\ServiceProviderProfile;
use App\Services\EventProjectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderReservationController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->profile($request);
        $dashboard = app(ServiceProviderDashboardController::class)->metricsFor($profile);

        return view('dashboards.service-provider.reservations', [
            ...$dashboard,
            'profile' => $profile,
            'statusLabels' => $this->statusLabels(),
            'reservations' => $this->reservationQuery($profile)
                ->when($request->filled('status'), fn (Builder $query) => $query->whereHas('items', fn (Builder $query) => $query
                    ->where('item_type', 'event_service')
                    ->where('provider_type', 'service_provider_profile')
                    ->where('provider_id', $profile->id)
                    ->where('status', $request->string('status'))))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('reference', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhereHas('client', fn (Builder $query) => $query
                                ->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search))
                            ->orWhereHas('items', fn (Builder $query) => $query
                                ->where('item_type', 'event_service')
                                ->where('title', 'like', $search));
                    });
                })
                ->latest()
                ->paginate(10)
                ->through(fn (EventProject $project): EventProject => $this->attachProviderRelations($project, $profile))
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, EventProject $eventProject): View
    {
        $profile = $this->profile($request);
        $this->authorizeProject($profile, $eventProject);
        $dashboard = app(ServiceProviderDashboardController::class)->metricsFor($profile);

        $eventProject->load('client');
        $eventProject = $this->attachProviderRelations($eventProject, $profile);

        return view('dashboards.service-provider.reservation-show', [
            ...$dashboard,
            'profile' => $profile,
            'reservation' => $eventProject,
            'providerItems' => $eventProject->provider_items,
            'providerMessages' => $eventProject->provider_messages,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function storeMessage(Request $request, EventProject $eventProject): RedirectResponse
    {
        $profile = $this->profile($request);
        $this->authorizeProject($profile, $eventProject);
        $eventProject->loadMissing('client');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'proposed_amount' => ['nullable', 'integer', 'min:1'],
        ]);

        $eventProject->messages()->create([
            'provider_type' => 'service_provider_profile',
            'provider_id' => $profile->id,
            'sender_id' => $request->user()->id,
            'recipient_id' => $eventProject->client_id,
            'message' => $validated['message'],
            'proposed_amount' => $validated['proposed_amount'] ?? null,
            'currency' => $eventProject->currency,
        ]);

        if (isset($validated['proposed_amount'])) {
            $this->applyProposedAmount($eventProject, $profile, (int) $validated['proposed_amount']);
        }

        return back()->with('pse_status', 'Message ajouté à la négociation.');
    }

    public function confirm(Request $request, EventProject $eventProject): RedirectResponse
    {
        $profile = $this->profile($request);
        $this->authorizeProject($profile, $eventProject);

        $this->providerItemsQuery($eventProject, $profile)
            ->get()
            ->each(function (EventProjectItem $item): void {
                $item->forceFill([
                    'provider_confirmed_at' => $item->provider_confirmed_at ?: now(),
                    'status' => $item->client_confirmed_at
                        ? EventProjectItemStatus::Confirmed
                        : EventProjectItemStatus::AwaitingClientConfirmation,
                ])->save();
            });

        app(EventProjectService::class)->refreshTotals($eventProject);

        return back()->with('pse_status', 'Proforma de réservation confirmée côté prestataire.');
    }

    private function reservationQuery(ServiceProviderProfile $profile): Builder
    {
        return EventProject::query()
            ->with('client')
            ->whereHas('items', fn (Builder $query) => $query
                ->where('item_type', 'event_service')
                ->where('provider_type', 'service_provider_profile')
                ->where('provider_id', $profile->id));
    }

    private function attachProviderRelations(EventProject $project, ServiceProviderProfile $profile): EventProject
    {
        $project->setRelation('provider_items', $this->providerItemsQuery($project, $profile)->with('source')->get());
        $project->setRelation('provider_messages', $project->messages()
            ->with('sender')
            ->where('provider_type', 'service_provider_profile')
            ->where('provider_id', $profile->id)
            ->latest()
            ->get());

        return $project;
    }

    private function providerItemsQuery(EventProject $project, ServiceProviderProfile $profile): HasMany
    {
        return $project->items()
            ->where('item_type', 'event_service')
            ->where('provider_type', 'service_provider_profile')
            ->where('provider_id', $profile->id)
            ->orderBy('id');
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

    private function authorizeProject(ServiceProviderProfile $profile, EventProject $eventProject): void
    {
        abort_unless($this->providerItemsQuery($eventProject, $profile)->exists(), 404);
    }

    private function applyProposedAmount(EventProject $eventProject, ServiceProviderProfile $profile, int $proposedAmount): void
    {
        $items = $this->providerItemsQuery($eventProject, $profile)->get();
        $currentTotal = max(1, (int) $items->sum('quoted_amount'));
        $remaining = $proposedAmount;

        $items->values()->each(function (EventProjectItem $item, int $index) use ($items, $currentTotal, &$remaining, $proposedAmount): void {
            $ratio = (int) $item->quoted_amount / $currentTotal;
            $newAmount = $index === $items->count() - 1
                ? $remaining
                : (int) round($proposedAmount * $ratio);
            $depositRatio = (int) $item->deposit_amount > 0 && (int) $item->quoted_amount > 0
                ? (int) $item->deposit_amount / (int) $item->quoted_amount
                : 0;

            $remaining -= $newAmount;

            $item->forceFill([
                'quoted_amount' => max(0, $newAmount),
                'deposit_amount' => (int) round(max(0, $newAmount) * $depositRatio),
                'client_confirmed_at' => null,
                'provider_confirmed_at' => null,
                'status' => EventProjectItemStatus::Negotiating,
            ])->save();
        });

        app(EventProjectService::class)->refreshTotals($eventProject);
    }

    /**
     * @return array<string, string>
     */
    private function statusLabels(): array
    {
        return [
            'draft' => 'Brouillon',
            'negotiating' => 'En négociation',
            'awaiting_client_confirmation' => 'Validation client attendue',
            'awaiting_provider_confirmation' => 'Validation prestataire attendue',
            'awaiting_payment' => 'Paiement attendu',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
        ];
    }
}
