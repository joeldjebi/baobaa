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
        Schema::create('venue_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->date('available_date')->index();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('status', 32)->default('available')->index();
            $table->string('block_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['venue_id', 'available_date', 'starts_at', 'ends_at'], 'venue_availability_unique_slot');
            $table->index(['venue_id', 'available_date', 'status'], 'venue_availability_lookup_index');
            $table->index(['available_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_availabilities');
    }
};
