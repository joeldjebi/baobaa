<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'event_project_id',
        'order_id',
        'provider_type',
        'provider_id',
        'payer_id',
        'reference',
        'provider',
        'provider_reference',
        'payment_method',
        'status',
        'amount',
        'currency',
        'provider_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'provider_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function eventProject(): BelongsTo
    {
        return $this->belongsTo(EventProject::class);
    }

    /**
     * Service provider paid through an event project deposit (provider_type = service_provider_profile).
     */
    public function serviceProviderProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderProfile::class, 'provider_id');
    }

    /**
     * What the payment is for, readable by clients and the SAP.
     */
    public function subjectLabel(): string
    {
        if ($this->booking_id) {
            return $this->booking?->venue?->name ?? 'Réservation d’espace';
        }

        if ($this->order_id) {
            return 'Commande '.$this->order?->reference.($this->order?->serviceProviderProfile ? ' · '.$this->order->serviceProviderProfile->business_name : '');
        }

        $providerName = $this->provider_type === 'service_provider_profile' ? $this->serviceProviderProfile?->business_name : null;

        return trim('Services '.($providerName ? '· '.$providerName : '').($this->eventProject ? ' · '.$this->eventProject->reference : ''));
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
}
