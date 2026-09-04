@props([
    'title' => 'Dashboard SAP',
    'subtitle' => null,
    'active' => 'overview',
    'ownersCount' => 0,
    'clientsCount' => 0,
    'publishedVenuesCount' => 0,
    'pendingAccessRequestsCount' => 0,
    'pendingSponsorshipsCount' => 0,
    'grossPaymentsAmount' => 0,
    'activeBookingsCount' => 0,
])

@php
    $navigation = [
        ['key' => 'overview', 'label' => 'Vue d’ensemble', 'route' => 'sap.dashboard', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
        ['key' => 'requests', 'label' => 'Validations', 'route' => 'sap.portal-requests', 'icon' => 'M9 12l2 2 4-5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['key' => 'owners', 'label' => 'Partenaires PEE', 'route' => 'sap.owners', 'icon' => 'M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M8 7h2M12 7h2M8 11h2M12 11h2M2 21h20'],
        ['key' => 'venues', 'label' => 'Espaces', 'route' => 'sap.venues', 'icon' => 'M10 20v-6h4v6M4 10 12 3l8 7v10H4V10Z'],
        ['key' => 'clients', 'label' => 'Clients', 'route' => 'sap.clients', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0'],
        ['key' => 'bookings', 'label' => 'Réservations', 'route' => 'sap.bookings', 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
        ['key' => 'payments', 'label' => 'Paiements', 'route' => 'sap.payments', 'icon' => 'M3 7h18v10H3V7Zm3 4h5M16 14h2'],
        ['key' => 'subscriptions', 'label' => 'Abonnements', 'route' => 'sap.subscription-plans', 'icon' => 'M4 6h16M4 12h16M4 18h10'],
        ['key' => 'commissions', 'label' => 'Commissions', 'route' => 'sap.commissions', 'icon' => 'M4 19 19 4M7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z'],
        ['key' => 'deposit-rules', 'label' => 'Acomptes réservation', 'route' => 'sap.deposit-rules', 'icon' => 'M12 6v12M8 10h6a3 3 0 0 1 0 6H8m8-8H9a3 3 0 0 0 0 6h7'],
        ['key' => 'service-types', 'label' => 'Types de services', 'route' => 'sap.service-types', 'icon' => 'M12 3l7 4v10l-7 4-7-4V7l7-4Zm0 0v8m7-4-7 4-7-4'],
        ['key' => 'sponsoring-plans', 'label' => 'Forfaits sponsoring', 'route' => 'sap.sponsorship-plans', 'icon' => 'M12 2l2.2 6.8h7.1l-5.7 4.1 2.2 6.8L12 15.5 6.2 19.7l2.2-6.8-5.7-4.1h7.1L12 2Z'],
    ];
@endphp

<x-layouts.baobaa :title="$title.' - SAP BAOBAA'">
    <style>
        @media (min-width: 1024px) {
            .sap-dashboard-grid {
                grid-template-columns: 300px minmax(0, 1fr);
            }
        }
    </style>

    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <div class="sap-dashboard-grid grid min-h-screen">
            <aside class="sticky top-0 hidden h-screen w-[300px] overflow-y-auto border-r border-white/80 bg-white/90 p-5 shadow-2xl shadow-[#173e7a]/8 backdrop-blur-xl lg:block">
                <a href="{{ route('sap.dashboard') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">super admin</span>
                    </span>
                </a>

                <div class="mt-7 rounded-[22px] border border-[#dce6f7] bg-[#07152f] p-4 text-white">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Centre de contrôle</p>
                    <p class="mt-2 text-base font-extrabold">Qualité, sécurité et revenus</p>
                    <p class="mt-1 text-xs font-bold text-white/60">{{ number_format($pendingAccessRequestsCount + $pendingSponsorshipsCount, 0, ',', ' ') }} validation{{ ($pendingAccessRequestsCount + $pendingSponsorshipsCount) > 1 ? 's' : '' }} en attente</p>
                </div>

                <nav class="mt-6 space-y-1.5">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'text-[#5d6b86] hover:bg-[#f0f5ff] hover:text-[#2f6bff]' }}">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <section class="min-w-0">
                <header class="sticky top-0 z-40 border-b border-white/80 bg-white/84 px-5 py-4 shadow-sm shadow-[#173e7a]/5 backdrop-blur-xl sm:px-7">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Portail SAP</p>
                            <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f] sm:text-3xl">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Déconnexion</button>
                        </form>
                    </div>

                    <nav class="mt-4 flex gap-2 overflow-x-auto lg:hidden baobaa-scrollbar-none">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" class="shrink-0 rounded-full px-4 py-2 text-xs font-extrabold {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white' : 'bg-[#f2f6ff] text-[#52617b]' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </header>

                <div class="px-5 py-6 sm:px-7">
                    @if (session('sap_status'))
                        <div class="mb-5 rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session('sap_status') }}</div>
                    @endif

                    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['label' => 'Revenus confirmés', 'value' => number_format($grossPaymentsAmount, 0, ',', ' ').' XOF', 'hint' => 'paiements réussis'],
                            ['label' => 'Réservations actives', 'value' => number_format($activeBookingsCount, 0, ',', ' '), 'hint' => 'pipeline opérationnel'],
                            ['label' => 'Espaces publiés', 'value' => number_format($publishedVenuesCount, 0, ',', ' '), 'hint' => 'visibles au catalogue'],
                            ['label' => 'Validations', 'value' => number_format($pendingAccessRequestsCount + $pendingSponsorshipsCount, 0, ',', ' '), 'hint' => 'à traiter par le SAP'],
                        ] as $metric)
                            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">{{ $metric['label'] }}</p>
                                <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $metric['value'] }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $metric['hint'] }}</p>
                            </div>
                        @endforeach
                    </section>

                    <div class="mt-6">
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </div>
    </main>
</x-layouts.baobaa>
