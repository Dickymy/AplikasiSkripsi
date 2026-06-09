<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom dosis numerik ke rekomendasi_rbs agar RBS juga bisa
     * menghitung kebutuhan pupuk presisi (seperti SPK lama).
     */
    public function up(): void
    {
        Schema::table('rekomendasi_rbs', function (Blueprint $table) {
            $table->double('dosis_urea')->nullable()->after('jumlah_rule_terpicu')->comment('kg per pokok — dihitung dari kriteria lahan');
            $table->double('dosis_kcl')->nullable()->after('dosis_urea')->comment('kg per pokok');
            $table->double('total_urea')->nullable()->after('dosis_kcl')->comment('kg total untuk seluruh blok');
            $table->double('total_kcl')->nullable()->after('total_urea')->comment('kg total untuk seluruh blok');
        });
    }

    public function down(): void
    {
        Schema::table('rekomendasi_rbs', function (Blueprint $table) {
            $table->dropColumn(['dosis_urea', 'dosis_kcl', 'total_urea', 'total_kcl']);
        });
    }
};
