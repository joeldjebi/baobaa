@props([
    'active' => null,
    'ctaLabel' => 'Devenir partenaire',
    'ctaUrl' => null,
])

<header {{ $attributes->merge(['class' => 'baobaa-site-header sticky top-0 z-40 px-5 sm:px-8 baobaa-sticky-stable']) }} data-site-header>
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
        <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3 text-[#2f6bff]">
            <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" width="80" height="40" class="h-10 w-auto rounded-xl object-cover shadow-md shadow-[#2f6bff]/20 transition-transform duration-300 hover:scale-105">
        </a>

        <x-navigation.public-menu :active="$active" />

        <div class="flex items-center gap-2">
            @guest
                <a href="{{ $ctaUrl ?? route('owner.register') }}" class="hidden whitespace-nowrap rounded-full bg-[#07152f] px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-[#2f6bff] hover:shadow-lg hover:shadow-[#2f6bff]/25 xl:inline-flex">{{ $ctaLabel }}</a>
            @endguest
            <x-navigation.cart-link />
            <x-navigation.account-menu />
            <x-navigation.mobile-menu :active="$active" />
        </div>
    </div>
</header>
