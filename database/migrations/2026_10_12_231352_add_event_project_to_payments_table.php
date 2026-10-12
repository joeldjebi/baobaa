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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->foreignId('event_project_id')->nullable()->after('booking_id')->constrained()->restrictOnDelete();
            $table->string('provider_type', 40)->nullable()->after('event_project_id');
            $table->unsignedBigInteger('provider_id')->nullable()->after('provider_type');
            $table->index(['event_project_id', 'provider_type', 'provider_id'], 'payments_event_project_provider_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_event_project_provider_index');
            $table->dropConstrainedForeignId('event_project_id');
            $table->dropColumn(['provider_type', 'provider_id']);
        });
    }
};
