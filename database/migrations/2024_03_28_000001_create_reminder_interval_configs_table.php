<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reminder_interval_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('reminder_type', ['pre_expiration', 'post_expiration']);
            $table->integer('days');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Ensure unique combinations of type and days
            $table->unique(['reminder_type', 'days']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminder_interval_configs');
    }
}; 