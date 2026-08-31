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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 80)->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedBigInteger('gross_amount');
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->unsignedBigInteger('net_amount');
            $table->string('currency', 3)->default('XOF')->index();
            $table->string('provider')->nullable()->index();
            $table->string('provider_reference')->nullable()->index();
            $table->date('scheduled_on')->nullable()->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            $table->index(['owner_profile_id', 'status', 'scheduled_on'], 'payout_owner_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
