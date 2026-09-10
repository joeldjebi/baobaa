<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\ProformaInvoiceStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\OwnerModuleTemplate;
use App\Models\Venue;
use App\Models\VenueAddOn;
use App\Services\BookingDepositService;
use App\Services\BookingWorkflowService;
use App\Services\EventProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientBookingPackageController extends Controller
{
    public function __construct(
        private readonly BookingDepositService $bookingDepositService,
        private readonly BookingWorkflowService $bookingWorkflowService,
        private readonly EventProjectService $eventProjectService,
    ) {}

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless((int) $booking->client_id === (int) $request->user()->id, 404);
        abort_unless($booking->venue instanceof Venue, 404);

        if ($this->hasSucceededDepositPayment($booking)) {
            return back()->with('package_status', 'Le package ne peut plus être modifié directement après le paiement de l’acompte.');
        }

        $currentEventServiceIds = $this->currentEventServiceIds($booking);

        $validated = $request->validate([
            'selected_add_on_ids' => ['nullable', 'array', 'max:20'],
            'selected_add_on_ids.*' => [
                'integer',
                Rule::exists('venue_add_ons', 'id')
                    ->where('venue_id', $booking->venue_id)
                    ->where('is_available', true),
            ],
            'add_on_quantities' => ['nullable', 'array'],
            'add_on_quantities.*' => ['integer', 'min:1', 'max:99'],
            'selected_module_template_ids' => ['nullable', 'array', 'max:20'],
            'selected_module_template_ids.*' => [
                'integer',
                Rule::exists('owner_module_templates', 'id')
                    ->where('owner_profile_id', $booking->owner_profile_id)
                    ->where('is_active', true),
            ],
            'module_template_quantities' => ['nullable', 'array'],
            'module_template_quantities.*' => ['integer', 'min:1', 'max:99'],
            'event_service_ids' => ['nullable', 'array', 'max:12'],
            'event_service_ids.*' => [
                'integer',
                Rule::exists('event_services', 'id')->where(function ($query) use ($currentEventServiceIds): void {
                    $query->where('status', VenueStatus::Published->value)
                        ->when($currentEventServiceIds !== [], fn ($query) => $query->orWhereIn('id', $currentEventServiceIds));
                }),
            ],
            'event_service_quantities' => ['nullable', 'array'],
            'event_service_quantities.*' => ['integer', 'min:1', 'max:99'],
        ]);

        $booking->loadMissing(['venue.ownerProfile.depositRules', 'payments', 'proformaInvoice']);

        $selectedVenueAddOns = $this->selectedVenueAddOnsFor($booking->venue, array_map('intval', $validated['selected_add_on_ids'] ?? []));
        $selectedModuleTemplates = $this->selectedModuleTemplatesFor($booking, array_map('intval', $validated['selected_module_template_ids'] ?? []));
        $selectedAddOnsPayload = $this->selectedAddOnsPayload(
            $selectedVenueAddOns,
            $selectedModuleTemplates,
            $validated['add_on_quantities'] ?? [],
            $validated['module_template_quantities'] ?? [],
        );
        $newAddOnsTotal = $this->selectedAddOnsTotal($selectedAddOnsPayload);
        $baseAmount = $this->baseVenueAmountFor($booking);
        $totalAmount = $baseAmount + $newAddOnsTotal;
        $reservationAmount = $this->bookingDepositService->amountFor($booking->venue, $totalAmount);
        $eventServiceQuantities = $this->selectedEventServiceQuantities($validated);

        DB::transaction(function () use ($booking, $selectedAddOnsPayload, $totalAmount, $reservationAmount, $eventServiceQuantities): void {
            $packageChanged = $booking->selected_add_ons !== $selectedAddOnsPayload
                || (int) $booking->total_amount !== $totalAmount
                || (int) $booking->reservation_amount !== $reservationAmount;

            $booking->forceFill([
                'selected_add_ons' => $selectedAddOnsPayload,
                'total_amount' => $totalAmount,
                'reservation_amount' => $reservationAmount,
            ])->save();

            if ($packageChanged && $booking->proformaInvoice) {
                $booking->proformaInvoice->forceFill([
                    'status' => ProformaInvoiceStatus::Sent,
                    'client_confirmed_at' => null,
                    'owner_confirmed_at' => null,
                ])->save();
            }

            $this->bookingWorkflowService->ensureReadyForNegotiation($booking->refresh());
            $this->eventProjectService->syncBookingEventServices($booking->refresh(), $eventServiceQuantities);
        });

        return back()->with('package_status', 'Votre package a été mis à jour. La proforma et l’acompte ont été recalculés.');
    }

    private function hasSucceededDepositPayment(Booking $booking): bool
    {
        return $booking->payments()
            ->where('status', PaymentStatus::Succeeded)
            ->exists();
    }

    /**
     * @return array<int, int>
     */
    private function currentEventServiceIds(Booking $booking): array
    {
        $booking->loadMissing('eventProjectItem.eventProject.items');

        return $booking->eventProjectItem?->eventProject?->items
            ?->where('item_type', 'event_service')
            ->pluck('source_id')
            ->filter()
            ->map(fn (int|string $id): int => (int) $id)
            ->values()
            ->all() ?? [];
    }

    /**
     * @param  array<int, int>  $selectedAddOnIds
     * @return Collection<int, VenueAddOn>
     */
    private function selectedVenueAddOnsFor(Venue $venue, array $selectedAddOnIds): Collection
    {
        if ($selectedAddOnIds === []) {
            return collect();
        }

        return VenueAddOn::query()
            ->where('venue_id', $venue->id)
            ->where('is_available', true)
            ->whereIn('id', array_unique($selectedAddOnIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, int>  $selectedModuleTemplateIds
     * @return Collection<int, OwnerModuleTemplate>
     */
    private function selectedModuleTemplatesFor(Booking $booking, array $selectedModuleTemplateIds): Collection
    {
        if ($selectedModuleTemplateIds === []) {
            return collect();
        }

        return OwnerModuleTemplate::query()
            ->where('owner_profile_id', $booking->owner_profile_id)
            ->where('is_active', true)
            ->whereIn('id', array_unique($selectedModuleTemplateIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, VenueAddOn>  $venueAddOns
     * @param  Collection<int, OwnerModuleTemplate>  $moduleTemplates
     * @param  array<int|string, int|string>  $venueQuantities
     * @param  array<int|string, int|string>  $templateQuantities
     * @return array<int, array{id: int, source_type: string, source_id: int, name: string, price: int, quantity: int, currency: string}>
     */
    private function selectedAddOnsPayload(Collection $venueAddOns, Collection $moduleTemplates, array $venueQuantities, array $templateQuantities): array
    {
        return $venueAddOns
            ->map(fn (VenueAddOn $addOn): array => $this->addOnPayload(
                $addOn->id,
                'venue_add_on',
                $addOn->name,
                (int) $addOn->price,
                $addOn->currency,
                $venueQuantities[$addOn->id] ?? 1,
            ))
            ->concat($moduleTemplates->map(fn (OwnerModuleTemplate $module): array => $this->addOnPayload(
                $module->id,
                'owner_module_template',
                $module->name,
                (int) $module->price,
                $module->currency,
                $templateQuantities[$module->id] ?? 1,
            )))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, source_type: string, source_id: int, name: string, price: int, quantity: int, currency: string}
     */
    private function addOnPayload(int $id, string $sourceType, string $name, int $price, string $currency, int|string $quantity): array
    {
        return [
            'id' => $id,
            'source_type' => $sourceType,
            'source_id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => max(1, (int) $quantity),
            'currency' => $currency,
        ];
    }

    /**
     * @param  array<int, array{id?: int, source_type?: string, source_id?: int, name?: string, price?: int, quantity?: int, currency?: string}>  $selectedAddOns
     */
    private function selectedAddOnsTotal(array $selectedAddOns): int
    {
        return collect($selectedAddOns)
            ->sum(fn (array $addOn): int => (int) ($addOn['price'] ?? 0) * max(1, (int) ($addOn['quantity'] ?? 1)));
    }

    private function baseVenueAmountFor(Booking $booking): int
    {
        $currentAddOnsTotal = $this->selectedAddOnsTotal($booking->selected_add_ons ?: []);
        $baseAmount = (int) $booking->total_amount - $currentAddOnsTotal;

        return $baseAmount > 0 ? $baseAmount : (int) $booking->venue->starting_price;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function selectedEventServiceQuantities(array $validated): array
    {
        $selectedServiceIds = array_map('intval', $validated['event_service_ids'] ?? []);
        $quantities = $validated['event_service_quantities'] ?? [];

        return collect($selectedServiceIds)
            ->mapWithKeys(fn (int $serviceId): array => [$serviceId => max(1, (int) ($quantities[$serviceId] ?? 1))])
            ->all();
    }
}
