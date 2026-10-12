@props(['active' => null])

<header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
    <div class="mx-auto flex max-w-7xl items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
            <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
        </a>

        <x-navigation.public-menu :active="$active" />

        <div class="flex items-center gap-2">
            <x-navigation.cart-link />
            <x-navigation.account-menu />
            <x-navigation.mobile-menu :active="$active" />
        </div>
    </div>
</header>
