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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('owner_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('venue_id')->constrained()->restrictOnDelete();
            $table->foreignId('venue_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venue_availability_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('status', 32)->default('draft')->index();
            $table->string('booking_mode', 32)->default('request')->index();
            $table->string('event_type', 80)->nullable()->index();
            $table->date('event_date')->index();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedInteger('guests_count')->index();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('reservation_amount')->default(0);
            $table->text('client_notes')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'event_date', 'starts_at', 'ends_at'], 'bookings_venue_slot_index');
            $table->index(['venue_id', 'status', 'event_date'], 'bookings_venue_status_date_index');
            $table->index(['client_id', 'status', 'event_date'], 'bookings_client_status_date_index');
            $table->index(['owner_profile_id', 'status', 'event_date'], 'bookings_owner_status_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
