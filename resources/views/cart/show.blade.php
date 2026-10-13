@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', ' ').' XOF';
@endphp

<x-layouts.baobaa title="Mon panier - BAOBAA">
    <main class="min-h-screen bg-[#f6f8fc] text-[#151821]">
        <x-navigation.public-header active="cart" />

        <section class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
            <h1 class="text-3xl font-extrabold tracking-[-0.04em]">Mon panier</h1>
            <p class="mt-2 text-sm font-semibold text-[#6f7890]">Une commande est créée par prestataire. Choisissez pour chacun la livraison ou le retrait.</p>

            @if (session('cart_status'))
                <div class="mt-5 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('cart_status') }}</div>
            @endif
            <x-forms.validation-errors class="mt-5" />

            @if ($providerGroups->isEmpty())
                <div class="mt-6 rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center">
                    <p class="text-lg font-extrabold">Votre panier est vide.</p>
                    <a href="{{ route('products.index') }}" class="mt-4 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white">Découvrir le matériel</a>
                </div>
            @else
                @php
                    $grandTotal = 0;
                @endphp
                <form method="POST" action="{{ route('cart.checkout') }}" id="checkout-form" class="hidden">@csrf</form>

                <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
                    <div class="space-y-5">
                        @foreach ($providerGroups as $group)
                            @php
                                $provider = $group['provider'];
                            @endphp
                            <section class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]" data-provider-group>
                                <h2 class="text-lg font-extrabold">{{ $provider->business_name }}</h2>
                                <div class="mt-4 space-y-3">
                                    @foreach ($group['items'] as $item)
                                        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                                            <img src="{{ $item->product->cover_image_url }}" alt="" class="size-16 rounded-xl object-cover">
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ route('products.show', $item->product) }}" class="text-sm font-extrabold hover:text-[#2f6bff]">{{ $item->product->name }}</a>
                                                <p class="text-xs font-bold text-[#6f7890]">
                                                    {{ $item->mode->label() }}
                                                    @if ($item->rental_starts_on)
                                                        · du {{ $item->rental_starts_on->format('d/m/Y') }} au {{ $item->rental_ends_on->format('d/m/Y') }} ({{ $item->rentalDays() }} j)
                                                    @endif
                                                    @if ($item->depositTotal() > 0)
                                                        · caution {{ $money($item->depositTotal()) }}
                                                    @endif
                                                </p>
                                            </div>
                                            <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="w-16 rounded-xl border border-[#dce6f7] px-2 py-1.5 text-sm font-bold" aria-label="Quantité">
                                                <button class="rounded-xl bg-white px-2 py-1.5 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">OK</button>
                                            </form>
                                            <p class="w-28 text-right text-sm font-extrabold">{{ $money($item->lineTotal()) }}</p>
                                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs font-extrabold text-[#b42318]" aria-label="Retirer {{ $item->product->name }}">Retirer</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 rounded-2xl border border-[#dce6f7] p-4">
                                    <p class="text-sm font-extrabold">Remise du matériel</p>
                                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-[#dce6f7] p-3 text-sm font-bold has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#f4f8ff]">
                                            <input form="checkout-form" type="radio" name="fulfillment[{{ $provider->id }}][method]" value="pickup" checked data-fulfillment-choice data-fee="0" class="accent-[#2f6bff]">
                                            Retrait gratuit à {{ $provider->city }}
                                        </label>
                                        @if ($group['delivery_zones']->isNotEmpty())
                                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-[#dce6f7] p-3 text-sm font-bold has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#f4f8ff]">
                                                <input form="checkout-form" type="radio" name="fulfillment[{{ $provider->id }}][method]" value="delivery" data-fulfillment-choice class="accent-[#2f6bff]">
                                                Livraison
                                            </label>
                                        @endif
                                    </div>
                                    @if ($group['delivery_zones']->isNotEmpty())
                                        <div class="mt-3 hidden gap-2 sm:grid-cols-3" data-delivery-fields>
                                            <select form="checkout-form" name="fulfillment[{{ $provider->id }}][city]" data-delivery-city class="rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold">
                                                @foreach ($group['delivery_zones'] as $zone)
                                                    <option value="{{ $zone->city }}" data-fee="{{ $zone->fee }}">{{ $zone->city }} ({{ $money((int) $zone->fee) }})</option>
                                                @endforeach
                                            </select>
                                            <input form="checkout-form" name="fulfillment[{{ $provider->id }}][address]" placeholder="Adresse de livraison" class="rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold">
                                            <input form="checkout-form" type="date" name="fulfillment[{{ $provider->id }}][date]" min="{{ now()->toDateString() }}" aria-label="Date de livraison" class="rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold">
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap justify-end gap-4 text-sm font-bold text-[#52617b]">
                                    <span>Articles : <strong class="text-[#151821]">{{ $money($group['items_total']) }}</strong></span>
                                    @if ($group['deposit_total'] > 0)
                                        <span>Caution : <strong class="text-[#151821]">{{ $money($group['deposit_total']) }}</strong></span>
                                    @endif
                                    <span>Livraison : <strong class="text-[#151821]" data-delivery-fee-label>{{ $money(0) }}</strong></span>
                                </div>
                                @php
                                    $grandTotal += $group['items_total'] + $group['deposit_total'];
                                @endphp
                            </section>
                        @endforeach
                    </div>

                    <aside class="h-max rounded-[26px] bg-white p-5 shadow-2xl shadow-[#173e7a]/12 ring-1 ring-[#dce6f7] lg:sticky lg:top-24">
                        <h2 class="text-lg font-extrabold">Paiement</h2>
                        <p class="mt-3 flex justify-between text-sm font-bold text-[#52617b]">Total à payer <span class="text-lg font-extrabold text-[#07152f]" data-grand-total data-base-total="{{ $grandTotal }}">{{ $money($grandTotal) }}</span></p>
                        <p class="text-xs font-semibold text-[#8a94aa]">Livraison et caution incluses. La caution vous est restituée au retour du matériel.</p>
                        <label class="mt-4 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Moyen de paiement
                            <select form="checkout-form" name="payment_method" class="mt-1 w-full rounded-xl border border-[#dce6f7] px-3 py-2.5 text-sm font-bold text-[#151821]">
                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="mt-3 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Message aux prestataires (facultatif)
                            <textarea form="checkout-form" name="client_notes" rows="2" class="mt-1 w-full rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold text-[#151821]"></textarea>
                        </label>
                        <x-forms.terms-acceptance class="mt-4" form="checkout-form" />
                        <button form="checkout-form" class="mt-4 w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Payer et commander (test)</button>
                    </aside>
                </div>
            @endif
        </section>

        <x-navigation.public-footer />
    </main>

    <script>
        (() => {
            const formatter = new Intl.NumberFormat('fr-FR');
            const grandTotal = document.querySelector('[data-grand-total]');

            const refresh = () => {
                let deliveryTotal = 0;

                document.querySelectorAll('[data-provider-group]').forEach((group) => {
                    const choice = group.querySelector('[data-fulfillment-choice]:checked');
                    const fields = group.querySelector('[data-delivery-fields]');
                    const city = group.querySelector('[data-delivery-city]');
                    const isDelivery = choice?.value === 'delivery';
                    const fee = isDelivery ? Number(city?.selectedOptions[0]?.dataset.fee || 0) : 0;

                    fields?.classList.toggle('hidden', ! isDelivery);
                    fields?.classList.toggle('grid', isDelivery);
                    group.querySelector('[data-delivery-fee-label]').textContent = `${formatter.format(fee)} XOF`;
                    deliveryTotal += fee;
                });

                if (grandTotal) {
                    grandTotal.textContent = `${formatter.format(Number(grandTotal.dataset.baseTotal) + deliveryTotal)} XOF`;
                }
            };

            document.querySelectorAll('[data-fulfillment-choice], [data-delivery-city]').forEach((input) => input.addEventListener('change', refresh));
            refresh();
        })();
    </script>
</x-layouts.baobaa>
