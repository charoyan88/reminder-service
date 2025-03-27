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
        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('expiration_type', ['fixed_period', 'year_end'])->default('fixed_period');//maybe change enum to sting
            $table->integer('expiration_period_months')->nullable(); // For fixed period types
            $table->boolean('requires_renewal')->default(true);
            $table->boolean('allows_early_renewal')->default(true);
            $table->integer('early_renewal_days')->nullable(); // Days before expiration when early renewal is allowed
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
        Schema::dropIfExists('order_types');
    }
}; 