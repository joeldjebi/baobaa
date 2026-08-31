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
        Schema::create('owner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('owner_type', 32)->index();
            $table->string('business_name')->index();
            $table->string('legal_name')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('verification_status', 32)->default('unverified')->index();
            $table->string('country_code', 2)->index();
            $table->string('city')->index();
            $table->string('whatsapp_phone', 32)->nullable();
            $table->string('payout_provider')->nullable();
            $table->string('payout_account_reference')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['country_code', 'city']);
            $table->index(['verification_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_profiles');
    }
};
