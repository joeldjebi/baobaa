@php
    $client = $reservation->client;
    $providerTotal = (int) $providerItems->sum('quoted_amount');
    $providerDeposit = (int) $providerItems->sum('deposit_amount');
    $providerStatus = $providerItems->contains(fn ($item) => $item->status->value === 'negotiating')
        ? 'negotiating'
        : ($providerItems->first()?->status->value ?? 'negotiating');
    $statusLabel = $statusLabels[$providerStatus] ?? 'À suivre';
    $latestNegotiatedAmount = $providerMessages
        ->filter(fn ($message) => $message->proposed_amount)
        ->sortByDesc('id')
        ->first()?->proposed_amount;
    $providerConfirmed = $providerItems->isNotEmpty() && $providerItems->every(fn ($item) => $item->provider_confirmed_at !== null);
    $clientConfirmed = $providerItems->isNotEmpty() && $providerItems->every(fn ($item) => $item->client_confirmed_at !== null);
@endphp

<x-dashboards.service-provider-shell title="Détail réservation" subtitle="Une réservation peut contenir plusieurs services. La négociation porte sur l’ensemble demandé au prestataire." active="reservations" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="space-y-5">
            <div class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">{{ $reservation->reference }}</p>
                        <h2 class="mt-2 break-words text-2xl font-extrabold tracking-[-0.01em] text-[#07152f]">{{ $reservation->name }}</h2>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#6f7890]">Réservation client regroupant {{ number_format($providerItems->count(), 0, ',', ' ') }} service{{ $providerItems->count() > 1 ? 's' : '' }} demandé{{ $providerItems->count() > 1 ? 's' : '' }} à votre organisation.</p>
                    </div>
                    <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabel }}</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-4">
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Client</p>
                        <p class="mt-2 break-words text-sm font-extrabold text-[#07152f]">{{ $client?->name ?? 'Client BAOBAA' }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Date</p>
                        <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ $reservation->event_date?->format('d/m/Y') ?? 'À préciser' }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Lieu</p>
                        <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ $reservation->city ?? 'Ville à préciser' }}{{ $reservation->district ? ' · '.$reservation->district : '' }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Invités</p>
                        <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ ($reservation->metadata['guests_count'] ?? null) ? number_format((int) $reservation->metadata['guests_count'], 0, ',', ' ') : 'À préciser' }}</p>
                    </div>
                </div>
            </div>

            <section class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Proforma de réservation</p>
                        <h3 class="mt-1 text-xl font-extrabold tracking-[-0.015em] text-[#07152f]">Services demandés au prestataire</h3>
                        <p class="mt-1 text-sm font-semibold text-[#6f7890]">Le prix proposé s’applique au total de cette réservation, puis il est réparti sur les services inclus.</p>
                    </div>
                    <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-xs font-extrabold text-[#52617b]">{{ $providerConfirmed && $clientConfirmed ? 'Confirmée par les deux parties' : 'Validation en cours' }}</span>
                </div>

                @if ($latestNegotiatedAmount)
                    <div class="mt-5 rounded-2xl border border-[#b9d3ff] bg-[#f4f8ff] p-4">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Dernier prix proposé pour la réservation</p>
                        <p class="mt-1 text-lg font-extrabold text-[#07152f]">{{ number_format($latestNegotiatedAmount, 0, ',', ' ') }} {{ $reservation->currency }}</p>
                    </div>
                @endif

                <div class="mt-5 overflow-hidden rounded-2xl border border-[#edf2fb]">
                    <div class="grid grid-cols-[minmax(0,1fr)_90px_140px_140px] bg-[#f7faff] px-4 py-3 text-xs font-extrabold uppercase text-[#7d8aa7] max-md:hidden">
                        <span>Service</span>
                        <span class="text-center">Qté</span>
                        <span class="text-right">Prix</span>
                        <span class="text-right">Total</span>
                    </div>
                    <div class="divide-y divide-[#edf2fb]">
                        @foreach ($providerItems as $item)
                            <div class="grid gap-2 px-4 py-4 text-sm md:grid-cols-[minmax(0,1fr)_90px_140px_140px] md:items-center">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-[#151821]">{{ $item->title }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $item->metadata['service_type'] ?? 'Service événementiel' }}</p>
                                </div>
                                <p class="font-bold text-[#52617b] md:text-center">{{ number_format((int) ($item->metadata['quantity'] ?? 1), 0, ',', ' ') }}</p>
                                <p class="font-bold text-[#52617b] md:text-right">{{ number_format((int) ($item->metadata['unit_amount'] ?? $item->quoted_amount), 0, ',', ' ') }} {{ $item->currency }}</p>
                                <p class="font-extrabold text-[#07152f] md:text-right">{{ number_format($item->quoted_amount, 0, ',', ' ') }} {{ $item->currency }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total proforma</p>
                        <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($providerTotal, 0, ',', ' ') }} {{ $reservation->currency }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Acompte indicatif</p>
                        <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($providerDeposit, 0, ',', ' ') }} {{ $reservation->currency }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Validation</p>
                        <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ $providerConfirmed ? 'Confirmée côté prestataire' : 'À confirmer côté prestataire' }}</p>
                    </div>
                </div>

                @unless ($providerConfirmed)
                    <form method="POST" action="{{ route('service-provider.reservations.proforma.confirm', $reservation) }}" class="mt-5">
                        @csrf
                        <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Confirmer le devis</button>
                    </form>
                @endunless

                @php
                    $paidItems = $providerItems->whereNotNull('paid_at');
                    $awaitingClient = $providerItems->contains(fn ($item) => $item->client_confirmed_at === null);
                @endphp
                <div class="mt-5 rounded-2xl p-4 text-sm font-extrabold ring-1 {{ $paidItems->isNotEmpty() ? 'bg-[#ecfdf5] text-[#047857] ring-[#a7f3d0]' : 'bg-[#f7faff] text-[#52617b] ring-[#dce6f7]' }}">
                    @if ($paidItems->isNotEmpty())
                        Acompte payé par le client le {{ $paidItems->max('paid_at')?->format('d/m/Y') }} : la prestation est confirmée.
                    @elseif ($awaitingClient)
                        En attente de la validation du devis par le client.
                    @else
                        Devis validé par le client : en attente du paiement de l’acompte.
                    @endif
                </div>
            </section>

            <section class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Négociation</p>
                        <h3 class="mt-1 text-xl font-extrabold tracking-[-0.015em] text-[#07152f]">Échanges avec le client</h3>
                    </div>
                    <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-xs font-extrabold text-[#52617b]">{{ $providerMessages->count() }} message(s)</span>
                </div>

                <form method="POST" action="{{ route('service-provider.reservations.messages.store', $reservation) }}" class="mt-5 grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7]">
                    @csrf
                    <textarea name="message" rows="3" placeholder="Répondez au client, précisez votre offre ou proposez un nouveau montant global..." class="w-full rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-semibold text-[#151821] outline-none focus:border-[#2f6bff]">{{ old('message') }}</textarea>
                    @error('message') <span class="text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    <div class="grid gap-3 md:grid-cols-[minmax(0,240px)_auto] md:items-center">
                        <label class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-2.5">
                            <span class="block text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Prix global proposé</span>
                            <input type="number" min="1" name="proposed_amount" value="{{ old('proposed_amount') }}" placeholder="Optionnel" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#07152f] outline-none placeholder:text-[#9ca7bb]">
                        </label>
                        <button class="rounded-2xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Envoyer</button>
                    </div>
                </form>

                <div class="mt-5 space-y-3">
                    @forelse ($providerMessages as $message)
                        @php
                            $isMine = (int) $message->sender_id === (int) auth()->id();
                        @endphp
                        <article class="rounded-2xl border {{ $isMine ? 'border-[#cfe0ff] bg-[#f4f8ff]' : 'border-[#edf2fb] bg-white' }} p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-extrabold text-[#151821]">{{ $isMine ? 'Vous' : $message->sender?->name }}</p>
                                <p class="text-xs font-bold text-[#8a94aa]">{{ $message->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">{{ $message->message }}</p>
                            @if ($message->proposed_amount)
                                <p class="mt-3 inline-flex rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">Proposition globale : {{ number_format($message->proposed_amount, 0, ',', ' ') }} {{ $message->currency }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-center text-sm font-semibold text-[#6f7890]">Aucun échange pour cette réservation.</p>
                    @endforelse
                </div>
            </section>
        </section>

        <aside class="xl:sticky xl:top-24 xl:self-start">
            <div class="rounded-[28px] bg-[#07162f] p-5 text-white shadow-2xl shadow-[#07162f]/20">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#9fc0ff]">Résumé client</p>
                <h3 class="mt-2 text-xl font-extrabold tracking-[-0.015em]">{{ $reservation->name }}</h3>
                <div class="mt-5 space-y-3 text-sm font-semibold text-white/75">
                    <p>{{ $client?->name ?? 'Client BAOBAA' }}</p>
                    <p>{{ $client?->email }}</p>
                    <p>{{ $reservation->city ?? 'Ville à préciser' }}{{ $reservation->district ? ' · '.$reservation->district : '' }}</p>
                    <p>{{ $reservation->event_date?->format('d/m/Y') ?? 'Date à préciser' }}</p>
                </div>
                @if ($reservation->metadata['client_notes'] ?? null)
                    <div class="mt-5 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#9fc0ff]">Brief client</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-white/75">{{ $reservation->metadata['client_notes'] }}</p>
                    </div>
                @endif
                <a href="{{ route('service-provider.reservations') }}" class="mt-5 flex justify-center rounded-2xl bg-white px-4 py-3 text-sm font-extrabold text-[#07152f]">Retour aux réservations</a>
            </div>
        </aside>
    </div>
</x-dashboards.service-provider-shell>
