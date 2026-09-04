<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingWorkflowService
{
    public function __construct(
        private readonly BookingDepositService $bookingDepositService,
        private readonly EventProjectService $eventProjectService,
        private readonly ProformaInvoiceService $proformaInvoiceService,
    ) {}

    public function ensureReadyForNegotiation(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $booking->loadMissing('venue.ownerProfile.depositRules');

            if ($booking->venue) {
                $totalAmount = $this->negotiatedAmountFor($booking)
                    ?: (int) ($booking->total_amount ?: $booking->venue->starting_price);
                $reservationAmount = $this->bookingDepositService->amountFor($booking->venue, $totalAmount);

                $booking->forceFill([
                    'currency' => $booking->currency ?: $booking->venue->currency,
                    'total_amount' => $totalAmount,
                    'reservation_amount' => $reservationAmount,
                ])->save();
            }

            $this->eventProjectService->ensureVenueBookingItem($booking);
            $this->proformaInvoiceService->createForBooking($booking);
            $this->ensureDepositPayment($booking);
            $this->eventProjectService->ensureVenueBookingItem($booking->refresh());

            return $booking->refresh();
        });
    }

    /**
     * @param  array<int, int>  $eventServiceIds
     */
    public function syncRequestedAdditions(Booking $booking, array $eventServiceIds, bool $ticketingRequested): void
    {
        $this->eventProjectService->syncRequestedAdditions($booking, $eventServiceIds, $ticketingRequested);
    }

    private function ensureDepositPayment(Booking $booking): void
    {
        $hasSucceededDepositPayment = $booking->payments()
            ->where('status', PaymentStatus::Succeeded)
            ->exists();

        if ($hasSucceededDepositPayment || $booking->reservation_amount <= 0) {
            return;
        }

        $paymentMethod = $booking->venue?->payment_methods[0] ?? 'baobaa_checkout';
        $pendingPayment = $booking->payments()
            ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::Pending])
            ->latest()
            ->first();

        if ($pendingPayment) {
            $pendingPayment->update([
                'amount' => $booking->reservation_amount,
                'currency' => $booking->currency,
                'provider' => $pendingPayment->provider ?: $paymentMethod,
                'payment_method' => $pendingPayment->payment_method ?: $paymentMethod,
                'provider_payload' => array_merge($pendingPayment->provider_payload ?: [], [
                    'source' => 'booking_workflow_negotiated_amount',
                ]),
            ]);

            return;
        }

        Payment::query()->create([
            'booking_id' => $booking->id,
            'payer_id' => $booking->client_id,
            'reference' => $this->uniquePaymentReference(),
            'provider' => $paymentMethod,
            'payment_method' => $paymentMethod,
            'status' => PaymentStatus::Initiated,
            'amount' => $booking->reservation_amount,
            'currency' => $booking->currency,
            'provider_payload' => [
                'reason' => 'reservation_deposit',
                'source' => 'booking_workflow_auto_repair',
            ],
        ]);
    }

    private function negotiatedAmountFor(Booking $booking): ?int
    {
        $hasSucceededDepositPayment = $booking->payments()
            ->where('status', PaymentStatus::Succeeded)
            ->exists();

        if ($hasSucceededDepositPayment) {
            return null;
        }

        $amount = $booking->messages()
            ->whereNotNull('proposed_amount')
            ->latest('id')
            ->value('proposed_amount');

        return $amount ? (int) $amount : null;
    }

    private function uniquePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
