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
        Schema::create('reminder_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('reminder_type', ['pre_expiration', 'post_expiration']);
            $table->integer('days_before_expiration')->nullable(); // For pre-expiration reminders
            $table->integer('days_after_expiration')->nullable(); // For post-expiration reminders
            $table->json('order_type_codes')->nullable(); // Array of order type codes to filter reminders
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_configurations');
    }
}; 