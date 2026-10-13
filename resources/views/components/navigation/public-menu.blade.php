@props([
    'active' => 'explore',
    'tone' => 'light',
])

@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $isClient = $user?->hasPortal(UserRole::Client) ?? false;
    $isOwner = $user?->hasPortal(UserRole::Owner) ?? false;

    $links = [
        'explore' => ['Explorer', url('/')],
        'venues' => ['Espaces', route('venues.index')],
        'partners' => ['Partenaires', route('owner-profiles.index')],
        'event-services' => ['Professionnels', route('service-provider-profiles.index')],
        'products' => ['Location et vente', route('products.index')],
    ];

    if ($isClient) {
        $links['client-reservations'] = ['Mes réservations', route('client.reservations')];
    } elseif ($isOwner) {
        $links['owner-dashboard'] = ['Mon espace pro', route('owner.dashboard')];
    }
@endphp

<nav {{ $attributes->merge(['class' => 'baobaa-nav relative hidden items-center rounded-full border border-[#e2e9f8] bg-[#f6f8fd] p-1 text-sm font-semibold text-[#4d5872] lg:flex']) }} data-nav aria-label="Navigation principale">
    <span class="baobaa-nav-indicator" data-nav-indicator aria-hidden="true"></span>
    @foreach ($links as $key => [$label, $url])
        <a href="{{ $url }}" @if ($active === $key) aria-current="page" @endif class="relative z-10 whitespace-nowrap rounded-full px-3.5 py-2 transition-colors duration-200 hover:text-[#07152f] aria-[current=page]:text-[#2f6bff] xl:px-4">{{ $label }}</a>
    @endforeach
</nav>
