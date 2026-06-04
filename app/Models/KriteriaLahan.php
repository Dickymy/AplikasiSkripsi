<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaLahan extends Model
{
    protected $fillable = [
        'blok_lahan_id',
        'tahun_tanam',
        'jenis_tanah',
        'topografi',
    ];

    protected function casts(): array
    {
        return [
            'tahun_tanam' => 'integer',
        ];
    }

    public function blokLahan()
    {
        return $this->belongsTo(BlokLahan::class, 'blok_lahan_id');
    }

    /**
     * Hitung umur tanaman secara dinamis
     */
    public function getUmurTanamanAttribute(): int
    {
        return now()->year - $this->tahun_tanam;
    }

    /**
     * Kategorisasi umur tanaman untuk Forward Chaining
     */
    public function getKategoriUmurAttribute(): string
    {
        $umur = $this->umur_tanaman;
        if ($umur >= 3 && $umur <= 8) {
            return 'Remaja';
        } elseif ($umur >= 9 && $umur <= 14) {
            return 'Menghasilkan Muda';
        } elseif ($umur >= 15 && $umur <= 25) {
            return 'Menghasilkan Tua';
        } elseif ($umur < 3) {
            return 'Belum Menghasilkan';
        } else {
            return 'Tua Renta';
        }
    }
}
