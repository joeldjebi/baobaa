@props([
    'listing',
    'badge' => null,
])

<a href="{{ route('venues.show', ['slug' => $listing['slug']]) }}" data-tilt class="baobaa-tilt group block w-full min-w-0 overflow-hidden rounded-[1.45rem] bg-white shadow-sm ring-1 ring-[#dce6f7] hover:shadow-2xl hover:shadow-[#2f6bff]/12">
    <div class="relative aspect-[4/3] overflow-hidden">
        <img src="{{ $listing['image'] }}" alt="{{ $listing['title'] }}" width="400" height="300" decoding="async" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @if ($listing['verified'] ?? false)
            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-extrabold text-[#2f6bff] shadow-sm">Vérifié</span>
        @endif
        @if ($badge)
            <span class="absolute bottom-3 left-3 max-w-[calc(100%-24px)] truncate rounded-full bg-[#07152f]/88 px-3 py-1 text-[11px] font-extrabold text-white backdrop-blur">{{ $badge }}</span>
        @endif
    </div>

    <div class="p-4">
        <p class="truncate text-xs font-bold text-[#6b7b99]">{{ $listing['city'] ?: 'Adresse sur demande' }}</p>
        <h3 class="mt-2 line-clamp-2 min-h-12 text-base font-extrabold leading-6 text-[#081225]">{{ $listing['title'] }}</h3>
        <div class="mt-4 flex items-end justify-between gap-3 border-t border-[#edf2fb] pt-3">
            <p class="text-xs font-semibold text-[#6b7b99]">{{ $listing['guests'] }}</p>
            <p class="text-right leading-tight">
                <span class="block text-[10px] font-bold uppercase tracking-[0.08em] text-[#8a94aa]">dès</span>
                <span class="block whitespace-nowrap text-sm font-extrabold text-[#2f6bff]">{{ $listing['price'] }}</span>
                @isset($listing['price_unit'])
                    <span class="block text-[10px] font-bold text-[#8a94aa]">{{ $listing['price_unit'] }}</span>
                @endisset
            </p>
        </div>
    </div>
</a>
