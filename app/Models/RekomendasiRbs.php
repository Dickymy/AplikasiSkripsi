<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiRbs extends Model
{
    protected $table = 'rekomendasi_rbs';

    protected $fillable = [
        'blok_lahan_id',
        'kondisi_lahan_id',
        'admin_id',
        'tanggal_analisis',
        'rules_terpicu',
        'masalah_teridentifikasi',
        'rekomendasi_pupuk',
        'saran_tindakan_utama',
        'status_kebutuhan_dominan',
        'jumlah_rule_terpicu',
        'dosis_urea',
        'dosis_kcl',
        'total_urea',
        'total_kcl',
        'catatan_dosis',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_analisis'        => 'date',
            'rules_terpicu'           => 'array',
            'masalah_teridentifikasi' => 'array',
            'rekomendasi_pupuk'       => 'array',
            'dosis_urea'              => 'double',
            'dosis_kcl'               => 'double',
            'total_urea'              => 'double',
            'total_kcl'               => 'double',
        ];
    }

    public function blokLahan(): BelongsTo
    {
        return $this->belongsTo(BlokLahan::class, 'blok_lahan_id');
    }

    public function kondisiLahan(): BelongsTo
    {
        return $this->belongsTo(KondisiLahan::class, 'kondisi_lahan_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // Accessor: badge warna status
    public function getWarnaBadgeAttribute(): string
    {
        return match($this->status_kebutuhan_dominan) {
            'Darurat' => 'red',
            'Segera'  => 'orange',
            'Normal'  => 'green',
            'Tunda'   => 'gray',
            default   => 'blue',
        };
    }

    /**
     * Accessor: label status yang ditampilkan ke user.
     * Data di DB tetap Darurat/Segera/Normal/Tunda, tapi tampilan lebih mudah dipahami.
     */
    public function getLabelStatusAttribute(): string
    {
        return self::labelStatus($this->status_kebutuhan_dominan);
    }

    /**
     * Static helper: konversi status DB ke label tampilan.
     */
    public static function labelStatus(?string $status): string
    {
        return match($status) {
            'Darurat' => 'Kritis',
            'Segera'  => 'Perlu Pupuk',
            'Normal'  => 'Sehat',
            'Tunda'   => 'Tunda Pupuk',
            default   => 'Belum Dicek',
        };
    }

    /**
     * Hitung kebutuhan karung Urea (1 karung = 50 kg)
     */
    public function getKarungUreaAttribute(): int
    {
        return $this->total_urea ? (int) ceil($this->total_urea / 50) : 0;
    }

    /**
     * Hitung kebutuhan karung KCl (1 karung = 50 kg)
     */
    public function getKarungKclAttribute(): int
    {
        return $this->total_kcl ? (int) ceil($this->total_kcl / 50) : 0;
    }
}
