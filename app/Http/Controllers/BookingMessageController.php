<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingMessageController extends Controller
{
    public function __construct(private readonly BookingWorkflowService $bookingWorkflowService) {}

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        $booking->loadMissing('ownerProfile.user');

        abort_unless(
            (int) $booking->client_id === (int) $user->id
                || (int) $booking->ownerProfile?->user_id === (int) $user->id,
            404
        );

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'proposed_amount' => ['nullable', 'integer', 'min:1'],
        ]);

        $recipientId = (int) $booking->client_id === (int) $user->id
            ? $booking->ownerProfile?->user_id
            : $booking->client_id;

        $booking->messages()->create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'message' => $validated['message'],
            'proposed_amount' => $validated['proposed_amount'] ?? null,
            'currency' => $booking->currency,
        ]);

        // Ensure EventProject and proforma exist before mapping negotiation to project
        $booking = $this->bookingWorkflowService->ensureReadyForNegotiation($booking);

        // If an EventProject exists for this booking, mirror the message into EventProjectMessage
        $eventProject = $booking->eventProjectItem?->eventProject;

        if ($eventProject) {
            $eventProject->messages()->create([
                'provider_type' => 'owner_profile',
                'provider_id' => $booking->owner_profile_id,
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'message' => $validated['message'],
                'proposed_amount' => $validated['proposed_amount'] ?? null,
                'currency' => $booking->currency,
            ]);

            // If client proposed a new total amount, distribute it across owner items
            if (isset($validated['proposed_amount'])) {
                $this->applyClientProposedAmountToOwnerItems($eventProject, $booking->owner_profile_id, (int) $validated['proposed_amount']);
            }
        }

        return back()->with('conversation_status', 'Message ajouté à la discussion.');
    }

    private function applyClientProposedAmountToOwnerItems($eventProject, int $ownerProfileId, int $proposedAmount): void
    {
        $items = $eventProject->items()->where('provider_type', 'owner_profile')->where('provider_id', $ownerProfileId)->get();
        if ($items->isEmpty()) {
            return;
        }

        $currentTotal = max(1, (int) $items->sum('quoted_amount'));
        $remaining = $proposedAmount;

        $items->values()->each(function ($item, $index) use ($items, $currentTotal, &$remaining, $proposedAmount): void {
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
                'status' => \App\Enums\EventProjectItemStatus::Negotiating,
            ])->save();
        });

        app(\App\Services\EventProjectService::class)->refreshTotals($eventProject);
    }
}
