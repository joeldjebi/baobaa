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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->string('verification_status', 32)->default('unverified')->index();
            $table->string('booking_mode', 32)->default('request')->index();
            $table->string('country_code', 2)->index();
            $table->string('city')->index();
            $table->string('district')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('min_capacity')->default(1)->index();
            $table->unsignedInteger('max_capacity')->index();
            $table->unsignedInteger('surface_area')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('starting_price')->nullable()->index();
            $table->unsignedBigInteger('reservation_amount')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'verification_status', 'published_at'], 'venues_publication_index');
            $table->index(['country_code', 'city', 'venue_category_id'], 'venues_location_category_index');
            $table->index(['city', 'status', 'max_capacity', 'starting_price'], 'venues_search_index');
            $table->index(['owner_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
