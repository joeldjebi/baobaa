@props([
    'title' => 'Dashboard PEE',
    'subtitle' => null,
    'active' => 'overview',
    'ownerProfile' => null,
    'activeVenuesCount' => 0,
    'pendingBookingsCount' => 0,
    'confirmedBookingsCount' => 0,
    'grossRevenue' => 0,
    'activeSubscription' => null,
    'billingPreferenceLabel' => 'Commission',
])

@php
    $navigation = [
        ['key' => 'overview', 'label' => 'Vue d’ensemble', 'route' => 'owner.dashboard', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
        ['key' => 'venues', 'label' => 'Mes espaces', 'route' => 'owner.venues', 'icon' => 'M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M8 7h2M12 7h2M8 11h2M12 11h2M2 21h20'],
        ['key' => 'bookings', 'label' => 'Réservations', 'route' => 'owner.bookings', 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
        ['key' => 'payments', 'label' => 'Paiements', 'route' => 'owner.payments', 'icon' => 'M3 7h18v10H3V7Zm3 4h5M16 14h2'],
        ['key' => 'sponsorships', 'label' => 'Sponsoriser', 'route' => 'owner.sponsorships', 'icon' => 'M12 2l2.2 6.8h7.1l-5.7 4.1 2.2 6.8L12 15.5 6.2 19.7l2.2-6.8-5.7-4.1h7.1L12 2Z'],
        ['key' => 'calendar', 'label' => 'Disponibilités', 'route' => 'owner.calendar', 'icon' => 'M8 2v4M16 2v4M4 10h16M6 4h12a2 2 0 0 1 2 2v14H4V6a2 2 0 0 1 2-2Z'],
        ['key' => 'addons', 'label' => 'Modules', 'route' => 'owner.addons', 'icon' => 'M12 5v14M5 12h14M4 4h16v16H4V4Z'],
        ['key' => 'reviews', 'label' => 'Avis clients', 'route' => 'owner.reviews', 'icon' => 'M12 17.3 18.2 21l-1.6-7L22 9.3l-7.1-.6L12 2 9.1 8.7 2 9.3 7.4 14 5.8 21 12 17.3Z'],
        ['key' => 'settings', 'label' => 'Paramètres', 'route' => 'owner.settings', 'icon' => 'M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5ZM19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2 3.4-.2-.1a1.7 1.7 0 0 0-1.9.1 8 8 0 0 1-1.7 1l-.3.1-.7-2.1h-4l-.7 2.1-.3-.1a8 8 0 0 1-1.7-1 1.7 1.7 0 0 0-1.9-.1l-.2.1-2-3.4.1-.1A1.7 1.7 0 0 0 4.6 15a8 8 0 0 1 0-2 1.7 1.7 0 0 0-.3-1.9l-.1-.1 2-3.4.2.1a1.7 1.7 0 0 0 1.9-.1 8 8 0 0 1 1.7-1l.3-.1.7 2.1h4l.7-2.1.3.1a8 8 0 0 1 1.7 1 1.7 1.7 0 0 0 1.9.1l.2-.1 2 3.4-.1.1a1.7 1.7 0 0 0-.3 1.9 8 8 0 0 1 0 2Z'],
    ];
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA PEE'">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <div data-owner-mobile-overlay class="fixed inset-0 z-[80] hidden bg-[#07152f]/45 opacity-0 backdrop-blur-sm transition-opacity duration-200 lg:hidden"></div>
        <aside data-owner-mobile-menu class="fixed inset-y-0 left-0 z-[90] hidden w-[min(88vw,330px)] -translate-x-full overflow-y-auto border-r border-white/80 bg-white p-5 shadow-2xl shadow-[#07152f]/25 transition-transform duration-300 lg:hidden">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('owner.dashboard') }}" class="flex min-w-0 items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="min-w-0 leading-none">
                        <span class="block truncate text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block truncate text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">portail propriétaire</span>
                    </span>
                </a>
                <button type="button" data-owner-mobile-close class="grid size-10 shrink-0 place-items-center rounded-full bg-[#f2f6ff] text-[#07152f]" aria-label="Fermer le menu">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="mt-7 rounded-[22px] border border-[#dce6f7] bg-[#f7faff] p-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Partenaire</p>
                <p class="mt-2 truncate text-base font-extrabold text-[#07152f]">{{ $ownerProfile?->business_name ?? 'Espace partenaire' }}</p>
                <p class="mt-1 truncate text-xs font-bold text-[#6f7890]">{{ $ownerProfile?->city ?? 'Ville' }} · {{ $ownerProfile?->country_code ?? 'CI' }}</p>
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
                <a href="{{ route('owner.venues.create') }}" class="flex items-center justify-center rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#07152f]/15 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Ajouter un espace</a>
                @if (auth()->user()?->hasPortal(\App\Enums\UserRole::Client))
                    <a href="{{ route('client.dashboard') }}" class="flex items-center justify-center rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Basculer côté client</a>
                @else
                    <form method="POST" action="{{ route('portals.client.enable') }}">
                        @csrf
                        <button class="w-full rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Demander l’accès client</button>
                    </form>
                @endif
            </div>
        </aside>

        <div class="grid min-h-screen lg:grid-cols-[290px_1fr]">
            <aside class="sticky top-0 hidden h-screen overflow-y-auto border-r border-white/80 bg-white/88 p-5 shadow-2xl shadow-[#173e7a]/8 backdrop-blur-xl lg:block">
                <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">portail propriétaire</span>
                    </span>
                </a>

                <div class="mt-7 rounded-[22px] border border-[#dce6f7] bg-[#f7faff] p-4">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Partenaire</p>
                    <p class="mt-2 truncate text-base font-extrabold text-[#07152f]">{{ $ownerProfile?->business_name ?? 'Espace partenaire' }}</p>
                    <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $ownerProfile?->city ?? 'Ville' }} · {{ $ownerProfile?->country_code ?? 'CI' }}</p>
                </div>

                <div class="mt-3 rounded-[22px] border border-[#dce6f7] bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Formule actuelle</p>
                    <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ $activeSubscription?->subscriptionPlan?->name ?? 'Sans abonnement actif' }}</p>
                    <p class="mt-1 text-xs font-bold text-[#6f7890]">Paiement à BAOBAA : {{ $billingPreferenceLabel }}</p>
                </div>

                <nav class="mt-6 space-y-1.5">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'text-[#5d6b86] hover:bg-[#f0f5ff] hover:text-[#2f6bff]' }}">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <a href="{{ route('owner.venues.create') }}" class="mt-6 flex items-center justify-center rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#07152f]/15 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Ajouter un espace</a>
                @if (auth()->user()?->hasPortal(\App\Enums\UserRole::Client))
                    <a href="{{ route('client.dashboard') }}" class="mt-3 flex items-center justify-center rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Basculer côté client</a>
                @else
                    <form method="POST" action="{{ route('portals.client.enable') }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded-2xl border border-[#c9d8ef] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">Demander l’accès client</button>
                    </form>
                @endif
            </aside>

            <section class="min-w-0">
                <header class="sticky top-0 z-40 border-b border-white/80 bg-white/84 px-5 py-4 shadow-sm shadow-[#173e7a]/5 backdrop-blur-xl sm:px-7">
                    <div class="flex items-start justify-between gap-3">
                        <button type="button" data-owner-mobile-open class="mt-1 grid size-11 shrink-0 place-items-center rounded-2xl border border-[#dce6f7] bg-white text-[#07152f] shadow-sm lg:hidden" aria-label="Ouvrir le menu">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                        </button>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Portail PEE</p>
                            <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f] sm:text-3xl">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('owner-profiles.index') }}" class="hidden rounded-full border border-[#c9d8ef] bg-white px-4 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm md:inline-flex">Partenaires publics</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="rounded-full bg-[#151821] px-3 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] sm:px-4 sm:text-sm">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </header>

                <div class="px-5 py-6 sm:px-7">
                    @if (session('portal_status'))
                        <div class="mb-5 rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session('portal_status') }}</div>
                    @endif

                    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['label' => 'Espaces publiés', 'value' => number_format($activeVenuesCount, 0, ',', ' '), 'hint' => 'visibles côté client'],
                            ['label' => 'Demandes à suivre', 'value' => number_format($pendingBookingsCount, 0, ',', ' '), 'hint' => 'réservations en attente'],
                            ['label' => 'Réservations confirmées', 'value' => number_format($confirmedBookingsCount, 0, ',', ' '), 'hint' => 'créneaux sécurisés'],
                            ['label' => 'Paiements reçus', 'value' => number_format($grossRevenue, 0, ',', ' ').' XOF', 'hint' => $billingPreferenceLabel],
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.querySelector('[data-owner-mobile-overlay]');
            const menu = document.querySelector('[data-owner-mobile-menu]');
            const openButton = document.querySelector('[data-owner-mobile-open]');
            const closeButton = document.querySelector('[data-owner-mobile-close]');

            if (! overlay || ! menu || ! openButton || ! closeButton) {
                return;
            }

            const openMenu = () => {
                overlay.classList.remove('hidden');
                menu.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    menu.classList.remove('-translate-x-full');
                });
            };

            const closeMenu = () => {
                overlay.classList.add('opacity-0');
                menu.classList.add('-translate-x-full');
                document.body.classList.remove('overflow-hidden');

                window.setTimeout(() => {
                    overlay.classList.add('hidden');
                    menu.classList.add('hidden');
                }, 220);
            };

            openButton.addEventListener('click', openMenu);
            closeButton.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
            menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
        });
    </script>
</x-layouts.baobaa>
