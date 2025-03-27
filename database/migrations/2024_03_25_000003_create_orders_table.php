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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_order_id')->nullable();
            $table->date('application_date');
            $table->date('expiration_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('replaced_by_order_id')->nullable();
            $table->timestamps();
            
            // Create index on expiration_date for quick lookups
            $table->index('expiration_date');
        });
        
        // Add the self-referencing foreign key after the table is created
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('replaced_by_order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}; 