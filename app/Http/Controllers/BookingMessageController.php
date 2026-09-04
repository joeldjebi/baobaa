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

        $this->bookingWorkflowService->ensureReadyForNegotiation($booking);

        return back()->with('conversation_status', 'Message ajouté à la discussion.');
    }
}
