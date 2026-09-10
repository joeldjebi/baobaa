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
        Schema::create('event_project_item_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_project_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->unsignedBigInteger('proposed_amount')->nullable();
            $table->string('currency', 3)->default('XOF')->index();
            $table->timestamps();

            $table->index(['event_project_item_id', 'created_at'], 'event_project_item_messages_thread_index');
            $table->index(['recipient_id', 'created_at'], 'event_project_item_messages_recipient_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_project_item_messages');
    }
};
