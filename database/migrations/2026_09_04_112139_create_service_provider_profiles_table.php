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
        Schema::create('service_provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->uuid('public_uuid')->unique();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('logo_disk')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_alt_text')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('tax_identifier', 120)->nullable()->index();
            $table->string('verification_status', 32)->default('pending')->index();
            $table->string('country_code', 2)->default('CI')->index();
            $table->string('city')->index();
            $table->string('district')->nullable()->index();
            $table->string('whatsapp_phone', 32)->nullable();
            $table->string('service_area')->nullable();
            $table->text('description')->nullable();
            $table->string('billing_preference', 32)->default('commission')->index();
            $table->timestamp('verified_at')->nullable()->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['verification_status', 'city'], 'service_provider_profiles_status_city_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_profiles');
    }
};
