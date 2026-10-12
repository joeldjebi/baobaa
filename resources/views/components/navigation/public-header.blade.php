@props([
    'active' => null,
    'ctaLabel' => 'Devenir partenaire',
    'ctaUrl' => null,
])

<header {{ $attributes->merge(['class' => 'sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable']) }}>
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
        <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3 text-[#2f6bff]">
            <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" width="88" height="44" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25">
        </a>

        <x-navigation.public-menu :active="$active" />

        <div class="flex items-center gap-2">
            @guest
                <a href="{{ $ctaUrl ?? route('owner.register') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] xl:inline-flex">{{ $ctaLabel }}</a>
            @endguest
            <x-navigation.cart-link />
            <x-navigation.account-menu />
            <x-navigation.mobile-menu :active="$active" />
        </div>
    </div>
</header>
