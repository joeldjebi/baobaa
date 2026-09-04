@php
    $bookingStatusLabels = [
        'draft' => 'Brouillon',
        'pending_owner' => 'Validation partenaire',
        'pending_payment' => 'Paiement attendu',
        'confirmed' => 'Confirmée',
        'declined' => 'Refusée',
        'cancelled' => 'Annulée',
        'completed' => 'Terminée',
        'disputed' => 'En litige',
    ];
    $paymentMethods = [
        'baobaa_checkout' => 'Paiement sécurisé BAOBAA',
        'wave' => 'Wave',
        'orange_money' => 'Orange Money',
        'mtn_money' => 'MTN Money',
        'bank_transfer' => 'Virement bancaire',
        'cash' => 'Paiement sur place',
    ];
    $canPay = $booking->proformaInvoice?->status?->value === 'confirmed'
        && $booking->payments->contains(fn ($payment) => in_array($payment->status->value, ['initiated', 'pending'], true));
@endphp

<x-dashboards.client-shell title="Dossier réservation" subtitle="Consultez la proforma, échangez avec le partenaire et sécurisez votre acompte." active="reservations" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="min-w-0 space-y-5">
            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('client.reservations') }}" class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Mes réservations</a>
                        <h2 class="mt-2 break-words text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $booking->reference }}</h2>
                        <p class="mt-1 break-words text-sm font-semibold text-[#6f7890]">{{ $booking->venue?->name }} · {{ $booking->event_date?->format('d/m/Y') }}</p>
                    </div>
                    <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-4">
                    @foreach ([
                        ['label' => 'Invités', 'value' => number_format($booking->guests_count, 0, ',', ' ')],
                        ['label' => 'Créneau', 'value' => substr($booking->starts_at, 0, 5).' - '.substr($booking->ends_at, 0, 5)],
                        ['label' => 'Budget espace', 'value' => number_format($booking->total_amount, 0, ',', ' ').' '.$booking->currency],
                        ['label' => 'Acompte requis', 'value' => number_format($booking->reservation_amount, 0, ',', ' ').' '.$booking->currency],
                    ] as $item)
                        <div class="min-w-0 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <p class="text-[11px] font-extrabold uppercase text-[#7d8aa7]">{{ $item['label'] }}</p>
                            <p class="mt-2 break-words text-sm font-extrabold text-[#07152f]">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <x-dashboards.partials.booking-workflow
                :booking="$booking"
                :message-route="route('client.reservations.messages.store', $booking)"
                :confirm-route="route('client.reservations.proforma.confirm', $booking)"
                :payment-route="route('client.reservations.test-payment', $booking)"
                :can-pay="$canPay"
                actor-label="Vous"
            />
        </section>

        <aside class="min-w-0 space-y-4">
            <div class="overflow-hidden rounded-[26px] border border-white/80 bg-white shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                @if ($booking->venue?->media?->first()?->url)
                    <img src="{{ $booking->venue->media->first()->url }}" alt="{{ $booking->venue->name }}" class="h-44 w-full object-cover">
                @endif
                <div class="p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Espace réservé</p>
                    <h3 class="mt-2 text-lg font-extrabold text-[#07152f]">{{ $booking->venue?->name }}</h3>
                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $booking->venue?->city }} · {{ $booking->venue?->district }}</p>
                    <a href="{{ route('venues.show', $booking->venue?->slug) }}" class="mt-4 inline-flex rounded-full border border-[#c9d8ef] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Voir la fiche publique</a>
                </div>
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h3 class="text-sm font-extrabold text-[#07152f]">Partenaire</h3>
                <p class="mt-3 break-words text-lg font-extrabold text-[#151821]">{{ $booking->venue?->ownerProfile?->business_name }}</p>
                <p class="mt-1 break-words text-sm font-semibold text-[#6f7890]">{{ $booking->venue?->ownerProfile?->city }}</p>
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h3 class="text-sm font-extrabold text-[#07152f]">Moyens de paiement</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach (($booking->venue?->payment_methods ?: ['baobaa_checkout']) as $method)
                        <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-xs font-extrabold text-[#52617b]">{{ $paymentMethods[$method] ?? $method }}</span>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</x-dashboards.client-shell>
