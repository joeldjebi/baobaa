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
        Schema::table('venues', function (Blueprint $table) {
            $table->json('highlights')->nullable()->after('description');
            $table->json('included_items')->nullable()->after('highlights');
            $table->json('space_details')->nullable()->after('included_items');
            $table->json('house_rules')->nullable()->after('space_details');
            $table->json('location_details')->nullable()->after('longitude');
            $table->json('availability_notes')->nullable()->after('reservation_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn([
                'highlights',
                'included_items',
                'space_details',
                'house_rules',
                'location_details',
                'availability_notes',
            ]);
        });
    }
};
