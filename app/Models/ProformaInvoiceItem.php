<?php

namespace App\Models;

use Database\Factories\ProformaInvoiceItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProformaInvoiceItem extends Model
{
    /** @use HasFactory<ProformaInvoiceItemFactory> */
    use HasFactory;

    protected $fillable = [
        'proforma_invoice_id',
        'label',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'sort_order',
    ];

    public function proformaInvoice(): BelongsTo
    {
        return $this->belongsTo(ProformaInvoice::class);
    }
}
