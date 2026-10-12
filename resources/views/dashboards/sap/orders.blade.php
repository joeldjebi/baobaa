<x-dashboards.sap-shell title="Commandes de matériel" subtitle="Suivez les commandes, les cautions retenues et modérez les articles publiés." active="orders" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    @if (session('sap_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('sap_status') }}</div>
    @endif

    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_200px_auto_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence, client ou prestataire" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
            <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <label class="flex h-12 items-center gap-2 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><input type="checkbox" name="retained_deposits" value="1" @checked(request()->boolean('retained_deposits'))> Cautions retenues</label>
            <button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button>
        </form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]"><tr><th class="px-4 py-3">Commande</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Prestataire</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Commission</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Caution</th></tr></thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-4"><p class="font-extrabold text-[#07152f]">{{ $order->reference }}</p><p class="text-xs font-bold text-[#64708a]">{{ $order->paid_at?->format('d/m/Y') }}</p></td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $order->client?->name }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $order->serviceProviderProfile?->business_name }}</td>
                            <td class="px-4 py-4 font-extrabold">{{ number_format($order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
                            <td class="px-4 py-4 font-bold">{{ number_format($order->commission_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
                            <td class="px-4 py-4">{{ $order->status->label() }}</td>
                            <td class="px-4 py-4 text-xs font-bold">{{ $order->deposit_status->label() }}@if ($order->deposit_retained_amount > 0)<span class="block text-[#b42318]">{{ number_format($order->deposit_retained_amount, 0, ',', ' ') }} {{ $order->currency }} · {{ $order->deposit_retention_reason }}</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $orders->links() }}</div>
    </section>

    <section class="mt-6 rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <h2 class="text-lg font-extrabold text-[#07152f]">Articles récemment publiés</h2>
        <div class="mt-4 grid gap-2">
            @forelse ($products as $product)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                    <p class="font-extrabold text-[#07152f]">{{ $product->name }} <span class="ml-2 text-xs font-bold text-[#64708a]">{{ $product->serviceProviderProfile?->business_name }} · {{ $product->category?->name }} · {{ $product->status->value }}</span></p>
                    <form method="POST" action="{{ route('sap.products.toggle', $product) }}">
                        @csrf
                        <button class="rounded-full bg-white px-3 py-1 text-xs font-extrabold {{ $product->status->value === 'suspended' ? 'text-[#2f6bff] ring-1 ring-[#dce6f7]' : 'text-[#b42318] ring-1 ring-[#ffd0d0]' }}">{{ $product->status->value === 'suspended' ? 'Réactiver' : 'Masquer du catalogue' }}</button>
                    </form>
                </div>
            @empty
                <p class="text-sm font-semibold text-[#64708a]">Aucun article publié.</p>
            @endforelse
        </div>
    </section>
</x-dashboards.sap-shell>
