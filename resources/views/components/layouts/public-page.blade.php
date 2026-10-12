@props([
    'title',
    'eyebrow' => null,
    'heading',
    'intro' => null,
    'description' => null,
    'active' => null,
])

<x-layouts.baobaa :title="$title.' - BAOBAA'" :description="$description ?? $intro">
    <main class="min-h-screen bg-[#f6f8fc] text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
                </a>

                <x-navigation.public-menu :active="$active" />

                <div class="flex items-center gap-2">
                    <x-navigation.account-menu />
                    <x-navigation.mobile-menu :active="$active" />
                </div>
            </div>
        </header>

        <section class="mx-auto max-w-4xl px-5 py-10 sm:px-8 lg:py-14">
            @if ($eyebrow)
                <p class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">{{ $eyebrow }}</p>
            @endif
            <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-[-0.045em] text-[#121722] sm:text-5xl">{{ $heading }}</h1>
            @if ($intro)
                <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-[#667088]">{{ $intro }}</p>
            @endif

            <div class="mt-8 rounded-[28px] bg-white p-6 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-9">
                {{ $slot }}
            </div>
        </section>

        <x-navigation.public-footer />
    </main>
</x-layouts.baobaa>
