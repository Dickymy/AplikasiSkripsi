<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiPemupukan extends Model
{
    protected $fillable = [
        'rekomendasi_rbs_id',
        'admin_id',
        'tanggal_realisasi',
        'jumlah_urea_realisasi',
        'jumlah_kcl_realisasi',
        'catatan_pelaksana',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_realisasi'      => 'date',
            'jumlah_urea_realisasi'  => 'decimal:2',
            'jumlah_kcl_realisasi'   => 'decimal:2',
        ];
    }

    public function rekomendasiRbs(): BelongsTo
    {
        return $this->belongsTo(RekomendasiRbs::class, 'rekomendasi_rbs_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
