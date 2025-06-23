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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();

            $table->string('model');
            $table->string('type');
            $table->integer('seats');
            $table->string('fuel_type');
            $table->integer('engine_capacity');
            $table->year('year');
            $table->string('transmission');
            $table->string('image')->nullable();
            $table->json('extra_images')->nullable();
            $table->json('rental_prices');
            $table->text('description');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
