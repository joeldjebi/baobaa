@php
    $projectStatusLabels = [
        'draft' => 'Brouillon',
        'active' => 'En préparation',
        'partially_confirmed' => 'Partiellement confirmé',
        'confirmed' => 'Confirmé',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];
    $itemTypeLabels = [
        'venue_booking' => 'Espace',
        'event_service' => 'Service',
        'ticketing' => 'Billetterie',
    ];
    $itemStatusLabels = [
        'draft' => 'Brouillon',
        'negotiating' => 'Négociation',
        'awaiting_client_confirmation' => 'Validation client',
        'awaiting_provider_confirmation' => 'Validation fournisseur',
        'awaiting_payment' => 'Acompte attendu',
        'confirmed' => 'Confirmé',
        'cancelled' => 'Annulé',
    ];
@endphp

<x-dashboards.client-shell title="Mes événements" subtitle="Pilotez vos dossiers événementiels : espaces, services, proformas et acomptes." active="projects" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        @if (session('project_status'))
            <div class="mb-5 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">
                {{ session('project_status') }}
            </div>
        @endif

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold tracking-[-0.035em] text-[#07152f]">Vos projets événementiels</h2>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">Espace, services PSE et billetterie réunis dans un seul dossier.</p>
            </div>
            <a href="{{ route('event-composer.create') }}" class="rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Composer un événement</a>
        </div>

        <form method="GET" class="grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_190px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence ou nom de l’événement" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach ($projectStatusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 grid gap-4">
            @forelse ($projects as $project)
                <article class="overflow-hidden rounded-[24px] border border-[#edf2fb] bg-[#fbfcff]">
                    <div class="flex flex-wrap items-start justify-between gap-4 bg-white p-4">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">{{ $project->reference }}</p>
                            <h2 class="mt-1 break-words text-xl font-extrabold tracking-[-0.035em] text-[#07152f]">{{ $project->name }}</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $project->event_date?->format('d/m/Y') ?? 'Date à préciser' }} · {{ $project->city ?? 'Ville à préciser' }}</p>
                        </div>
                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $projectStatusLabels[$project->status->value] ?? 'À suivre' }}</span>
                    </div>

                    <div class="grid gap-3 p-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-white p-4 ring-1 ring-[#edf2fb]">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Éléments</p>
                            <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($project->items_count, 0, ',', ' ') }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 ring-1 ring-[#edf2fb]">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total estimé</p>
                            <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($project->estimated_total_amount, 0, ',', ' ') }} {{ $project->currency }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 ring-1 ring-[#edf2fb]">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total confirmé</p>
                            <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($project->confirmed_total_amount, 0, ',', ' ') }} {{ $project->currency }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 px-4 pb-4">
                        @foreach ($project->items as $item)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white p-3 ring-1 ring-[#edf2fb]">
                                <div class="min-w-0">
                                    <p class="text-sm font-extrabold text-[#07152f]">{{ $itemTypeLabels[$item->item_type] ?? 'Élément' }} · {{ $item->title }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ number_format($item->quoted_amount, 0, ',', ' ') }} {{ $item->currency }} · acompte {{ number_format($item->deposit_amount, 0, ',', ' ') }} {{ $item->currency }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-extrabold text-[#52617b]">{{ $itemStatusLabels[$item->status->value] ?? 'À suivre' }}</span>
                                    @if ($item->booking)
                                        <a href="{{ route('client.reservations.show', $item->booking) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Ouvrir</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center text-sm font-semibold text-[#6f7890]">Aucun projet événementiel pour le moment.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $projects->links() }}</div>
    </section>
</x-dashboards.client-shell>
