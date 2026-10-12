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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->boolean('is_rentable')->default(true)->index();
            $table->boolean('is_sellable')->default(false)->index();
            $table->unsignedBigInteger('rental_price_per_day')->nullable();
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->unsignedInteger('stock_quantity')->default(1);
            $table->string('currency', 3)->default('XOF');
            $table->string('city')->nullable()->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'product_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
