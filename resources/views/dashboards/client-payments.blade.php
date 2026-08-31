<x-dashboards.client-shell title="Historique des paiements" subtitle="Suivez vos acomptes, références et statuts de paiement BAOBAA." active="payments" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <form method="GET" class="grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence ou nom de l’espace" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach ($paymentStatusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 grid gap-3 lg:hidden">
            @forelse ($payments as $payment)
                <article class="rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-extrabold text-[#151821]">{{ $payment->booking?->venue?->name }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $payment->reference }}</p>
                        </div>
                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[11px] font-extrabold text-[#2f6bff]">{{ $paymentStatusLabels[$payment->status->value] ?? 'À suivre' }}</span>
                    </div>
                    <p class="mt-4 text-lg font-extrabold text-[#07152f]">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</p>
                    <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $payment->payment_method ?? $payment->provider }} · {{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i') }}</p>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-center text-sm font-semibold text-[#6f7890]">Aucun paiement trouvé.</p>
            @endforelse
        </div>

        <div class="mt-5 hidden overflow-x-auto rounded-2xl border border-[#edf2fb] lg:block">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Méthode</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Date</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($payments as $payment)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $payment->reference }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->booking?->venue?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->payment_method ?? $payment->provider }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $paymentStatusLabels[$payment->status->value] ?? 'À suivre' }}</span></td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucun paiement trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $payments->links() }}</div>
    </section>
</x-dashboards.client-shell>
