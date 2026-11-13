<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The equipment is the list of option keys checked for the car; cars already in the inventory have none yet.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->json('equipment')->nullable()->after('internal_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * The equipment checked since is lost.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });
    }
};
