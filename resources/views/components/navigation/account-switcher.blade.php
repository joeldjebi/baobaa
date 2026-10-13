@props([
    'mode' => 'login',
    'current' => 'client',
])

@php
    $tabs = [
        'client' => ['Client', route('portal.login', ['portal' => 'client']), route('client.register')],
        'proprietaire' => ['Propriétaire', route('portal.login', ['portal' => 'proprietaire']), route('owner.register')],
        'prestataire' => ['Professionnel', route('portal.login', ['portal' => 'prestataire']), route('service-provider.register')],
    ];
@endphp

{{-- Lets visitors switch between the client, venue owner and professional login or sign-up pages. --}}
<nav {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-1 rounded-2xl bg-[#e9eef8] p-1 text-[13px] font-semibold sm:text-sm']) }} aria-label="{{ $mode === 'register' ? 'Type de compte à créer' : 'Type de compte' }}">
    @foreach ($tabs as $key => [$label, $loginUrl, $registerUrl])
        <a href="{{ $mode === 'register' ? $registerUrl : $loginUrl }}" @if ($key === $current) aria-current="page" @endif @class([
            'truncate rounded-xl px-1 py-2 text-center transition sm:px-2',
            'bg-white text-[#07152f] shadow-sm' => $key === $current,
            'text-[#5d6d89] hover:text-[#07152f]' => $key !== $current,
        ])>{{ $label }}</a>
    @endforeach
</nav>
