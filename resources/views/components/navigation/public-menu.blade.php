@props([
    'active' => 'explore',
    'tone' => 'light',
])

@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $isClient = $user?->hasPortal(UserRole::Client) ?? false;
    $isOwner = $user?->hasPortal(UserRole::Owner) ?? false;
    $ownerCtaUrl = $user ? route('portals.owner.request.form') : route('owner.register');
    $linkBase = 'rounded-full px-4 py-2 transition hover:bg-white hover:text-[#2f6bff]';
    $activeClass = 'rounded-full bg-white px-4 py-2 text-[#2f6bff] shadow-sm';
@endphp

<nav {{ $attributes->merge(['class' => 'hidden items-center rounded-full border border-[#e2e9f8] bg-[#f8fbff]/90 px-2 py-1 text-sm font-extrabold text-[#4d5872] lg:flex']) }}>
    <a href="{{ url('/') }}" class="{{ $active === 'explore' ? $activeClass : $linkBase }}">Explorer</a>
    <a href="{{ route('venues.index') }}" class="{{ $active === 'venues' ? $activeClass : $linkBase }}">Espaces</a>
    <a href="{{ route('owner-profiles.index') }}" class="{{ $active === 'partners' ? $activeClass : $linkBase }}">Partenaires</a>

    @if ($isClient)
        <a href="{{ route('client.reservations') }}" class="{{ $active === 'client-reservations' ? $activeClass : $linkBase }}">Mes réservations</a>
    @elseif ($isOwner)
        <a href="{{ route('owner.dashboard') }}" class="{{ $active === 'owner-dashboard' ? $activeClass : $linkBase }}">Mon espace pro</a>
    @else
        <a href="{{ $ownerCtaUrl }}" class="{{ $active === 'list-venue' ? $activeClass : $linkBase }}">Devenir partenaire</a>
    @endif
</nav>
