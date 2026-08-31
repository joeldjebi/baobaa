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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('price');
            $table->string('currency', 3)->default('XOF')->index();
            $table->string('billing_period', 32)->default('monthly')->index();
            $table->unsignedInteger('active_venues_limit')->nullable();
            $table->decimal('reduced_commission_rate', 5, 2)->nullable();
            $table->unsignedSmallInteger('visibility_boost_level')->default(0)->index();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
