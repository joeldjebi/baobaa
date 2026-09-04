@props([
    'title' => 'Dashboard client',
    'subtitle' => null,
    'active' => 'overview',
    'client' => null,
    'upcomingBookingsCount' => 0,
    'confirmedPaymentsAmount' => 0,
    'reservedVenuesCount' => 0,
    'pendingPaymentsCount' => 0,
])

@php
    use App\Enums\UserRole;

    $navigation = [
        ['key' => 'overview', 'label' => 'Vue d’ensemble', 'route' => 'client.dashboard', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
        ['key' => 'projects', 'label' => 'Mes événements', 'route' => 'client.projects', 'icon' => 'M12 3l7 4v10l-7 4-7-4V7l7-4Zm0 0v8m7-4-7 4-7-4'],
        ['key' => 'composer', 'label' => 'Composer', 'route' => 'event-composer.create', 'icon' => 'M12 5v14M5 12h14M4 4h16v16H4V4Z'],
        ['key' => 'reservations', 'label' => 'Réservations', 'route' => 'client.reservations', 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
        ['key' => 'payments', 'label' => 'Paiements', 'route' => 'client.payments', 'icon' => 'M3 7h18v10H3V7Zm3 4h5M16 14h2'],
        ['key' => 'profile', 'label' => 'Profil et sécurité', 'route' => 'client.profile', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0'],
        ['key' => 'explore', 'label' => 'Explorer les espaces', 'route' => 'venues.index', 'icon' => 'M10 20v-6h4v6M4 10 12 3l8 7v10H4V10Z'],
    ];
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA client'">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <div class="mx-auto grid min-h-screen w-full max-w-[1800px] lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="sticky top-0 hidden h-screen overflow-y-auto border-r border-white/80 bg-white/88 p-5 shadow-2xl shadow-[#173e7a]/8 backdrop-blur-xl lg:block">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">espace client</span>
                    </span>
                </a>

                <div class="mt-7 rounded-[22px] border border-[#dce6f7] bg-[#f7faff] p-4">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Profil</p>
                    <p class="mt-2 truncate text-base font-extrabold text-[#07152f]">{{ $client?->name ?? 'Client BAOBAA' }}</p>
                    <p class="mt-1 truncate text-xs font-bold text-[#6f7890]">{{ $client?->email }}</p>
                </div>

                <nav class="mt-6 space-y-1.5">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'text-[#5d6b86] hover:bg-[#f0f5ff] hover:text-[#2f6bff]' }}">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-6 grid gap-2">
                    <a href="{{ route('event-composer.create') }}" class="flex items-center justify-center rounded-2xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Composer un événement</a>
                    <a href="{{ route('venues.index') }}" class="flex items-center justify-center rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#07152f]/15 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Réserver un espace</a>
                    @if ($client && ! $client->hasPortal(UserRole::Owner))
                        <a href="{{ route('portals.owner.request.form') }}" class="flex items-center justify-center rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Devenir partenaire vérifié</a>
                    @elseif ($client?->hasPortal(UserRole::Owner))
                        <a href="{{ route('owner.dashboard') }}" class="flex items-center justify-center rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Basculer côté PEE</a>
                    @endif
                </div>
            </aside>

            <section class="min-w-0">
                <header class="sticky top-0 z-40 border-b border-white/80 bg-white/84 px-4 py-4 shadow-sm shadow-[#173e7a]/5 backdrop-blur-xl sm:px-6 lg:px-7">
                    <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Portail client</p>
                            <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f] sm:text-3xl">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 max-w-2xl text-sm font-semibold text-[#6f7890]">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('venues.index') }}" class="hidden rounded-full border border-[#c9d8ef] bg-white px-4 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm md:inline-flex">Voir les espaces</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Déconnexion</button>
                            </form>
                        </div>
                    </div>

                    <nav class="mx-auto mt-4 flex w-full max-w-7xl gap-2 overflow-x-auto lg:hidden baobaa-scrollbar-none">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" class="shrink-0 rounded-full px-4 py-2 text-xs font-extrabold {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white' : 'bg-[#f2f6ff] text-[#52617b]' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </header>

                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-7">
                    @if (session('portal_status'))
                        <div class="mb-5 rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session('portal_status') }}</div>
                    @endif

                    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['label' => 'Réservations à venir', 'value' => number_format($upcomingBookingsCount, 0, ',', ' '), 'hint' => 'demandes et confirmations'],
                            ['label' => 'Paiements confirmés', 'value' => number_format($confirmedPaymentsAmount, 0, ',', ' ').' XOF', 'hint' => 'acomptes sécurisés'],
                            ['label' => 'Espaces réservés', 'value' => number_format($reservedVenuesCount, 0, ',', ' '), 'hint' => 'lieux déjà choisis'],
                            ['label' => 'Paiements à suivre', 'value' => number_format($pendingPaymentsCount, 0, ',', ' '), 'hint' => 'transactions en attente'],
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
