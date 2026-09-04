@props([
    'booking',
    'messageRoute',
    'confirmRoute',
    'paymentRoute' => null,
    'canPay' => false,
    'actorLabel' => 'Utilisateur',
])

@php
    use App\Enums\PaymentStatus;
    use App\Enums\ProformaInvoiceStatus;

    $invoice = $booking->proformaInvoice;
    $invoiceStatusLabels = [
        'sent' => 'En attente de validation',
        'accepted_by_client' => 'Confirmée par le client',
        'accepted_by_owner' => 'Confirmée par le partenaire',
        'confirmed' => 'Confirmée par les deux parties',
        'cancelled' => 'Annulée',
    ];
    $pendingPayment = $booking->payments->first(fn ($payment) => in_array($payment->status, [PaymentStatus::Initiated, PaymentStatus::Pending], true));
    $succeededPayment = $booking->payments->first(fn ($payment) => $payment->status === PaymentStatus::Succeeded);
@endphp

<div class="space-y-5">
    @foreach (['booking_status', 'proforma_status', 'payment_status', 'conversation_status'] as $statusKey)
        @if (session($statusKey))
            <div class="rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff] shadow-sm">{{ session($statusKey) }}</div>
        @endif
    @endforeach

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Facture proforma</p>
                <h3 class="mt-1 break-words text-xl font-extrabold tracking-[-0.035em] text-[#07152f]">{{ $invoice?->reference ?? 'Proforma en préparation' }}</h3>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">Base contractuelle avant paiement de l’acompte.</p>
            </div>
            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $invoice ? ($invoiceStatusLabels[$invoice->status->value] ?? 'À suivre') : 'À générer' }}</span>
        </div>

        @if ($invoice)
            <div class="mt-5 overflow-hidden rounded-2xl border border-[#edf2fb]">
                <div class="grid grid-cols-[minmax(0,1fr)_80px_120px_120px] bg-[#f7faff] px-4 py-3 text-xs font-extrabold uppercase text-[#7d8aa7] max-md:hidden">
                    <span>Désignation</span>
                    <span class="text-center">Qté</span>
                    <span class="text-right">Prix</span>
                    <span class="text-right">Total</span>
                </div>
                <div class="divide-y divide-[#edf2fb]">
                    @foreach ($invoice->items as $item)
                        <div class="grid gap-2 px-4 py-4 text-sm md:grid-cols-[minmax(0,1fr)_80px_120px_120px] md:items-center">
                            <div class="min-w-0">
                                <p class="font-extrabold text-[#151821]">{{ $item->label }}</p>
                                @if ($item->description)
                                    <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $item->description }}</p>
                                @endif
                            </div>
                            <p class="font-bold text-[#52617b] md:text-center">{{ number_format($item->quantity, 0, ',', ' ') }}</p>
                            <p class="font-bold text-[#52617b] md:text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} {{ $invoice->currency }}</p>
                            <p class="font-extrabold text-[#07152f] md:text-right">{{ number_format($item->total_price, 0, ',', ' ') }} {{ $invoice->currency }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Total proforma</p>
                    <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}</p>
                </div>
                <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Acompte à payer</p>
                    <p class="mt-2 text-lg font-extrabold text-[#07152f]">{{ number_format($invoice->deposit_amount, 0, ',', ' ') }} {{ $invoice->currency }}</p>
                </div>
                <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Paiement</p>
                    <p class="mt-2 text-sm font-extrabold text-[#07152f]">{{ $succeededPayment ? 'Acompte payé' : ($pendingPayment ? 'Acompte en attente' : 'Aucun paiement ouvert') }}</p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                @if ($invoice->status !== ProformaInvoiceStatus::Confirmed)
                    <form method="POST" action="{{ $confirmRoute }}">
                        @csrf
                        <button class="rounded-2xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Confirmer la proforma</button>
                    </form>
                @endif

                @if ($canPay && $paymentRoute)
                    <form method="POST" action="{{ $paymentRoute }}">
                        @csrf
                        <button class="rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#07152f]/15">Payer l’acompte test</button>
                    </form>
                @elseif ($paymentRoute && $pendingPayment)
                    <button type="button" disabled class="cursor-not-allowed rounded-2xl bg-[#edf2fb] px-4 py-3 text-sm font-extrabold text-[#8a94aa]">Paiement test après double confirmation</button>
                @endif
            </div>
        @else
            <p class="mt-5 rounded-2xl bg-[#fff8eb] p-4 text-sm font-bold text-[#9a6700]">La proforma sera générée automatiquement dès la création complète de la réservation.</p>
        @endif
    </section>

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Négociation</p>
                <h3 class="mt-1 text-xl font-extrabold tracking-[-0.035em] text-[#07152f]">Échanges client · partenaire</h3>
            </div>
            <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-xs font-extrabold text-[#52617b]">{{ $booking->messages->count() }} message(s)</span>
        </div>

        <form method="POST" action="{{ $messageRoute }}" class="mt-5 grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7]">
            @csrf
            <textarea name="message" rows="3" placeholder="Ajoutez une précision, une demande ou une réponse professionnelle..." class="w-full rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-semibold text-[#151821] outline-none focus:border-[#2f6bff]">{{ old('message') }}</textarea>
            <div class="grid gap-3 md:grid-cols-[minmax(0,220px)_auto] md:items-center">
                <label class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-2.5">
                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Prix proposé</span>
                    <input type="number" min="1" name="proposed_amount" value="{{ old('proposed_amount') }}" placeholder="Optionnel" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#07152f] outline-none placeholder:text-[#9ca7bb]">
                </label>
                <button class="rounded-2xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Envoyer</button>
            </div>
        </form>

        <div class="mt-5 space-y-3">
            @forelse ($booking->messages as $message)
                @php($isMine = (int) $message->sender_id === (int) auth()->id())
                <article class="rounded-2xl border {{ $isMine ? 'border-[#cfe0ff] bg-[#f4f8ff]' : 'border-[#edf2fb] bg-white' }} p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-extrabold text-[#151821]">{{ $isMine ? $actorLabel : $message->sender?->name }}</p>
                        <p class="text-xs font-bold text-[#8a94aa]">{{ $message->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">{{ $message->message }}</p>
                    @if ($message->proposed_amount)
                        <p class="mt-3 inline-flex rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">Proposition : {{ number_format($message->proposed_amount, 0, ',', ' ') }} {{ $message->currency }}</p>
                    @endif
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-center text-sm font-semibold text-[#6f7890]">Aucun échange pour le moment.</p>
            @endforelse
        </div>
    </section>
</div>
