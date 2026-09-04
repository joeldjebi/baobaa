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
        Schema::create('event_project_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_project_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 40)->index();
            $table->string('provider_type', 40)->nullable()->index();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->nullableMorphs('source');
            $table->string('status', 40)->default('negotiating')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('quoted_amount')->default(0);
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->timestamp('client_confirmed_at')->nullable()->index();
            $table->timestamp('provider_confirmed_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['event_project_id', 'item_type', 'status'], 'event_project_items_project_type_status_index');
            $table->index(['provider_type', 'provider_id', 'status'], 'event_project_items_provider_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_project_items');
    }
};
