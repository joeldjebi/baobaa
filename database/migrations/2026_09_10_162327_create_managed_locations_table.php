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
        Schema::create('managed_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('managed_locations')->nullOnDelete();
            $table->string('type', 24)->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('code', 12)->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order'], 'managed_locations_public_index');
            $table->index(['parent_id', 'type', 'is_active'], 'managed_locations_parent_lookup_index');
            $table->unique(['type', 'parent_id', 'slug'], 'managed_locations_type_parent_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managed_locations');
    }
};
