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
        private readonly ProformaInvoiceService $proformaInvoiceService,
    ) {}

    public function ensureReadyForNegotiation(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $booking->loadMissing('venue.ownerProfile.depositRules');

            if ($booking->venue) {
                $totalAmount = (int) ($booking->total_amount ?: $booking->venue->starting_price);
                $reservationAmount = (int) ($booking->reservation_amount ?: $this->bookingDepositService->amountFor($booking->venue, $totalAmount));

                $booking->forceFill([
                    'currency' => $booking->currency ?: $booking->venue->currency,
                    'total_amount' => $totalAmount,
                    'reservation_amount' => $reservationAmount,
                ])->save();
            }

            $this->proformaInvoiceService->createForBooking($booking);
            $this->ensureDepositPayment($booking);

            return $booking->refresh();
        });
    }

    private function ensureDepositPayment(Booking $booking): void
    {
        $hasDepositPayment = $booking->payments()
            ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::Pending, PaymentStatus::Succeeded])
            ->exists();

        if ($hasDepositPayment || $booking->reservation_amount <= 0) {
            return;
        }

        $paymentMethod = $booking->venue?->payment_methods[0] ?? 'baobaa_checkout';

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

    private function uniquePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
