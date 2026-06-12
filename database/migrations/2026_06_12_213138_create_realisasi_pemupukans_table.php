<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_pemupukans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_rbs_id')->constrained('rekomendasi_rbs')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->date('tanggal_realisasi');
            $table->decimal('jumlah_urea_realisasi', 8, 2)->default(0);
            $table->decimal('jumlah_kcl_realisasi', 8, 2)->default(0);
            $table->text('catatan_pelaksana')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_pemupukans');
    }
};
