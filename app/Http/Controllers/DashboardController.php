<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $blokLahans = BlokLahan::with([
            'anggota',
            'rekomendasiRbsTerbaru',
            'kondisiTerbaru',
        ])->get();

        $mapData = $blokLahans->map(function ($blok) {
            $rbs = $blok->rekomendasiRbsTerbaru;

            return [
                'id'               => $blok->id,
                'nama_blok'        => $blok->nama_blok,
                'nama_pemilik'     => $blok->nama_pemilik,
                'luas_ha'          => $blok->luas_ha,
                'sph'              => $blok->sph,
                'umur_tanaman'     => $blok->umur_tanaman,
                'geojson'          => json_decode($blok->koordinat_geojson, true),
                // Status & data RBS
                'status_rbs'       => $rbs?->status_kebutuhan_dominan ?? 'Belum Dianalisis',
                'masalah_rbs'      => $rbs?->masalah_teridentifikasi ?? [],
                'pupuk_rbs'        => $rbs?->rekomendasi_pupuk ?? [],
                'saran_rbs'        => $rbs?->saran_tindakan_utama ?? '',
                'tgl_analisis_rbs' => $rbs?->tanggal_analisis?->format('d/m/Y') ?? '-',
                'jumlah_rule'      => $rbs?->jumlah_rule_terpicu ?? 0,
                // Dosis numerik
                'dosis_urea'       => $rbs?->dosis_urea,
                'dosis_kcl'        => $rbs?->dosis_kcl,
                'total_urea'       => $rbs?->total_urea,
                'total_kcl'        => $rbs?->total_kcl,
            ];
        });

        $stats = [
            'total_blok'     => $blokLahans->count(),
            'total_luas'     => $blokLahans->sum('luas_ha'),
            'sudah_analisis' => $blokLahans->filter(fn($b) => $b->rekomendasiRbsTerbaru)->count(),
            'darurat'        => $blokLahans->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Darurat')->count(),
            'segera'         => $blokLahans->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Segera')->count(),
            'belum_kondisi'  => $blokLahans->filter(fn($b) => !$b->kondisiTerbaru)->count(),
        ];

        return view('dashboard.index', compact('mapData', 'stats'));
    }
}
