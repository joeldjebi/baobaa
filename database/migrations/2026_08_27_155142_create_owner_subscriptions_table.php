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
        Schema::create('owner_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('pending_payment')->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF')->index();
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->boolean('auto_renews')->default(false)->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['owner_profile_id', 'status', 'ends_on'], 'owner_subscription_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_subscriptions');
    }
};
