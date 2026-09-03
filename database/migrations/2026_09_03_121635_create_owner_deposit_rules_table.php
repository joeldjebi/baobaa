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
        Schema::create('owner_deposit_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('deposit_type', 32)->default('percentage')->index();
            $table->decimal('percentage_rate', 5, 2)->nullable();
            $table->unsignedBigInteger('fixed_amount')->nullable();
            $table->unsignedBigInteger('minimum_amount')->default(0);
            $table->unsignedBigInteger('maximum_amount')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();

            $table->index(['owner_profile_id', 'is_active', 'starts_at'], 'owner_deposit_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_deposit_rules');
    }
};
