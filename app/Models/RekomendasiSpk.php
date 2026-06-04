<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekomendasiSpk extends Model
{
    protected $fillable = [
        'blok_lahan_id',
        'admin_id',
        'tanggal_analisis',
        'dosis_urea',
        'dosis_kcl',
        'total_urea',
        'total_kcl',
        'status_akhir',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_analisis' => 'date',
            'dosis_urea'       => 'double',
            'dosis_kcl'        => 'double',
            'total_urea'       => 'double',
            'total_kcl'        => 'double',
        ];
    }

    public function blokLahan()
    {
        return $this->belongsTo(BlokLahan::class, 'blok_lahan_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Hitung kebutuhan karung Urea (1 karung = 50 kg)
     */
    public function getKarungUreaAttribute(): int
    {
        return (int) ceil($this->total_urea / 50);
    }

    /**
     * Hitung kebutuhan karung KCl (1 karung = 50 kg)
     */
    public function getKarungKclAttribute(): int
    {
        return (int) ceil($this->total_kcl / 50);
    }
}
