<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BaobaaCheckout = 'baobaa_checkout';
    case Wave = 'wave';
    case OrangeMoney = 'orange_money';
    case MtnMoney = 'mtn_money';
    case MoovMoney = 'moov_money';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::BaobaaCheckout => 'Paiement sécurisé BAOBAA',
            self::Wave => 'Wave',
            self::OrangeMoney => 'Orange Money',
            self::MtnMoney => 'MTN Money',
            self::MoovMoney => 'Moov Money',
            self::BankTransfer => 'Virement bancaire',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $method): array => [$method->value => $method->label()])
            ->all();
    }

    public static function labelFor(string $value): string
    {
        return self::tryFrom($value)?->label() ?? $value;
    }
}
