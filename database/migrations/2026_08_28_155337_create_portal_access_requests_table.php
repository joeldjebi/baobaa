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
        Schema::create('portal_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('requested_role', 32)->index();
            $table->string('status', 32)->default('pending')->index();
            $table->string('applicant_type', 32)->nullable()->index();
            $table->string('business_name')->nullable()->index();
            $table->string('legal_name')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('whatsapp_phone', 32)->nullable();
            $table->text('motivation')->nullable();
            $table->text('decision_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'requested_role', 'status'], 'portal_access_user_role_status_index');
            $table->index(['requested_role', 'status', 'created_at'], 'portal_access_review_queue_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_access_requests');
    }
};
