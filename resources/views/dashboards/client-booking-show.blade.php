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
    $paymentMethods = \App\Enums\PaymentMethod::options() + ['cash' => 'Paiement sur place'];
    $canPay = $booking->proformaInvoice?->status?->value === 'confirmed'
        && $booking->payments->contains(fn ($payment) => in_array($payment->status->value, ['initiated', 'pending'], true));
    $selectedPackageAddOns = collect($booking->selected_add_ons ?: []);
    $selectedVenueAddOnQuantities = $selectedPackageAddOns
        ->filter(fn ($addOn) => ($addOn['source_type'] ?? 'venue_add_on') === 'venue_add_on')
        ->mapWithKeys(fn ($addOn) => [(int) ($addOn['source_id'] ?? $addOn['id'] ?? 0) => max(1, (int) ($addOn['quantity'] ?? 1))])
        ->all();
    $selectedModuleTemplateQuantities = $selectedPackageAddOns
        ->filter(fn ($addOn) => ($addOn['source_type'] ?? null) === 'owner_module_template')
        ->mapWithKeys(fn ($addOn) => [(int) ($addOn['source_id'] ?? $addOn['id'] ?? 0) => max(1, (int) ($addOn['quantity'] ?? 1))])
        ->all();
    $selectedServiceQuantities = $selectedServiceQuantities ?? [];
@endphp

<x-dashboards.client-shell title="Dossier réservation" subtitle="Consultez la proforma, échangez avec le partenaire et sécurisez votre acompte." active="reservations" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="min-w-0 space-y-5">
            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('client.reservations') }}" class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Mes réservations</a>
                        <h2 class="mt-2 break-words text-2xl font-extrabold tracking-[-0.01em] text-[#07152f]">{{ $booking->reference }}</h2>
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

            @if (session('package_status'))
                <div class="rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session('package_status') }}</div>
            @endif

            <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Composition</p>
                        <h3 class="mt-1 break-words text-xl font-extrabold tracking-[-0.015em] text-[#07152f]">Ajuster mon package</h3>
                        <p class="mt-1 text-sm font-semibold leading-6 text-[#6f7890]">Modifiez les modules et les services avant validation définitive de la proforma.</p>
                    </div>
                    <span class="rounded-full {{ $canEditPackage ? 'bg-[#eef4ff] text-[#2f6bff]' : 'bg-[#fff4e5] text-[#9a6700]' }} px-3 py-1 text-xs font-extrabold">
                        {{ $canEditPackage ? 'Modifiable' : 'Verrouillé' }}
                    </span>
                </div>

                @unless ($canEditPackage)
                    <p class="mt-5 rounded-2xl border border-[#ffe3b3] bg-[#fff8eb] p-4 text-sm font-bold leading-6 text-[#9a6700]">Le package n’est plus modifiable directement après paiement de l’acompte ou clôture de la réservation. Utilisez la conversation pour demander un ajustement au partenaire.</p>
                @endunless

                <form method="POST" action="{{ route('client.reservations.package.update', $booking) }}" class="mt-5 space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="rounded-[22px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-extrabold text-[#07152f]">Modules optionnels de l’espace</h4>
                                <p class="mt-1 text-xs font-semibold text-[#6f7890]">Cochez les options utiles et ajustez les quantités.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#52617b] ring-1 ring-[#e2eaf8]">{{ count($packageAddOns ?? []) }} module(s)</span>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @forelse (($packageAddOns ?? []) as $addOn)
                                @php
                                    $isTemplateModule = ($addOn['source'] ?? 'venue_add_on') === 'owner_module_template';
                                    $selectedQuantity = $isTemplateModule
                                        ? ($selectedModuleTemplateQuantities[(int) $addOn['id']] ?? 1)
                                        : ($selectedVenueAddOnQuantities[(int) $addOn['id']] ?? 1);
                                    $isSelected = $isTemplateModule
                                        ? array_key_exists((int) $addOn['id'], $selectedModuleTemplateQuantities)
                                        : array_key_exists((int) $addOn['id'], $selectedVenueAddOnQuantities);
                                @endphp
                                <div class="rounded-2xl border border-[#e2eaf8] bg-white p-4">
                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input type="checkbox" name="{{ $isTemplateModule ? 'selected_module_template_ids[]' : 'selected_add_on_ids[]' }}" value="{{ $addOn['id'] }}" class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked($isSelected) @disabled(! $canEditPackage)>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-extrabold text-[#07152f]">{{ $addOn['name'] }}</span>
                                            <span class="mt-1 block text-xs font-bold text-[#6f7890]">{{ $addOn['price'] }}</span>
                                        </span>
                                    </label>
                                    <label class="mt-3 block">
                                        <span class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Quantité</span>
                                        <input type="number" min="1" max="99" name="{{ $isTemplateModule ? 'module_template_quantities' : 'add_on_quantities' }}[{{ $addOn['id'] }}]" value="{{ old(($isTemplateModule ? 'module_template_quantities' : 'add_on_quantities').'.'.$addOn['id'], $selectedQuantity) }}" class="mt-1 h-10 w-full rounded-xl border border-[#dce6f7] bg-[#fbfcff] px-3 text-sm font-extrabold text-[#07152f] outline-none focus:border-[#2f6bff]" @disabled(! $canEditPackage)>
                                    </label>
                                </div>
                            @empty
                                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-white p-5 text-sm font-semibold text-[#6f7890] md:col-span-2">Aucun module optionnel disponible pour cet espace.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[22px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-extrabold text-[#07152f]">Services événementiels complémentaires</h4>
                                <p class="mt-1 text-xs font-semibold text-[#6f7890]">Ajoutez ou retirez les services utiles à votre événement.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#52617b] ring-1 ring-[#e2eaf8]">{{ count($publicEventProviders ?? []) }} partenaire(s)</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            @forelse (($publicEventProviders ?? collect()) as $providerGroup)
                                @php $provider = $providerGroup['provider']; @endphp
                                <div class="rounded-2xl border border-[#e2eaf8] bg-white p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="font-extrabold text-[#07152f]">{{ $provider?->business_name }}</p>
                                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $provider?->city }}{{ $provider?->district ? ' · '.$provider->district : '' }}</p>
                                        </div>
                                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $providerGroup['services']->count() }} service(s)</span>
                                    </div>

                                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                                        @foreach ($providerGroup['services'] as $service)
                                            @php
                                                $serviceQuantity = $selectedServiceQuantities[(int) $service->id] ?? 1;
                                                $serviceSelected = array_key_exists((int) $service->id, $selectedServiceQuantities);
                                            @endphp
                                            <div class="rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                                                <label class="flex cursor-pointer items-start gap-3">
                                                    <input type="checkbox" name="event_service_ids[]" value="{{ $service->id }}" class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked($serviceSelected) @disabled(! $canEditPackage)>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block text-sm font-extrabold text-[#07152f]">{{ $service->name }}</span>
                                                        <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">{{ $service->type?->name ?? 'Service événementiel' }} · {{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency }}</span>
                                                    </span>
                                                </label>
                                                <label class="mt-3 block">
                                                    <span class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Quantité</span>
                                                    <input type="number" min="1" max="99" name="event_service_quantities[{{ $service->id }}]" value="{{ old('event_service_quantities.'.$service->id, $serviceQuantity) }}" class="mt-1 h-10 w-full rounded-xl border border-[#dce6f7] bg-white px-3 text-sm font-extrabold text-[#07152f] outline-none focus:border-[#2f6bff]" @disabled(! $canEditPackage)>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-white p-5 text-sm font-semibold text-[#6f7890]">Aucun service complémentaire disponible pour le moment.</p>
                            @endforelse
                        </div>
                    </div>

                    @if ($canEditPackage)
                        <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Mettre à jour mon package</button>
                    @endif
                </form>
            </section>

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
