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
            $table->string('price_unit', 24)->default('event')->after('starting_price');
            $table->unsignedSmallInteger('minimum_duration_hours')->nullable()->after('price_unit');
            $table->time('opens_at')->nullable()->after('availability_notes');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->json('video_urls')->nullable()->after('closes_at');
            $table->string('virtual_tour_url', 500)->nullable()->after('video_urls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['price_unit', 'minimum_duration_hours', 'opens_at', 'closes_at', 'video_urls', 'virtual_tour_url']);
        });
    }
};
