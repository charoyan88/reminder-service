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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pre_expiration', 'post_expiration']);
            $table->string('subject');
            $table->text('body');
            $table->string('language_code', 5)->default('en'); // Default to English
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Create index for template lookup
            $table->index(['type', 'language_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
}; 