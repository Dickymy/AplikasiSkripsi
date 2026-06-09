<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blok_lahans', function (Blueprint $table) {
            $table->dropColumn(['total_tonase_panen', 'yield_per_hektar']);
        });
    }

    public function down(): void
    {
        Schema::table('blok_lahans', function (Blueprint $table) {
            $table->decimal('total_tonase_panen', 10, 2)->nullable()->after('koordinat_geojson');
            $table->decimal('yield_per_hektar', 10, 2)->nullable()->after('total_tonase_panen');
        });
    }
};
