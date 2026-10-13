@props([
    'name',
    'url',
    'logoUrl' => null,
    'coverUrl' => null,
    'location' => null,
    'verified' => false,
    'description' => null,
    'tags' => [],
    'meta' => null,
    'actionLabel' => 'Voir le profil',
])

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12']) }}>
    <div class="relative h-36 overflow-hidden bg-[linear-gradient(135deg,#07152f,#2f6bff)]">
        @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="" width="480" height="180" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <span class="absolute inset-0 bg-gradient-to-t from-[#07152f]/55 to-transparent"></span>
        @endif
        @if ($verified)
            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1 text-[11px] font-extrabold text-[#047857] shadow-sm">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                Vérifié
            </span>
        @endif
    </div>

    <div class="relative flex flex-1 flex-col px-5 pb-5">
        <span class="relative z-10 -mt-9 grid size-[72px] shrink-0 place-items-center overflow-hidden rounded-[20px] border-4 border-white bg-[#eaf1ff] text-2xl font-extrabold text-[#2f6bff] shadow-lg shadow-[#173e7a]/10">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo {{ $name }}" width="72" height="72" loading="lazy" decoding="async" referrerpolicy="no-referrer" class="h-full w-full object-cover">
            @else
                {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
            @endif
        </span>
        <h2 class="mt-3 line-clamp-1 text-lg font-extrabold tracking-[-0.015em] text-[#151821]">{{ $name }}</h2>
        @if ($location)
            <p class="mt-0.5 text-sm font-bold text-[#6f7890]">{{ $location }}</p>
        @endif
        @if ($description)
            <p class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-[#52617b]">{{ $description }}</p>
        @endif
        @if (count($tags) > 0)
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($tags as $tag)
                    <span class="rounded-full bg-[#f4f7ff] px-2.5 py-1 text-[11px] font-extrabold text-[#4d5872]">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
        <div class="mt-auto flex items-center justify-between gap-3 border-t border-[#edf2fb] pt-4 [&:not(:first-child)]:mt-4">
            <span class="text-xs font-extrabold text-[#4d5872]">{{ $meta }}</span>
            <span class="inline-flex items-center gap-1 text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">{{ $actionLabel }} <span aria-hidden="true">→</span></span>
        </div>
    </div>
</a>
