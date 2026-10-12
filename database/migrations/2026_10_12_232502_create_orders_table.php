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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('service_provider_profile_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('paid')->index();
            $table->string('fulfillment_method', 16);
            $table->string('delivery_city')->nullable();
            $table->string('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->unsignedBigInteger('items_total');
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('deposit_total')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->string('payment_method', 40);
            $table->string('deposit_status', 24)->default('none')->index();
            $table->unsignedBigInteger('deposit_retained_amount')->default(0);
            $table->string('deposit_retention_reason', 500)->nullable();
            $table->text('client_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['service_provider_profile_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
