<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlokLahan extends Model
{
    protected $fillable = [
        'nama_blok',
        'nama_pemilik',
        'luas_ha',
        'sph',
        'koordinat_geojson',
        'total_tonase_panen',
        'yield_per_hektar',
    ];

    protected function casts(): array
    {
        return [
            'luas_ha'            => 'double',
            'sph'                => 'integer',
            'total_tonase_panen' => 'double',
            'yield_per_hektar'   => 'double',
        ];
    }

    public function kriteriaLahan()
    {
        return $this->hasOne(KriteriaLahan::class, 'blok_lahan_id');
    }

    public function rekomendasiSpks()
    {
        return $this->hasMany(RekomendasiSpk::class, 'blok_lahan_id');
    }

    public function rekomendasiTerbaru()
    {
        return $this->hasOne(RekomendasiSpk::class, 'blok_lahan_id')->latestOfMany();
    }

    public function kondisiLahans()
    {
        return $this->hasMany(KondisiLahan::class, 'blok_lahan_id');
    }

    public function kondisiTerbaru()
    {
        return $this->hasOne(KondisiLahan::class, 'blok_lahan_id')->latestOfMany('tanggal_observasi');
    }

    public function rekomendasiRbs()
    {
        return $this->hasMany(RekomendasiRbs::class, 'blok_lahan_id');
    }

    public function rekomendasiRbsTerbaru()
    {
        return $this->hasOne(RekomendasiRbs::class, 'blok_lahan_id')->latestOfMany('tanggal_analisis');
    }
}
