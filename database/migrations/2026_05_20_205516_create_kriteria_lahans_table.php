<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_lahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blok_lahan_id')->constrained('blok_lahans')->onDelete('cascade');
            $table->integer('tahun_tanam');
            $table->enum('jenis_tanah', ['Berpasir', 'Lempung', 'Liat']);
            $table->enum('topografi', ['Datar 0-15°', 'Bergelombang 15-30°', 'Curam >30°']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_lahans');
    }
};
