@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', ' ').' '.$project->currency;
    $statusClass = fn (string $status): string => match ($status) {
        'confirmed' => 'bg-[#ecfdf5] text-[#047857]',
        'awaiting_payment', 'awaiting_client_confirmation' => 'bg-[#fff7ed] text-[#c2410c]',
        'cancelled' => 'bg-[#fff6f6] text-[#b42318]',
        default => 'bg-[#eef4ff] text-[#2f6bff]',
    };
@endphp

<x-dashboards.client-shell :title="$project->name" subtitle="Échangez avec chaque prestataire, validez leurs devis et payez les acomptes pour confirmer votre événement." active="projects" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <a href="{{ route('client.projects') }}" class="mb-4 inline-flex text-sm font-extrabold text-[#2f6bff]">← Mes événements</a>

    @if (session('project_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('project_status') }}</div>
    @endif

    <x-forms.validation-errors class="mb-4" />

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">{{ $project->reference }}</p>
        <div class="mt-3 grid gap-3 md:grid-cols-4">
            <div class="rounded-2xl bg-[#f7faff] p-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Date</p>
                <p class="mt-2 font-extrabold text-[#07152f]">{{ $project->event_date?->format('d/m/Y') ?? 'À préciser' }}</p>
            </div>
            <div class="rounded-2xl bg-[#f7faff] p-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Lieu</p>
                <p class="mt-2 font-extrabold text-[#07152f]">{{ collect([$project->district, $project->city])->filter()->implode(', ') ?: 'À préciser' }}</p>
            </div>
            <div class="rounded-2xl bg-[#f7faff] p-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total estimé</p>
                <p class="mt-2 font-extrabold text-[#07152f]">{{ $money((int) $project->estimated_total_amount) }}</p>
            </div>
            <div class="rounded-2xl bg-[#f7faff] p-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total confirmé</p>
                <p class="mt-2 font-extrabold text-[#047857]">{{ $money((int) $project->confirmed_total_amount) }}</p>
            </div>
        </div>
    </section>

    @foreach ($venueItems as $venueItem)
        <section class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Espace</p>
                <p class="mt-1 text-lg font-extrabold text-[#07152f]">{{ $venueItem->title }}</p>
                <p class="text-sm font-semibold text-[#6f7890]">{{ $money((int) $venueItem->quoted_amount) }} · {{ $statusLabels[$venueItem->status->value] ?? 'À suivre' }}</p>
            </div>
            @if ($venueItem->booking)
                <a href="{{ route('client.reservations.show', $venueItem->booking) }}" class="rounded-full bg-[#07152f] px-4 py-2 text-sm font-extrabold text-white">Gérer la réservation de l’espace</a>
            @endif
        </section>
    @endforeach

    @forelse ($providerLanes as $lane)
        @php($provider = $lane['provider'])
        <section id="prestataire-{{ $provider->id }}" class="mt-5 scroll-mt-24 rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Prestataire</p>
                    <h2 class="mt-1 text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">{{ $provider->business_name }}</h2>
                    <a href="{{ route('service-provider-profiles.show', $provider) }}" class="text-xs font-extrabold text-[#2f6bff]">Voir le profil</a>
                </div>
                <div class="text-right">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Devis</p>
                    <p class="text-xl font-extrabold text-[#07152f]">{{ $money($lane['quoted_amount']) }}</p>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @foreach ($lane['items'] as $item)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-[#07152f]">{{ $item->title }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $money((int) $item->quoted_amount) }} · acompte {{ $money((int) $item->deposit_amount) }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $statusClass($item->status->value) }}">{{ $statusLabels[$item->status->value] ?? 'À suivre' }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                @if ($lane['can_confirm'])
                    <form method="POST" action="{{ route('client.projects.confirm', [$project, $provider]) }}">
                        @csrf
                        <button class="rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Accepter le devis de {{ $money($lane['quoted_amount']) }}</button>
                    </form>
                @endif

                @if ($lane['deposit_due'] > 0)
                    <form method="POST" action="{{ route('client.projects.pay', [$project, $provider]) }}" class="flex flex-wrap items-center gap-2 rounded-2xl bg-[#fff7ed] p-3 ring-1 ring-[#fed7aa]">
                        @csrf
                        <span class="text-sm font-extrabold text-[#9a3412]">Acompte à payer : {{ $money($lane['deposit_due']) }}</span>
                        <select name="payment_method" class="rounded-full border border-[#fed7aa] bg-white px-3 py-2 text-xs font-extrabold text-[#151821]">
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-full bg-[#07152f] px-5 py-2.5 text-sm font-extrabold text-white">Payer l’acompte (test)</button>
                    </form>
                @endif

                @foreach ($lane['payments'] as $payment)
                    <span class="rounded-full bg-[#ecfdf5] px-3 py-1.5 text-xs font-extrabold text-[#047857]">Acompte payé : {{ $money((int) $payment->amount) }} le {{ $payment->paid_at?->format('d/m/Y') }}</span>
                @endforeach
            </div>

            <div class="mt-5 border-t border-[#edf2fb] pt-4">
                <p class="text-sm font-extrabold text-[#07152f]">Messages avec {{ $provider->business_name }}</p>
                <div class="mt-3 max-h-80 space-y-2 overflow-y-auto">
                    @forelse ($lane['messages'] as $message)
                        @php($isMine = (int) $message->sender_id === (int) $client->id)
                        <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm font-semibold leading-6 {{ $isMine ? 'bg-[#2f6bff] text-white' : 'bg-[#f2f6ff] text-[#151821]' }}">
                                <p>{{ $message->message }}</p>
                                @if ($message->proposed_amount)
                                    <p class="mt-1 text-xs font-extrabold">Nouveau montant proposé : {{ $money((int) $message->proposed_amount) }}</p>
                                @endif
                                <p class="mt-1 text-[10px] font-bold opacity-70">{{ $isMine ? 'Vous' : $provider->business_name }} · {{ $message->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-[#6f7890]">Aucun message pour le moment. Posez vos questions au prestataire ici.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('client.projects.messages.store', [$project, $provider]) }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <textarea name="message" rows="2" required maxlength="2000" placeholder="Votre message au prestataire..." class="min-w-0 flex-1 resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></textarea>
                    <button class="rounded-2xl bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white">Envoyer</button>
                </form>
            </div>
        </section>
    @empty
        @if ($venueItems->isEmpty())
            <p class="mt-5 rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center text-sm font-semibold text-[#6f7890]">Aucun prestataire dans ce dossier pour le moment.</p>
        @endif
    @endforelse

    @foreach ($otherItems as $item)
        <section class="mt-5 rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <p class="text-sm font-extrabold text-[#07152f]">{{ $item->title }}</p>
            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $statusLabels[$item->status->value] ?? 'À suivre' }}</p>
        </section>
    @endforeach
</x-dashboards.client-shell>
