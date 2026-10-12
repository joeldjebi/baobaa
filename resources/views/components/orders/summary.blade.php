@props(['order'])

@php($money = fn (int $amount): string => number_format($amount, 0, ',', ' ').' '.$order->currency)

<div {{ $attributes->merge(['class' => 'rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]']) }}>
    <div class="space-y-2">
        @foreach ($order->items as $item)
            <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                <div>
                    <p class="text-sm font-extrabold text-[#151821]">{{ $item->quantity }} × {{ $item->product_name }}</p>
                    <p class="mt-1 text-xs font-bold text-[#6f7890]">
                        {{ $item->mode->label() }}
                        @if ($item->rental_starts_on)
                            · du {{ $item->rental_starts_on->format('d/m/Y') }} au {{ $item->rental_ends_on->format('d/m/Y') }} ({{ $item->rental_days }} jour{{ $item->rental_days > 1 ? 's' : '' }})
                        @endif
                        @if ($item->deposit_amount > 0)
                            · caution {{ $money((int) $item->deposit_amount) }}
                        @endif
                    </p>
                </div>
                <p class="text-sm font-extrabold text-[#07152f]">{{ $money((int) $item->line_total) }}</p>
            </div>
        @endforeach
    </div>
    <dl class="mt-4 space-y-1.5 border-t border-[#edf2fb] pt-4 text-sm font-bold text-[#52617b]">
        <div class="flex justify-between"><dt>Articles</dt><dd class="text-[#151821]">{{ $money((int) $order->items_total) }}</dd></div>
        <div class="flex justify-between"><dt>{{ $order->fulfillment_method->label() }}{{ $order->delivery_city ? ' · '.$order->delivery_city : '' }}</dt><dd class="text-[#151821]">{{ $money((int) $order->delivery_fee) }}</dd></div>
        @if ($order->deposit_total > 0)
            <div class="flex justify-between"><dt>Caution ({{ mb_strtolower($order->deposit_status->label()) }})</dt><dd class="text-[#151821]">{{ $money((int) $order->deposit_total) }}</dd></div>
        @endif
        <div class="flex justify-between border-t border-[#edf2fb] pt-2 text-base font-extrabold text-[#07152f]"><dt>Total payé</dt><dd>{{ $money((int) $order->total_amount) }}</dd></div>
    </dl>
    @if ($order->deposit_retained_amount > 0)
        <p class="mt-3 rounded-2xl bg-[#fff6f6] p-3 text-xs font-bold text-[#b42318]">Retenue sur caution : {{ $money((int) $order->deposit_retained_amount) }} — {{ $order->deposit_retention_reason }}</p>
    @endif
    @if ($order->delivery_address)
        <p class="mt-3 text-xs font-semibold text-[#6f7890]">Adresse de livraison : {{ $order->delivery_address }}{{ $order->delivery_date ? ' · le '.$order->delivery_date->format('d/m/Y') : '' }}</p>
    @endif
</div>
