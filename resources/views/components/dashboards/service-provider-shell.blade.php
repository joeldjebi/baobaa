@props([
    'title' => 'Dashboard PSE',
    'subtitle' => null,
    'active' => 'overview',
    'profile' => null,
    'activeServicesCount' => 0,
    'draftServicesCount' => 0,
    'requestsCount' => 0,
    'grossRevenue' => 0,
])

@php
    $navigation = [
        ['key' => 'overview', 'label' => 'Vue d’ensemble', 'route' => 'service-provider.dashboard', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
        ['key' => 'services', 'label' => 'Mes services', 'route' => 'service-provider.services', 'icon' => 'M12 3l7 4v10l-7 4-7-4V7l7-4Zm0 0v8m7-4-7 4-7-4'],
        ['key' => 'reservations', 'label' => 'Réservations', 'route' => 'service-provider.reservations', 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
        ['key' => 'products', 'label' => 'Mes articles', 'route' => 'service-provider.products', 'icon' => 'M20 7 12 3 4 7v10l8 4 8-4V7Zm-8 4 8-4M12 11v10M12 11 4 7'],
        ['key' => 'orders', 'label' => 'Commandes', 'route' => 'service-provider.orders', 'icon' => 'M6 6h15l-2 9H8L6 3H3M9 20h.01M18 20h.01'],
        ['key' => 'delivery', 'label' => 'Livraison', 'route' => 'service-provider.delivery-zones', 'icon' => 'M3 7h11v9H3V7Zm11 3h4l3 3v3h-7v-6ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z'],
        ['key' => 'settings', 'label' => 'Paramètres', 'route' => 'service-provider.settings', 'icon' => 'M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5ZM19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2 3.4-.2-.1a1.7 1.7 0 0 0-1.9.1 8 8 0 0 1-1.7 1l-.3.1-.7-2.1h-4l-.7 2.1-.3-.1a8 8 0 0 1-1.7-1 1.7 1.7 0 0 0-1.9-.1l-.2.1-2-3.4.1-.1A1.7 1.7 0 0 0 4.6 15a8 8 0 0 1 0-2 1.7 1.7 0 0 0-.3-1.9l-.1-.1 2-3.4.2.1a1.7 1.7 0 0 0 1.9-.1 8 8 0 0 1 1.7-1l.3-.1.7 2.1h4l.7-2.1.3.1a8 8 0 0 1 1.7 1 1.7 1.7 0 0 0 1.9.1l.2-.1 2 3.4-.1.1a1.7 1.7 0 0 0-.3 1.9 8 8 0 0 1 0 2Z'],
    ];
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA PSE'">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <div class="grid min-h-screen lg:grid-cols-[290px_1fr]">
            <aside class="sticky top-0 hidden h-screen overflow-y-auto border-r border-white/80 bg-white/88 p-5 shadow-2xl shadow-[#173e7a]/8 backdrop-blur-xl lg:block">
                <a href="{{ route('service-provider.dashboard') }}" class="flex items-center justify-center text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto max-w-[170px] rounded-2xl object-contain bg-white p-1 shadow-lg shadow-[#2f6bff]/20 ring-1 ring-[#dbe3f8]" loading="lazy">
                </a>

                <div class="mt-7 rounded-[22px] border border-[#dce6f7] bg-[#f7faff] p-4">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Prestataire</p>
                    <p class="mt-2 truncate text-base font-extrabold text-[#07152f]">{{ $profile?->business_name ?? 'Prestataire BAOBAA' }}</p>
                    <p class="mt-1 truncate text-xs font-bold text-[#6f7890]">{{ $profile?->city ?? 'Ville' }} · {{ $profile?->service_area ?? 'Zone à définir' }}</p>
                </div>

                <nav class="mt-6 space-y-1.5">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition {{ $active === $item['key'] ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'text-[#5d6b86] hover:bg-[#f0f5ff] hover:text-[#2f6bff]' }}">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <a href="{{ route('service-provider.services.create') }}" class="mt-6 flex items-center justify-center rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#07152f]/15 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Ajouter un service</a>
            </aside>

            <section class="min-w-0">
                <header class="sticky top-0 z-40 border-b border-white/80 bg-white/84 px-5 py-4 shadow-sm shadow-[#173e7a]/5 backdrop-blur-xl sm:px-7">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Portail PSE</p>
                            <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.01em] text-[#07152f] sm:text-3xl">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 max-w-2xl text-sm font-semibold text-[#6f7890]">{{ $subtitle }}</p>
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
                    @php
                        $providerVerification = auth()->user()?->serviceProviderProfile?->verification_status;
                    @endphp
                    @if ($providerVerification === \App\Enums\VerificationStatus::Rejected)
                        <div class="mb-5 rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-semibold text-[#b42318]">Votre profil n’a pas été validé par l’équipe BAOBAA : vos prestations ne sont pas visibles par les clients. Contactez-nous pour en savoir plus.</div>
                    @elseif ($providerVerification !== null && $providerVerification !== \App\Enums\VerificationStatus::Verified)
                        <div class="mb-5 rounded-2xl border border-[#fde3a7] bg-[#fffaf0] px-4 py-3 text-sm font-semibold text-[#92400e]">Votre profil est en cours de validation par l’équipe BAOBAA. Vous pouvez déjà préparer vos prestations : elles seront visibles par les clients dès la validation.</div>
                    @endif

                    @if (session('pse_status'))
                        <div class="mb-5 rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session('pse_status') }}</div>
                    @endif

                    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['label' => 'Services publiés', 'value' => number_format($activeServicesCount, 0, ',', ' '), 'hint' => 'visibles côté client'],
                            ['label' => 'Brouillons', 'value' => number_format($draftServicesCount, 0, ',', ' '), 'hint' => 'à compléter'],
                            ['label' => 'Réservations', 'value' => number_format($requestsCount, 0, ',', ' '), 'hint' => 'à traiter'],
                            ['label' => 'Revenus', 'value' => number_format($grossRevenue, 0, ',', ' ').' XOF', 'hint' => 'paiements confirmés'],
                        ] as $metric)
                            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">{{ $metric['label'] }}</p>
                                <p class="mt-2 text-2xl font-extrabold tracking-[-0.01em] text-[#07152f]">{{ $metric['value'] }}</p>
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
