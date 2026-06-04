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
        Schema::table('blok_lahans', function (Blueprint $table) {
            $table->string('nama_pemilik', 100)->after('nama_blok')->default('-');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blok_lahans', function (Blueprint $table) {
            $table->dropColumn('nama_pemilik');
        });
    }
};
