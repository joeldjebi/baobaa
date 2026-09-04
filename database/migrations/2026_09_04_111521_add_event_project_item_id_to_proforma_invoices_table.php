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
        Schema::table('proforma_invoices', function (Blueprint $table) {
            $table->foreignId('event_project_item_id')
                ->nullable()
                ->after('booking_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['event_project_item_id', 'status'], 'proforma_invoices_project_item_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            $table->dropIndex('proforma_invoices_project_item_status_index');
            $table->dropConstrainedForeignId('event_project_item_id');
        });
    }
};
