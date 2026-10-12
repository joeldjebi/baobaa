<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductMode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    /**
     * Statuses the provider can move the order to from its current status.
     *
     * @return array<int, OrderStatus>
     */
    public function nextStatuses(Order $order): array
    {
        $order->loadMissing('items');

        return match ($order->status) {
            OrderStatus::Paid => [OrderStatus::Preparing, OrderStatus::Delivered],
            OrderStatus::Preparing => [OrderStatus::Delivered],
            OrderStatus::Delivered => $order->hasRentals() ? [OrderStatus::Returned] : [OrderStatus::Completed],
            OrderStatus::Returned => [OrderStatus::Completed],
            default => [],
        };
    }

    public function advance(Order $order, OrderStatus $status): void
    {
        if (! in_array($status, $this->nextStatuses($order), true)) {
            throw ValidationException::withMessages(['status' => 'Cette étape n’est pas possible pour cette commande.']);
        }

        if ($status === OrderStatus::Completed && $order->deposit_status === DepositStatus::Held) {
            throw ValidationException::withMessages(['status' => 'Restituez ou retenez la caution avant de terminer la commande.']);
        }

        $order->update(['status' => $status]);
    }

    /**
     * Cancel a paid order before handover: sold units return to stock and the deposit is released.
     */
    public function cancel(Order $order): void
    {
        if (! in_array($order->status, [OrderStatus::Paid, OrderStatus::Preparing], true)) {
            throw ValidationException::withMessages(['status' => 'Une commande déjà livrée ne peut plus être annulée.']);
        }

        DB::transaction(function () use ($order): void {
            $order->items
                ->filter(fn (OrderItem $item): bool => $item->mode === ProductMode::Sale && $item->product_id !== null)
                ->each(fn (OrderItem $item) => Product::query()->whereKey($item->product_id)->increment('stock_quantity', $item->quantity));

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'deposit_status' => $order->deposit_status === DepositStatus::Held ? DepositStatus::Released : $order->deposit_status,
            ]);
        });
    }

    /**
     * Release the deposit, or retain part of it with a reason, once rented items are back.
     */
    public function settleDeposit(Order $order, int $retainedAmount, ?string $reason): void
    {
        if ($order->deposit_status !== DepositStatus::Held || $order->status !== OrderStatus::Returned) {
            throw ValidationException::withMessages(['deposit' => 'La caution se règle une fois le matériel retourné.']);
        }

        if ($retainedAmount > $order->deposit_total) {
            throw ValidationException::withMessages(['retained_amount' => 'Le montant retenu ne peut pas dépasser la caution.']);
        }

        if ($retainedAmount > 0 && blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Indiquez la raison de la retenue sur caution.']);
        }

        $order->update([
            'deposit_status' => $retainedAmount > 0 ? DepositStatus::Retained : DepositStatus::Released,
            'deposit_retained_amount' => $retainedAmount,
            'deposit_retention_reason' => $retainedAmount > 0 ? $reason : null,
        ]);
    }
}
