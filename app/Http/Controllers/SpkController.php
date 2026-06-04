<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Services\SpkService;
use Illuminate\Http\Request;

class SpkController extends Controller
{
    public function __construct(private SpkService $spkService) {}

    public function index()
    {
        $blokLahans = BlokLahan::with(['kriteriaLahan', 'rekomendasiTerbaru'])->get();
        return view('spk.index', compact('blokLahans'));
    }

    public function analisis(BlokLahan $blokLahan)
    {
        try {
            $hasil = $this->spkService->analisis($blokLahan);
            return redirect()->route('spk.index')
                ->with('success', "Analisis SPK untuk blok '{$blokLahan->nama_blok}' berhasil. Status: {$hasil['rekomendasi']->status_akhir}.");
        } catch (\Exception $e) {
            return redirect()->route('spk.index')
                ->with('error', $e->getMessage());
        }
    }

    public function analisisSemua()
    {
        $hasil = $this->spkService->analisisSemua();
        $berhasil = count($hasil['results']);
        $gagal    = count($hasil['errors']);

        $message = "Analisis selesai: {$berhasil} blok berhasil dianalisis.";
        if ($gagal > 0) {
            $message .= " {$gagal} blok gagal: " . implode('; ', $hasil['errors']);
        }

        return redirect()->route('spk.index')
            ->with($gagal > 0 ? 'warning' : 'success', $message);
    }

    public function detail(BlokLahan $blokLahan)
    {
        $blokLahan->load(['kriteriaLahan', 'rekomendasiSpks' => fn($q) => $q->latest()->with('admin')]);
        return view('spk.detail', compact('blokLahan'));
    }
}
