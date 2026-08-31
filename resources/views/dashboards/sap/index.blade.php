@php
    $maxRevenue = max(1, collect($monthlyRevenue)->max('amount') ?: 1);
@endphp

<x-dashboards.sap-shell title="Vue d’ensemble" subtitle="Pilotage global des accès, revenus, espaces et validations." active="overview" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-[#07152f]">Revenus mensuels</h2>
                    <p class="mt-1 text-sm font-semibold text-[#64708a]">Paiements confirmés sur les six derniers mois.</p>
                </div>
                <a href="{{ route('sap.payments') }}" class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Voir paiements</a>
            </div>
            <div class="mt-6 flex h-72 items-end gap-3 rounded-3xl bg-[#f7faff] p-5 ring-1 ring-[#edf2fb]">
                @foreach ($monthlyRevenue as $month)
                    <div class="flex h-full flex-1 flex-col justify-end gap-2">
                        <div class="rounded-t-2xl bg-[#2f6bff] shadow-lg shadow-[#2f6bff]/20" style="height: {{ max(8, (int) (($month['amount'] / $maxRevenue) * 100)) }}%"></div>
                        <p class="text-center text-[11px] font-extrabold text-[#64708a]">{{ $month['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-[28px] bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/15">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">File SAP</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-[-0.04em]">{{ number_format($pendingAccessRequestsCount + $pendingSponsorshipsCount, 0, ',', ' ') }} validations</h2>
                <a href="{{ route('sap.portal-requests') }}" class="mt-5 inline-flex rounded-2xl bg-white px-4 py-2.5 text-sm font-extrabold text-[#07152f]">Traiter maintenant</a>
            </section>

            <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <h2 class="text-lg font-extrabold text-[#07152f]">Demandes récentes</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($recentAccessRequests as $request)
                        <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#edf2fb]">
                            <p class="text-sm font-extrabold text-[#07152f]">{{ $request->business_name ?? $request->user?->name }}</p>
                            <p class="mt-1 text-xs font-bold text-[#64708a]">{{ $request->user?->email }} · {{ $request->status }}</p>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-[#64708a]">Aucune demande récente.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</x-dashboards.sap-shell>
