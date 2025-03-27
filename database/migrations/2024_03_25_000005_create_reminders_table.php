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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reminder_configuration_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_date');
            $table->dateTime('sent_date')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->string('email_to');
            $table->string('email_subject');
            $table->text('email_content');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Create indices for various lookups
            $table->index(['status', 'scheduled_date']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
}; 