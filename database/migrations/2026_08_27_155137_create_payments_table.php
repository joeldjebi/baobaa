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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('payer_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 80)->unique();
            $table->string('provider', 80)->index();
            $table->string('provider_reference')->nullable()->index();
            $table->string('payment_method', 40)->index();
            $table->string('status', 32)->default('initiated')->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF')->index();
            $table->json('provider_payload')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['provider', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
