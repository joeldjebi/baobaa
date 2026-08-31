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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scope', 32)->default('global')->index();
            $table->foreignId('venue_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_profile_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('commission_type', 32)->default('percentage')->index();
            $table->decimal('percentage_rate', 5, 2)->nullable();
            $table->unsignedBigInteger('fixed_amount')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();

            $table->index(['scope', 'is_active', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
