@props([
    'active' => null,
])

<header {{ $attributes->merge(['class' => 'baobaa-site-header sticky top-0 z-40 px-5 sm:px-8 baobaa-sticky-stable']) }} data-site-header>
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
        <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3 text-[#2f6bff]">
            <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" width="80" height="40" class="h-10 w-auto rounded-xl object-cover shadow-md shadow-[#2f6bff]/20 transition-transform duration-300 hover:scale-105">
        </a>

        <x-navigation.public-menu :active="$active" />

        <div class="flex items-center gap-2">
            <x-navigation.cart-link />
            <x-navigation.account-menu />
            <x-navigation.mobile-menu :active="$active" />
        </div>
    </div>
</header>
