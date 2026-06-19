<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekomendasi_rbs', function (Blueprint $table) {
            // Fitur 1: Histori Rekomendasi
            $table->boolean('is_latest')->default(true)->after('tanggal_analisis');
            $table->integer('nomor_analisis')->nullable()->after('is_latest');

            // Fitur 2: Jadwal Pemupukan Per Tahap
            $table->json('jadwal_pemupukan')->nullable()->after('catatan_dosis');

            // Fitur 3: Status Validitas
            $table->string('validitas_rekomendasi', 50)->default('Estimasi Visual')->after('jadwal_pemupukan');
            $table->text('catatan_validitas')->nullable()->after('validitas_rekomendasi');

            // Fitur 6: Confidence Score
            $table->integer('confidence_score')->default(0)->after('catatan_validitas');
            $table->string('confidence_label', 20)->default('Rendah')->after('confidence_score');
            $table->text('catatan_confidence')->nullable()->after('confidence_label');

            // Fitur 7: Notifikasi Data Belum Cukup
            $table->boolean('data_cukup')->default(false)->after('catatan_confidence');
            $table->json('data_kurang')->nullable()->after('data_cukup');
            $table->text('notifikasi_data')->nullable()->after('data_kurang');
        });

        // Drop unique constraint if exists (blok_lahan_id was used for updateOrCreate)
        // Now we allow multiple records per blok_lahan_id for history
    }

    public function down(): void
    {
        Schema::table('rekomendasi_rbs', function (Blueprint $table) {
            $table->dropColumn([
                'is_latest',
                'nomor_analisis',
                'jadwal_pemupukan',
                'validitas_rekomendasi',
                'catatan_validitas',
                'confidence_score',
                'confidence_label',
                'catatan_confidence',
                'data_cukup',
                'data_kurang',
                'notifikasi_data',
            ]);
        });
    }
};
