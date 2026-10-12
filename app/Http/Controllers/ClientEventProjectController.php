<?php

namespace App\Http\Controllers;

use App\Enums\EventProjectItemStatus;
use App\Enums\PaymentMethod;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\ServiceProviderProfile;
use App\Services\EventProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientEventProjectController extends Controller
{
    public function __construct(private readonly EventProjectService $eventProjectService) {}

    public function show(Request $request, EventProject $eventProject): View
    {
        $this->authorizeClient($request, $eventProject);

        $eventProject->load(['items.booking.venue', 'messages.sender', 'payments']);

        $serviceItems = $eventProject->items->where('item_type', 'event_service')->where('provider_type', 'service_provider_profile');
        $providers = ServiceProviderProfile::query()
            ->whereIn('id', $serviceItems->pluck('provider_id')->unique())
            ->get()
            ->keyBy('id');

        $providerLanes = $serviceItems
            ->groupBy('provider_id')
            ->map(fn ($items, $providerId): array => [
                'provider' => $providers->get($providerId),
                'items' => $items->values(),
                'messages' => $eventProject->messages
                    ->where('provider_type', 'service_provider_profile')
                    ->where('provider_id', (int) $providerId)
                    ->sortBy('created_at')
                    ->values(),
                'payments' => $eventProject->payments
                    ->where('provider_type', 'service_provider_profile')
                    ->where('provider_id', (int) $providerId)
                    ->values(),
                'quoted_amount' => (int) $items->whereNotIn('status', [EventProjectItemStatus::Cancelled])->sum('quoted_amount'),
                'deposit_due' => (int) $items->where('status', EventProjectItemStatus::AwaitingPayment)->whereNull('paid_at')->sum('deposit_amount'),
                'can_confirm' => $items->contains(fn (EventProjectItem $item): bool => $item->client_confirmed_at === null
                    && in_array($item->status, [EventProjectItemStatus::Negotiating, EventProjectItemStatus::AwaitingClientConfirmation], true)),
            ])
            ->filter(fn (array $lane): bool => $lane['provider'] !== null)
            ->values();

        return view('dashboards.client-project-show', [
            ...app(DashboardController::class)->clientMetricsFor($request->user()),
            'client' => $request->user(),
            'project' => $eventProject,
            'venueItems' => $eventProject->items->where('item_type', 'venue_booking')->values(),
            'otherItems' => $eventProject->items->whereNotIn('item_type', ['venue_booking', 'event_service'])->values(),
            'providerLanes' => $providerLanes,
            'statusLabels' => self::statusLabels(),
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    public function storeMessage(Request $request, EventProject $eventProject, ServiceProviderProfile $serviceProviderProfile): RedirectResponse
    {
        $this->authorizeLane($request, $eventProject, $serviceProviderProfile);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $eventProject->messages()->create([
            'provider_type' => 'service_provider_profile',
            'provider_id' => $serviceProviderProfile->id,
            'sender_id' => $request->user()->id,
            'recipient_id' => $serviceProviderProfile->user_id,
            'message' => $validated['message'],
            'currency' => $eventProject->currency,
        ]);

        return $this->backToLane($eventProject, $serviceProviderProfile, 'Message envoyé au prestataire.');
    }

    public function confirm(Request $request, EventProject $eventProject, ServiceProviderProfile $serviceProviderProfile): RedirectResponse
    {
        $this->authorizeLane($request, $eventProject, $serviceProviderProfile);

        $this->eventProjectService->confirmProviderLane($eventProject, $serviceProviderProfile->id, 'client');

        $message = $this->eventProjectService->providerLaneDepositDue($eventProject, $serviceProviderProfile->id) > 0
            ? 'Devis accepté. Vous pouvez maintenant payer l’acompte pour bloquer la prestation.'
            : 'Devis accepté. Le prestataire doit encore le confirmer.';

        return $this->backToLane($eventProject, $serviceProviderProfile, $message);
    }

    /**
     * Test checkout used until the real payment gateway is connected, like the venue deposit flow.
     */
    public function pay(Request $request, EventProject $eventProject, ServiceProviderProfile $serviceProviderProfile): RedirectResponse
    {
        $this->authorizeLane($request, $eventProject, $serviceProviderProfile);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        $payment = $this->eventProjectService->recordProviderLaneDeposit(
            $eventProject,
            $serviceProviderProfile,
            $request->user(),
            $validated['payment_method'],
            ['mode' => 'test', 'provider_reference' => 'TEST-'.Str::upper(Str::random(10))],
        );

        return $this->backToLane(
            $eventProject,
            $serviceProviderProfile,
            $payment
                ? 'Paiement test validé : la prestation est confirmée.'
                : 'Aucun acompte n’est à payer pour ce prestataire. Le devis doit d’abord être validé par les deux parties.',
        );
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            'draft' => 'Brouillon',
            'negotiating' => 'En négociation',
            'awaiting_client_confirmation' => 'Votre validation est attendue',
            'awaiting_provider_confirmation' => 'Validation du prestataire attendue',
            'awaiting_payment' => 'Acompte à payer',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
        ];
    }

    private function authorizeClient(Request $request, EventProject $eventProject): void
    {
        abort_unless((int) $eventProject->client_id === (int) $request->user()->id, 404);
    }

    private function authorizeLane(Request $request, EventProject $eventProject, ServiceProviderProfile $serviceProviderProfile): void
    {
        $this->authorizeClient($request, $eventProject);

        abort_unless($this->eventProjectService->providerLaneItems($eventProject, $serviceProviderProfile->id)->isNotEmpty(), 404);
    }

    private function backToLane(EventProject $eventProject, ServiceProviderProfile $serviceProviderProfile, string $message): RedirectResponse
    {
        return redirect()
            ->to(route('client.projects.show', $eventProject).'#prestataire-'.$serviceProviderProfile->id)
            ->with('project_status', $message);
    }
}
