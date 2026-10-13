@props([
    'active' => null,
])

@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $links = [
        'explore' => ['label' => 'Explorer', 'url' => url('/')],
        'venues' => ['label' => 'Espaces', 'url' => route('venues.index')],
        'partners' => ['label' => 'Partenaires', 'url' => route('owner-profiles.index')],
        'event-services' => ['label' => 'Professionnels', 'url' => route('service-provider-profiles.index')],
        'products' => ['label' => 'Location et vente', 'url' => route('products.index')],
    ];

    if ($user?->hasPortal(UserRole::Client)) {
        $links['client-reservations'] = ['label' => 'Mes réservations', 'url' => route('client.reservations')];
        $links['cart'] = ['label' => 'Mon panier', 'url' => route('cart.show')];
    } elseif ($user?->hasPortal(UserRole::Owner)) {
        $links['owner-dashboard'] = ['label' => 'Mon espace pro', 'url' => route('owner.dashboard')];
    } elseif ($user?->hasPortal(UserRole::ServiceProvider)) {
        $links['provider-dashboard'] = ['label' => 'Mon espace pro', 'url' => route('service-provider.dashboard')];
    }

    $links['contact'] = ['label' => 'Contact', 'url' => route('pages.contact')];
@endphp

<div {{ $attributes->merge(['class' => 'lg:hidden']) }} data-mobile-menu>
    <button type="button" data-mobile-menu-open class="grid size-11 place-items-center rounded-full bg-white text-[#151821] shadow-sm ring-1 ring-[#e2e9f8] transition hover:bg-[#f4f7ff] focus:outline-none focus:ring-4 focus:ring-[#2f6bff]/15" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="baobaa-mobile-menu">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>

    <div id="baobaa-mobile-menu" data-mobile-menu-panel class="fixed inset-0 z-[1100] hidden" role="dialog" aria-modal="true" aria-label="Menu de navigation">
        <div data-mobile-menu-close class="absolute inset-0 bg-[#071225]/45 backdrop-blur-sm"></div>

        <nav class="absolute inset-y-0 right-0 flex w-[min(86vw,340px)] flex-col overflow-y-auto bg-white p-5 shadow-2xl">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-10 w-auto rounded-xl object-contain" loading="lazy">
                </a>
                <button type="button" data-mobile-menu-close class="grid size-10 place-items-center rounded-full text-2xl font-light text-[#4d5872] transition hover:bg-[#f4f7ff]" aria-label="Fermer le menu">&times;</button>
            </div>

            <div class="mt-6 grid gap-1 text-base font-semibold">
                @foreach ($links as $key => $link)
                    <a href="{{ $link['url'] }}" @class([
                        'rounded-2xl px-4 py-3 transition',
                        'bg-[#eef4ff] text-[#2f6bff]' => $active === $key,
                        'text-[#151821] hover:bg-[#f4f7ff] hover:text-[#2f6bff]' => $active !== $key,
                    ])>{{ $link['label'] }}</a>
                @endforeach
            </div>

            <div class="mt-auto border-t border-[#edf2fb] pt-5">
                @auth
                    <p class="truncate px-1 text-sm font-extrabold text-[#151821]">{{ $user->name }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded-2xl border border-[#ffd0d0] px-4 py-3 text-sm font-extrabold text-[#b42318]">Déconnexion</button>
                    </form>
                @else
                    <p class="px-1 text-xs font-semibold uppercase tracking-[0.08em] text-[#7d879d]">Mon compte</p>
                    <x-navigation.account-actors mode="both" class="mt-2 gap-2" />
                @endauth
            </div>
        </nav>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-mobile-menu]').forEach((menu) => {
                const panel = menu.querySelector('[data-mobile-menu-panel]');
                const openButton = menu.querySelector('[data-mobile-menu-open]');

                document.body.appendChild(panel);

                const toggle = (isOpen) => {
                    panel.classList.toggle('hidden', ! isOpen);
                    openButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    document.body.classList.toggle('overflow-hidden', isOpen);
                };

                openButton.addEventListener('click', () => toggle(true));
                panel.querySelectorAll('[data-mobile-menu-close]').forEach((element) => element.addEventListener('click', () => toggle(false)));
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && ! panel.classList.contains('hidden')) {
                        toggle(false);
                    }
                });
            });
        });
    </script>
@endonce
