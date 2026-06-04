<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_bases', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_kondisi', 255)->comment('Format: KATEGORI_UMUR|JENIS_TANAH|TOPOGRAFI');
            $table->double('takaran_urea')->comment('kg per pokok');
            $table->double('takaran_kcl')->comment('kg per pokok');
            $table->string('status_pemupukan', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_bases');
    }
};
