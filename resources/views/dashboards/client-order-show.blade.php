<x-dashboards.client-shell :title="'Commande '.$order->reference" :subtitle="'Prestataire : '.$order->serviceProviderProfile?->business_name" active="orders" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <a href="{{ route('client.orders') }}" class="mb-4 inline-flex text-sm font-extrabold text-[#2f6bff]">← Mes commandes</a>

    @php
        $steps = $order->hasRentals()
            ? [\App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Preparing, \App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Returned, \App\Enums\OrderStatus::Completed]
            : [\App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Preparing, \App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Completed];
        $currentIndex = array_search($order->status, $steps, true);
    @endphp

    @if ($order->status === \App\Enums\OrderStatus::Cancelled)
        <p class="mb-4 rounded-2xl bg-[#fff6f6] p-4 text-sm font-extrabold text-[#b42318]">Cette commande a été annulée le {{ $order->cancelled_at?->format('d/m/Y') }}. Le montant payé vous est remboursé.</p>
    @else
        <ol class="mb-5 grid gap-2 {{ count($steps) === 5 ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }}">
            @foreach ($steps as $index => $step)
                <li @class([
                    'rounded-2xl px-3 py-2 text-center text-xs font-extrabold',
                    'bg-[#2f6bff] text-white' => $currentIndex !== false && $index <= $currentIndex,
                    'bg-white text-[#8a94aa] ring-1 ring-[#dce6f7]' => $currentIndex === false || $index > $currentIndex,
                ])>{{ $step->label() }}</li>
            @endforeach
        </ol>
    @endif

    <x-orders.summary :order="$order" />
</x-dashboards.client-shell>
