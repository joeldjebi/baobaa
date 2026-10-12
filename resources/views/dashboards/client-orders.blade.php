<x-dashboards.client-shell title="Mes commandes" subtitle="Matériel loué ou acheté : suivez la préparation, la livraison et vos cautions." active="orders" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    @if (session('order_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('order_status') }}</div>
    @endif

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="grid gap-3">
            @forelse ($orders as $order)
                <a href="{{ route('client.orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#edf2fb] transition hover:ring-[#2f6bff]">
                    <div>
                        <p class="font-extrabold text-[#07152f]">{{ $order->reference }} · {{ $order->serviceProviderProfile?->business_name }}</p>
                        <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $order->paid_at?->format('d/m/Y') }} · {{ $order->items->sum('quantity') }} article(s) · {{ $order->fulfillment_method->label() }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $order->status->label() }}</span>
                        <span class="text-sm font-extrabold text-[#07152f]">{{ number_format($order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-[#cbd8f4] p-8 text-center">
                    <p class="text-sm font-semibold text-[#6f7890]">Aucune commande pour le moment.</p>
                    <a href="{{ route('products.index') }}" class="mt-3 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white">Louer ou acheter du matériel</a>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $orders->links() }}</div>
    </section>
</x-dashboards.client-shell>
