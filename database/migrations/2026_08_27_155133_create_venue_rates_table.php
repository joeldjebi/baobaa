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
        Schema::create('venue_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('rate_type', 32)->index();
            $table->unsignedBigInteger('price');
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedSmallInteger('min_hours')->nullable();
            $table->unsignedSmallInteger('max_hours')->nullable();
            $table->unsignedInteger('min_guests')->nullable()->index();
            $table->unsignedInteger('max_guests')->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('conditions')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'is_active', 'rate_type']);
            $table->index(['currency', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_rates');
    }
};
