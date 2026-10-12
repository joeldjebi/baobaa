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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_profile_id')->constrained()->cascadeOnDelete();
            $table->string('city');
            $table->unsignedBigInteger('fee')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_provider_profile_id', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
