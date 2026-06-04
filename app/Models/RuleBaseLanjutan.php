<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleBaseLanjutan extends Model
{
    protected $table = 'rule_bases_lanjutan';

    protected $fillable = [
        'kondisi_warna_daun',
        'kondisi_ph_min',
        'kondisi_ph_max',
        'kondisi_kelembaban',
        'kondisi_musim',
        'kondisi_drainase',
        'kondisi_defisiensi',
        'kondisi_kategori_umur',
        'kondisi_pelepah',
        'kondisi_tandan',
        'ada_serangan_hama',
        'indikasi_masalah',
        'jenis_pupuk_utama',
        'jenis_pupuk_pendukung',
        'dosis_anjuran',
        'metode_aplikasi',
        'waktu_aplikasi',
        'saran_tindakan',
        'status_kebutuhan',
        'prioritas',
        'aktif',
        'keterangan_rule',
    ];

    protected function casts(): array
    {
        return [
            'aktif'              => 'boolean',
            'ada_serangan_hama'  => 'boolean',
            'kondisi_ph_min'     => 'decimal:2',
            'kondisi_ph_max'     => 'decimal:2',
            'prioritas'          => 'integer',
        ];
    }

    // Scope: hanya rule aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
