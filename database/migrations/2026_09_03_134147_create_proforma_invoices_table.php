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
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('status', 32)->default('sent')->index();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->unsignedBigInteger('service_fee_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamp('client_confirmed_at')->nullable()->index();
            $table->timestamp('owner_confirmed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
