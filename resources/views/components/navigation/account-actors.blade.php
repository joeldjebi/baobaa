@props([
    'mode' => 'login',
])

@php
    $actors = [
        [
            'title' => 'Client',
            'text' => 'J’organise un événement',
            'login' => route('portal.login', ['portal' => 'client']),
            'register' => route('client.register'),
            'color' => 'bg-[#eef4ff] text-[#2f6bff]',
            'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z',
        ],
        [
            'title' => 'Propriétaire d’espace',
            'text' => 'Je loue ma salle ou mon lieu',
            'login' => route('portal.login', ['portal' => 'proprietaire']),
            'register' => route('owner.register'),
            'color' => 'bg-[#eef1f6] text-[#07152f]',
            'icon' => 'M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M8 7h2M12 7h2M8 11h2M12 11h2M2 21h20',
        ],
        [
            'title' => 'Professionnel',
            'text' => 'Je propose mes services ou mon matériel',
            'login' => route('portal.login', ['portal' => 'prestataire']),
            'register' => route('service-provider.register'),
            'color' => 'bg-[#e8f8f1] text-[#0f9f6e]',
            'icon' => 'M9 18V5l12-2v13M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm12-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        ],
    ];
@endphp

{{-- Lists the three account types (client, venue owner, professional) with their login and/or sign-up links. --}}
<div {{ $attributes->merge(['class' => 'grid gap-1']) }}>
    @foreach ($actors as $actor)
        @if ($mode === 'both')
            <div class="flex items-center gap-3 rounded-2xl bg-[#f7f9fd] p-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $actor['color'] }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $actor['icon'] }}"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-[#07152f]">{{ $actor['title'] }}</span>
                    <span class="mt-1.5 flex gap-1.5 text-xs font-semibold">
                        <a href="{{ $actor['login'] }}" class="rounded-full bg-white px-3 py-1.5 text-[#07152f] ring-1 ring-[#dce6f7] hover:text-[#2f6bff]">Connexion</a>
                        <a href="{{ $actor['register'] }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-white hover:bg-[#2f6bff]">Inscription</a>
                    </span>
                </span>
            </div>
        @else
            <a href="{{ $mode === 'register' ? $actor['register'] : $actor['login'] }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-[#f4f7ff]">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $actor['color'] }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $actor['icon'] }}"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-[#07152f]">{{ $actor['title'] }}</span>
                    <span class="block text-xs font-medium text-[#6f7890]">{{ $actor['text'] }}</span>
                </span>
                <span class="text-[#9aa3b5] transition group-hover:translate-x-0.5 group-hover:text-[#2f6bff]" aria-hidden="true">→</span>
            </a>
        @endif
    @endforeach
</div>
