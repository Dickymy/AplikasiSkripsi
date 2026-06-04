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
    ];

    protected function casts(): array
    {
        return [
            'tanggal_analisis'        => 'date',
            'rules_terpicu'           => 'array',
            'masalah_teridentifikasi' => 'array',
            'rekomendasi_pupuk'       => 'array',
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
}
