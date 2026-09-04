<?php

namespace Database\Factories;

use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaInvoiceItem>
 */
class ProformaInvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proforma_invoice_id' => ProformaInvoice::factory(),
            'label' => 'Location de l’espace',
            'description' => fake()->sentence(8),
            'quantity' => 1,
            'unit_price' => 500000,
            'total_price' => 500000,
            'sort_order' => 1,
        ];
    }
}
