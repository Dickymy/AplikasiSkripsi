<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $blokLahans = BlokLahan::with([
            'rekomendasiTerbaru',
            'kriteriaLahan',
            'rekomendasiRbsTerbaru',
            'kondisiTerbaru',
        ])->get();

        $mapData = $blokLahans->map(function ($blok) {
            $rekomendasi = $blok->rekomendasiTerbaru;
            $rbs         = $blok->rekomendasiRbsTerbaru;
            $kriteria    = $blok->kriteriaLahan;
            $umur        = $kriteria ? (now()->year - $kriteria->tahun_tanam) : null;

            return [
                'id'                 => $blok->id,
                'nama_blok'          => $blok->nama_blok,
                'nama_pemilik'       => $blok->nama_pemilik,
                'luas_ha'            => $blok->luas_ha,
                'sph'                => $blok->sph,
                'umur_tanaman'       => $umur,
                'geojson'            => json_decode($blok->koordinat_geojson, true),
                'status_akhir'       => $rekomendasi ? $rekomendasi->status_akhir : 'Belum Dianalisis',
                'dosis_urea'         => $rekomendasi ? $rekomendasi->dosis_urea : null,
                'dosis_kcl'          => $rekomendasi ? $rekomendasi->dosis_kcl : null,
                'total_urea'         => $rekomendasi ? $rekomendasi->total_urea : null,
                'total_kcl'          => $rekomendasi ? $rekomendasi->total_kcl : null,
                'total_tonase_panen' => $blok->total_tonase_panen,
                'yield_per_hektar'   => $blok->yield_per_hektar,
                // Data RBS untuk popup
                'status_rbs'         => $rbs?->status_kebutuhan_dominan ?? 'Belum Dianalisis',
                'masalah_rbs'        => $rbs?->masalah_teridentifikasi ?? [],
                'pupuk_rbs'          => $rbs?->rekomendasi_pupuk ?? [],
                'saran_rbs'          => $rbs?->saran_tindakan_utama ?? '',
                'tgl_analisis_rbs'   => $rbs?->tanggal_analisis?->format('d/m/Y') ?? '-',
                'jumlah_rule'        => $rbs?->jumlah_rule_terpicu ?? 0,
            ];
        });

        $stats = [
            'total_blok'     => $blokLahans->count(),
            'total_luas'     => $blokLahans->sum('luas_ha'),
            'sudah_analisis' => $blokLahans->filter(fn($b) => $b->rekomendasiTerbaru)->count(),
            'segera_pupuk'   => $blokLahans->filter(fn($b) => $b->rekomendasiTerbaru?->status_akhir === 'Segera Pupuk')->count(),
            'rbs_darurat'    => $blokLahans->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Darurat')->count(),
            'rbs_segera'     => $blokLahans->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Segera')->count(),
        ];

        return view('dashboard.index', compact('mapData', 'stats'));
    }
}
