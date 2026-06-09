<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\RekomendasiRbs;
use App\Models\RuleBaseLanjutan;
use App\Services\RbsService;
use Illuminate\Http\Request;

class RbsController extends Controller
{
    public function __construct(private RbsService $rbsService) {}

    /**
     * Daftar blok + status analisis RBS (dengan filter anggota).
     */
    public function index(Request $request)
    {
        $query = BlokLahan::with([
            'anggota',
            'kondisiTerbaru',
            'rekomendasiRbsTerbaru',
        ]);

        // Filter by anggota
        if ($request->filled('anggota_id')) {
            $query->where('anggota_id', $request->anggota_id);
        }

        // Filter by specific blok
        if ($request->filled('blok_lahan_id')) {
            $query->where('id', $request->blok_lahan_id);
        }

        $bloks = $query->latest()->get();
        $anggotas = \App\Models\Anggota::orderBy('nama')->get();

        // Blok options for filter (scoped by anggota if selected)
        $blokFilter = $request->filled('anggota_id')
            ? BlokLahan::where('anggota_id', $request->anggota_id)->orderBy('nama_blok')->get()
            : collect();

        return view('rbs.index', compact('bloks', 'anggotas', 'blokFilter'));
    }

    /**
     * Analisis satu blok.
     */
    public function analisis(BlokLahan $blokLahan)
    {
        try {
            $hasil = $this->rbsService->analisis($blokLahan);
            return redirect()
                ->route('rbs.detail', $blokLahan)
                ->with('success', "Analisis RBS blok '{$blokLahan->nama_blok}' berhasil. Status: {$hasil['rekomendasi']->status_kebutuhan_dominan}.");
        } catch (\Exception $e) {
            return redirect()->route('rbs.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Analisis semua blok yang memiliki data kondisi.
     */
    public function analisisSemua()
    {
        $hasil    = $this->rbsService->analisisSemua();
        $berhasil = count($hasil['results']);
        $gagal    = count($hasil['errors']);

        $message = "Analisis selesai: {$berhasil} blok berhasil dianalisis.";
        if ($gagal > 0) {
            $message .= " {$gagal} blok gagal: " . implode('; ', $hasil['errors']);
        }

        return redirect()->route('rbs.index')
            ->with($gagal > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Detail hasil analisis satu blok.
     */
    public function detail(BlokLahan $blokLahan)
    {
        $blokLahan->load([
            'kondisiTerbaru',
            'kondisiLahans' => fn($q) => $q->latest('tanggal_observasi')->limit(5),
            'rekomendasiRbsTerbaru.kondisiLahan',
            'rekomendasiRbsTerbaru.admin',
        ]);

        return view('rbs.detail', compact('blokLahan'));
    }

    /**
     * API endpoint untuk popup peta WebGIS.
     */
    public function apiPopup(BlokLahan $blokLahan)
    {
        $rbs = $blokLahan->rekomendasiRbsTerbaru;
        if (!$rbs) {
            return response()->json([
                'status'  => 'Belum Dianalisis',
                'masalah' => [],
                'pupuk'   => [],
                'saran'   => '',
            ]);
        }

        return response()->json([
            'status'       => $rbs->status_kebutuhan_dominan,
            'warna_badge'  => $rbs->warna_badge,
            'tanggal'      => $rbs->tanggal_analisis->format('d/m/Y'),
            'masalah'      => $rbs->masalah_teridentifikasi,
            'pupuk'        => $rbs->rekomendasi_pupuk,
            'saran'        => $rbs->saran_tindakan_utama,
            'jumlah_rule'  => $rbs->jumlah_rule_terpicu,
        ]);
    }
}
