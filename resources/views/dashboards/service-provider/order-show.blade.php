<x-dashboards.service-provider-shell :title="'Commande '.$order->reference" subtitle="Faites avancer la commande étape par étape." active="orders" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <a href="{{ route('service-provider.orders') }}" class="mb-4 inline-flex text-sm font-extrabold text-[#2f6bff]">← Commandes</a>

    @if (session('pse_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('pse_status') }}</div>
    @endif
    <x-forms.validation-errors class="mb-4" />

    <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
        <x-orders.summary :order="$order" />

        <aside class="space-y-4">
            <section class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Client</p>
                <p class="mt-1 font-extrabold text-[#151821]">{{ $order->client?->name }}</p>
                <p class="text-sm font-semibold text-[#6f7890]">{{ $order->client?->phone }} · {{ $order->client?->email }}</p>
                @if ($order->client_notes)
                    <p class="mt-3 rounded-2xl bg-[#f7faff] p-3 text-sm font-semibold text-[#52617b]">{{ $order->client_notes }}</p>
                @endif
            </section>

            <section class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Statut</p>
                <p class="mt-1 text-lg font-extrabold text-[#07152f]">{{ $order->status->label() }}</p>
                <div class="mt-3 grid gap-2">
                    @foreach ($nextStatuses as $nextStatus)
                        <form method="POST" action="{{ route('service-provider.orders.status', $order) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $nextStatus->value }}">
                            <button class="w-full rounded-2xl bg-[#2f6bff] px-4 py-2.5 text-sm font-extrabold text-white">Passer à : {{ $nextStatus->label() }}</button>
                        </form>
                    @endforeach
                    @if (in_array($order->status, [\App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Preparing], true))
                        <form method="POST" action="{{ route('service-provider.orders.status', $order) }}" onsubmit="return confirm('Annuler cette commande ? Le client sera remboursé.');">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button class="w-full rounded-2xl border border-[#ffd0d0] px-4 py-2.5 text-sm font-extrabold text-[#b42318]">Annuler la commande</button>
                        </form>
                    @endif
                </div>
            </section>

            @if ($order->deposit_status === \App\Enums\DepositStatus::Held && $order->status === \App\Enums\OrderStatus::Returned)
                <form method="POST" action="{{ route('service-provider.orders.deposit', $order) }}" class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                    @csrf
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Caution de {{ number_format($order->deposit_total, 0, ',', ' ') }} {{ $order->currency }}</p>
                    <label class="mt-3 block">
                        <span class="mb-1 block text-sm font-extrabold text-[#17233f]">Montant retenu (0 = restitution complète)</span>
                        <input type="number" name="retained_amount" min="0" max="{{ $order->deposit_total }}" value="{{ old('retained_amount', 0) }}" class="h-11 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
                    </label>
                    <label class="mt-3 block">
                        <span class="mb-1 block text-sm font-extrabold text-[#17233f]">Raison de la retenue</span>
                        <textarea name="reason" rows="2" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-2 text-sm font-bold">{{ old('reason') }}</textarea>
                    </label>
                    <button class="mt-3 w-full rounded-2xl bg-[#07152f] px-4 py-2.5 text-sm font-extrabold text-white">Régler la caution</button>
                </form>
            @endif
        </aside>
    </div>
</x-dashboards.service-provider-shell>
