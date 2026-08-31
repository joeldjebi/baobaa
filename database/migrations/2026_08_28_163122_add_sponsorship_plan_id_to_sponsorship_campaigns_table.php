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
        Schema::table('sponsorship_campaigns', function (Blueprint $table) {
            $table->foreignId('sponsorship_plan_id')->nullable()->after('venue_id')->constrained()->nullOnDelete();
            $table->index(['sponsorship_plan_id', 'status'], 'sponsorship_campaign_plan_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsorship_campaigns', function (Blueprint $table) {
            $table->dropIndex('sponsorship_campaign_plan_status_index');
            $table->dropConstrainedForeignId('sponsorship_plan_id');
        });
    }
};
