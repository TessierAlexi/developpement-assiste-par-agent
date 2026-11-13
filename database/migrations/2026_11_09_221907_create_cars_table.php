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
            $table->unsignedSmallInteger('year');
            $table->string('make');
            $table->string('model');
            $table->string('trim');
            $table->string('body_type');
            $table->string('fuel_type');
            $table->string('transmission');
            $table->string('colour');
            $table->unsignedInteger('mileage_km');
            $table->unsignedInteger('price');
            $table->text('description')->nullable();
            $table->string('photo_url', 2048)->nullable();
            $table->string('photo_source_url', 2048)->nullable();
            $table->string('photo_author')->nullable();
            $table->string('photo_licence')->nullable();
            $table->string('photo_licence_url', 2048)->nullable();
            $table->boolean('photo_attribution_required')->default(false);
            $table->timestamps();

            $table->index('created_at');
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
