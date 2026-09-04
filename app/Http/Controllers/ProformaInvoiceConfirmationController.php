<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingWorkflowService;
use App\Services\ProformaInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProformaInvoiceConfirmationController extends Controller
{
    public function __construct(
        private readonly ProformaInvoiceService $proformaInvoiceService,
        private readonly BookingWorkflowService $bookingWorkflowService,
    ) {}

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        $booking = $this->bookingWorkflowService->ensureReadyForNegotiation($booking);
        $booking->loadMissing(['ownerProfile.user', 'proformaInvoice']);

        abort_unless($booking->proformaInvoice, 404);

        if ((int) $booking->client_id === (int) $user->id) {
            $this->proformaInvoiceService->confirmByClient($booking->proformaInvoice);

            return back()->with('proforma_status', 'Proforma confirmée côté client.');
        }

        abort_unless((int) $booking->ownerProfile?->user_id === (int) $user->id, 404);

        $this->proformaInvoiceService->confirmByOwner($booking->proformaInvoice);

        return back()->with('proforma_status', 'Proforma confirmée côté partenaire.');
    }
}
