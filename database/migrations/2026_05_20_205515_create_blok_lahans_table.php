<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blok_lahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_blok', 100);
            $table->double('luas_ha');
            $table->integer('sph')->comment('Standar Pohon per Hektar');
            $table->longText('koordinat_geojson');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blok_lahans');
    }
};
