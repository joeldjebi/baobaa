@php
    $cartItemsCount = auth()->user()?->hasPortal(\App\Enums\UserRole::Client) ? auth()->user()->cartItems()->count() : null;
@endphp

@if ($cartItemsCount !== null)
    <a href="{{ route('cart.show') }}" {{ $attributes->merge(['class' => 'relative grid size-11 place-items-center rounded-full bg-white text-[#151821] shadow-sm ring-1 ring-[#e2e9f8] transition hover:bg-[#f4f7ff]']) }} aria-label="Panier ({{ $cartItemsCount }} article{{ $cartItemsCount > 1 ? 's' : '' }})">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-2 9H8L6 3H3"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/></svg>
        @if ($cartItemsCount > 0)
            <span class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-[#2f6bff] px-1 text-[10px] font-extrabold text-white">{{ $cartItemsCount }}</span>
        @endif
    </a>
@endif
