<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProformaInvoiceStatus;
use App\Models\Booking;
use App\Services\BookingWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestPaymentController extends Controller
{
    public function __construct(private readonly BookingWorkflowService $bookingWorkflowService) {}

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless((int) $booking->client_id === (int) $request->user()->id, 404);

        $booking = $this->bookingWorkflowService->ensureReadyForNegotiation($booking);
        $booking->loadMissing(['payments', 'proformaInvoice']);

        if ($booking->proformaInvoice?->status !== ProformaInvoiceStatus::Confirmed) {
            return back()->with('payment_status', 'La proforma doit être confirmée par le client et le partenaire avant le paiement.');
        }

        $payment = $booking->payments()
            ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::Pending])
            ->latest()
            ->first();

        if (! $payment) {
            return back()->with('payment_status', 'Aucun acompte en attente de paiement pour cette réservation.');
        }

        DB::transaction(function () use ($booking, $payment): void {
            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'provider_reference' => 'TEST-'.Str::upper(Str::random(10)),
                'paid_at' => now(),
                'provider_payload' => array_merge($payment->provider_payload ?: [], [
                    'mode' => 'test',
                    'confirmed_by' => 'baobaa_test_checkout',
                ]),
            ]);

            $booking->update([
                'status' => $booking->booking_mode === 'instant' ? BookingStatus::Confirmed : BookingStatus::PendingOwner,
            ]);
        });

        return back()->with('payment_status', 'Paiement test validé. L’acompte est enregistré dans l’historique.');
    }
}
