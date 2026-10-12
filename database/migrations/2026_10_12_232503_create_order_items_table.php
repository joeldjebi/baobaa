<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('mode', 16);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedSmallInteger('rental_days')->nullable();
            $table->date('rental_starts_on')->nullable();
            $table->date('rental_ends_on')->nullable();
            $table->unsignedBigInteger('line_total');
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'mode', 'rental_starts_on', 'rental_ends_on'], 'order_items_rental_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
