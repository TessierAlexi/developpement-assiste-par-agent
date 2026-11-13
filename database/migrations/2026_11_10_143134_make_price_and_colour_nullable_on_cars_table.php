<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A car can be in the inventory before its price or colour is known.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('colour')->nullable()->change();
            $table->unsignedInteger('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Cars without a colour or price get placeholder values so the columns can be required again.
     */
    public function down(): void
    {
        DB::table('cars')->whereNull('colour')->update(['colour' => 'Unknown']);
        DB::table('cars')->whereNull('price')->update(['price' => 0]);

        Schema::table('cars', function (Blueprint $table) {
            $table->string('colour')->nullable(false)->change();
            $table->unsignedInteger('price')->nullable(false)->change();
        });
    }
};
