<?php

namespace App\Services;

use App\Enums\ProformaInvoiceStatus;
use App\Models\Booking;
use App\Models\ProformaInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProformaInvoiceService
{
    public function createForBooking(Booking $booking): ProformaInvoice
    {
        $booking->loadMissing('venue');

        return DB::transaction(function () use ($booking): ProformaInvoice {
            $invoice = ProformaInvoice::query()->firstOrCreate([
                'booking_id' => $booking->id,
            ], [
                'event_project_item_id' => $booking->event_project_item_id,
                'reference' => $this->uniqueReference(),
                'status' => ProformaInvoiceStatus::Sent,
                'currency' => $booking->currency,
                'subtotal_amount' => $booking->total_amount,
                'deposit_amount' => $booking->reservation_amount,
                'service_fee_amount' => 0,
                'total_amount' => $booking->total_amount,
            ]);

            $amountChanged = (int) $invoice->total_amount !== (int) $booking->total_amount
                || (int) $invoice->deposit_amount !== (int) $booking->reservation_amount;

            if ($amountChanged) {
                $invoice->forceFill([
                    'status' => ProformaInvoiceStatus::Sent,
                    'event_project_item_id' => $booking->event_project_item_id,
                    'subtotal_amount' => $booking->total_amount,
                    'deposit_amount' => $booking->reservation_amount,
                    'service_fee_amount' => 0,
                    'total_amount' => $booking->total_amount,
                    'client_confirmed_at' => null,
                    'owner_confirmed_at' => null,
                ])->save();
            }

            $selectedAddOns = collect($booking->selected_add_ons ?: []);
            $selectedAddOnsTotal = (int) $selectedAddOns->sum(fn (array $addOn): int => (int) ($addOn['price'] ?? 0) * max(1, (int) ($addOn['quantity'] ?? 1)));
            $venueAmount = max(0, (int) $booking->total_amount - $selectedAddOnsTotal);

            $invoice->items()->updateOrCreate([
                'sort_order' => 1,
            ], [
                'label' => 'Location de l’espace',
                'description' => $booking->venue?->name ?: 'Espace événementiel BAOBAA',
                'quantity' => 1,
                'unit_price' => $venueAmount,
                'total_price' => $venueAmount,
            ]);

            $invoice->items()->where('sort_order', '>', 1)->delete();

            $selectedAddOns
                ->values()
                ->each(function (array $addOn, int $index) use ($invoice): void {
                    $quantity = max(1, (int) ($addOn['quantity'] ?? 1));
                    $unitPrice = (int) ($addOn['price'] ?? 0);

                    $invoice->items()->create([
                        'label' => 'Module optionnel',
                        'description' => (string) ($addOn['name'] ?? 'Module complémentaire'),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $quantity,
                        'sort_order' => $index + 2,
                    ]);
                });

            if ($invoice->event_project_item_id !== $booking->event_project_item_id) {
                $invoice->update(['event_project_item_id' => $booking->event_project_item_id]);
            }

            return $invoice->load('items');
        });
    }

    public function confirmByClient(ProformaInvoice $invoice): ProformaInvoice
    {
        return $this->confirm($invoice, 'client');
    }

    public function confirmByOwner(ProformaInvoice $invoice): ProformaInvoice
    {
        return $this->confirm($invoice, 'owner');
    }

    private function confirm(ProformaInvoice $invoice, string $actor): ProformaInvoice
    {
        $invoice->forceFill($actor === 'client'
            ? ['client_confirmed_at' => $invoice->client_confirmed_at ?: now()]
            : ['owner_confirmed_at' => $invoice->owner_confirmed_at ?: now()]
        )->save();

        $invoice->refresh();

        $invoice->update([
            'status' => match (true) {
                $invoice->client_confirmed_at && $invoice->owner_confirmed_at => ProformaInvoiceStatus::Confirmed,
                $invoice->client_confirmed_at !== null => ProformaInvoiceStatus::AcceptedByClient,
                $invoice->owner_confirmed_at !== null => ProformaInvoiceStatus::AcceptedByOwner,
                default => ProformaInvoiceStatus::Sent,
            },
        ]);

        return $invoice->refresh();
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'PRO-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (ProformaInvoice::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
