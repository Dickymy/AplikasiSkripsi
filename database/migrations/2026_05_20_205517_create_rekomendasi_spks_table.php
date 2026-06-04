<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_spks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blok_lahan_id')->constrained('blok_lahans')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->date('tanggal_analisis');
            $table->double('dosis_urea')->comment('kg per pokok');
            $table->double('dosis_kcl')->comment('kg per pokok');
            $table->double('total_urea')->comment('kg total untuk seluruh blok');
            $table->double('total_kcl')->comment('kg total untuk seluruh blok');
            $table->string('status_akhir', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_spks');
    }
};
