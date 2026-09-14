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
        Schema::create('availability_holds', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('holdable');
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->string('status', 32)->default('active')->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->dateTime('expires_at')->index();
            $table->timestamps();

            $table->index(['holdable_type', 'holdable_id', 'status', 'expires_at'], 'availability_holds_resource_status_index');
            $table->index(['holdable_type', 'holdable_id', 'starts_at', 'ends_at'], 'availability_holds_resource_slot_index');
            $table->index(['client_id', 'status', 'expires_at'], 'availability_holds_client_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_holds');
    }
};
