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
        Schema::create('event_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('name');
            $table->string('status', 40)->default('active')->index();
            $table->string('event_type', 80)->nullable()->index();
            $table->date('event_date')->nullable()->index();
            $table->string('country_code', 2)->default('CI')->index();
            $table->string('city')->nullable()->index();
            $table->string('district')->nullable()->index();
            $table->string('currency', 3)->default('XOF')->index();
            $table->unsignedBigInteger('estimated_total_amount')->default(0);
            $table->unsignedBigInteger('confirmed_total_amount')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status', 'event_date'], 'event_projects_client_status_date_index');
            $table->index(['status', 'event_date'], 'event_projects_status_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_projects');
    }
};
