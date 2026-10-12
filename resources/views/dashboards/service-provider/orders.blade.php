<x-dashboards.service-provider-shell title="Commandes" subtitle="Préparez, livrez et suivez le retour du matériel loué." active="orders" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <form method="GET" class="grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_200px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence ou client" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Filtrer</button>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Commande</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Remise</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Caution</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4"><p class="font-extrabold text-[#151821]">{{ $order->reference }}</p><p class="text-xs font-bold text-[#6f7890]">{{ $order->paid_at?->format('d/m/Y H:i') }} · {{ $order->items->sum('quantity') }} article(s)</p></td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $order->client?->name }}</td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $order->fulfillment_method->label() }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $order->status->label() }}</span></td>
                            <td class="px-4 py-4 text-xs font-bold text-[#52617b]">{{ $order->deposit_status->label() }}</td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('service-provider.orders.show', $order) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucune commande pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $orders->links() }}</div>
    </section>
</x-dashboards.service-provider-shell>
