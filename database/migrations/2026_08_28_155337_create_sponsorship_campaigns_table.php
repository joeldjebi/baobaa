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
        Schema::create('sponsorship_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('goal', 64)->default('visibility')->index();
            $table->string('placement', 64)->default('catalog_top')->index();
            $table->string('status', 32)->default('pending')->index();
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->unsignedBigInteger('budget_amount');
            $table->unsignedBigInteger('daily_budget')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->json('target_cities')->nullable();
            $table->unsignedInteger('impressions_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['owner_profile_id', 'status', 'starts_on'], 'sponsorship_owner_status_index');
            $table->index(['venue_id', 'status', 'starts_on', 'ends_on'], 'sponsorship_venue_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorship_campaigns');
    }
};
