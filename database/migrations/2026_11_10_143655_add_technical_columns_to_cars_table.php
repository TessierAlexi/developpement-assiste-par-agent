<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Every technical detail is optional: cars already in the inventory do not have them yet.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('vin', 17)->nullable()->unique()->after('stock_number');
            $table->string('interior_colour')->nullable()->after('colour');
            $table->string('engine')->nullable()->after('interior_colour');
            $table->decimal('engine_displacement_l', 3, 1)->nullable()->after('engine');
            $table->unsignedTinyInteger('cylinders')->nullable()->after('engine_displacement_l');
            $table->string('drivetrain', 3)->nullable()->after('cylinders');
            $table->unsignedTinyInteger('doors')->nullable()->after('drivetrain');
            $table->unsignedTinyInteger('seats')->nullable()->after('doors');
            $table->decimal('city_l_per_100km', 3, 1)->nullable()->after('seats');
            $table->decimal('highway_l_per_100km', 3, 1)->nullable()->after('city_l_per_100km');
            $table->unsignedInteger('cost_price')->nullable()->after('price');
            $table->text('internal_notes')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * The technical details entered since are lost.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique(['vin']);
            $table->dropColumn([
                'vin', 'interior_colour', 'engine', 'engine_displacement_l', 'cylinders', 'drivetrain',
                'doors', 'seats', 'city_l_per_100km', 'highway_l_per_100km', 'cost_price', 'internal_notes',
            ]);
        });
    }
};
