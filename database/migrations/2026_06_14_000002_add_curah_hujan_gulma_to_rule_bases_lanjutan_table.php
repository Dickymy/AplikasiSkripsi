<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rule_bases_lanjutan', function (Blueprint $table) {
            // Fitur 4: Rule untuk Curah Hujan dan Gulma
            $table->string('kondisi_curah_hujan_kategori', 50)->nullable()->after('kondisi_kelembaban');
            $table->boolean('ada_gulma_dominan')->nullable()->after('ada_serangan_hama');
        });
    }

    public function down(): void
    {
        Schema::table('rule_bases_lanjutan', function (Blueprint $table) {
            $table->dropColumn(['kondisi_curah_hujan_kategori', 'ada_gulma_dominan']);
        });
    }
};
