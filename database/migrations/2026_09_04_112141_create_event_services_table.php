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
        Schema::create('event_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_service_type_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->string('country_code', 2)->default('CI')->index();
            $table->string('city')->index();
            $table->string('district')->nullable()->index();
            $table->string('service_area')->nullable();
            $table->string('pricing_unit', 40)->default('event')->index();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('starting_price')->default(0);
            $table->unsignedBigInteger('deposit_amount')->nullable();
            $table->json('attributes')->nullable();
            $table->json('availability_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['service_provider_profile_id', 'status'], 'event_services_provider_status_index');
            $table->index(['event_service_type_id', 'status', 'city'], 'event_services_type_status_city_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_services');
    }
};
