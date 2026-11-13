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
     * Existing cars get a stock number derived from their id and their
     * listing date as the date they entered the inventory.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('stock_number', 20)->nullable()->after('id');
            $table->string('status', 20)->default('available')->after('stock_number');
            $table->date('received_on')->nullable()->after('status');
            $table->date('sold_on')->nullable()->after('received_on');
        });

        DB::table('cars')->lazyById()->each(fn (object $car) => DB::table('cars')
            ->where('id', $car->id)
            ->update([
                'stock_number' => sprintf('STK-%05d', $car->id),
                'received_on' => substr((string) $car->created_at, 0, 10) ?: now()->toDateString(),
            ]));

        Schema::table('cars', function (Blueprint $table) {
            $table->string('stock_number', 20)->nullable(false)->unique()->change();
            $table->date('received_on')->nullable(false)->change();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique(['stock_number']);
            $table->dropIndex(['status']);
            $table->dropColumn(['stock_number', 'status', 'received_on', 'sold_on']);
        });
    }
};
