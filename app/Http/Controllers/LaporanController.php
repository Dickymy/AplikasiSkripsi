<?php

namespace App\Http\Controllers;

use App\Models\RekomendasiSpk;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = RekomendasiSpk::with(['blokLahan', 'admin'])
            ->latest('tanggal_analisis');

        // Filter by status
        if ($request->filled('status_akhir')) {
            $query->where('status_akhir', $request->status_akhir);
        }

        // Filter by pemilik lahan
        if ($request->filled('nama_pemilik')) {
            $query->whereHas('blokLahan', function ($q) use ($request) {
                $q->where('nama_pemilik', $request->nama_pemilik);
            });
        }

        $rekap = $query->get();

        // Summary stats (only for filtered data)
        $totalUrea   = $rekap->sum('total_urea');
        $totalKcl    = $rekap->sum('total_kcl');
        $karungUrea  = (int) ceil($totalUrea / 50);
        $karungKcl   = (int) ceil($totalKcl / 50);

        // Daftar unik nama pemilik lahan untuk dropdown filter
        $daftarPemilik = \App\Models\BlokLahan::select('nama_pemilik')
            ->distinct()
            ->orderBy('nama_pemilik')
            ->pluck('nama_pemilik');

        return view('laporan.index', compact(
            'rekap', 'totalUrea', 'totalKcl', 'karungUrea', 'karungKcl', 'daftarPemilik'
        ));
    }

    public function show(RekomendasiSpk $rekomendasiSpk)
    {
        $rekomendasiSpk->load(['blokLahan.kriteriaLahan', 'admin']);
        return view('laporan.show', compact('rekomendasiSpk'));
    }
}
