<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\RekomendasiRbs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = RekomendasiRbs::with(['blokLahan.anggota', 'admin', 'kondisiLahan'])
            ->latest('tanggal_analisis');

        // Filter by status
        if ($request->filled('status_kebutuhan_dominan')) {
            $query->where('status_kebutuhan_dominan', $request->status_kebutuhan_dominan);
        }

        // Filter by anggota/pemilik lahan
        if ($request->filled('anggota_id')) {
            $query->whereHas('blokLahan', function ($q) use ($request) {
                $q->where('anggota_id', $request->anggota_id);
            });
        }

        // Filter by specific blok lahan
        if ($request->filled('blok_lahan_id')) {
            $query->where('blok_lahan_id', $request->blok_lahan_id);
        }

        $rekap = $query->get();

        // Summary stats
        $totalUrea  = $rekap->sum('total_urea');
        $totalKcl   = $rekap->sum('total_kcl');
        $karungUrea = $totalUrea > 0 ? (int) ceil($totalUrea / 50) : 0;
        $karungKcl  = $totalKcl > 0 ? (int) ceil($totalKcl / 50) : 0;

        // Daftar anggota untuk dropdown filter
        $anggotas = Anggota::orderBy('nama')->get();

        // Blok options for filter (scoped by anggota if selected)
        $blokFilter = $request->filled('anggota_id')
            ? \App\Models\BlokLahan::where('anggota_id', $request->anggota_id)->orderBy('nama_blok')->get()
            : collect();

        return view('laporan.index', compact(
            'rekap', 'totalUrea', 'totalKcl', 'karungUrea', 'karungKcl', 'anggotas', 'blokFilter'
        ));
    }

    public function show(RekomendasiRbs $rekomendasiRbs)
    {
        $rekomendasiRbs->load(['blokLahan.anggota', 'kondisiLahan', 'admin']);
        return view('laporan.show', compact('rekomendasiRbs'));
    }

    public function exportPdf(RekomendasiRbs $rekomendasiRbs)
    {
        $rekomendasiRbs->load(['blokLahan.anggota', 'kondisiLahan', 'admin']);

        $pdf = Pdf::loadView('laporan.pdf', compact('rekomendasiRbs'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Laporan_' . str_replace(' ', '_', $rekomendasiRbs->blokLahan->nama_blok) . '_' . $rekomendasiRbs->tanggal_analisis->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
