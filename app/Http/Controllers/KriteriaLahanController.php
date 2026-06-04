<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\KriteriaLahan;
use Illuminate\Http\Request;

class KriteriaLahanController extends Controller
{
    public function index()
    {
        $kriteriaLahans = KriteriaLahan::with('blokLahan')->latest()->get();
        return view('kriteria_lahan.index', compact('kriteriaLahans'));
    }

    public function create()
    {
        $blokLahans = BlokLahan::whereDoesntHave('kriteriaLahan')->get();
        return view('kriteria_lahan.create', compact('blokLahans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blok_lahan_id' => ['required', 'exists:blok_lahans,id'],
            'tahun_tanam'   => ['required', 'integer', 'min:1990', 'max:' . now()->year],
            'jenis_tanah'   => ['required', 'in:Tanah Lempung,Tanah Lempung Berpasir,Tanah Berpasir,Tanah Liat,Tanah Gambut,Tanah Aluvial,Tanah Podsolik Merah Kuning (PMK),Tanah Laterit,Tanah Berbatu,Lainnya'],
            'topografi'     => ['required', 'in:Datar 0-15°,Bergelombang 15-30°,Curam >30°'],
        ], [
            'blok_lahan_id.required' => 'Blok lahan wajib dipilih.',
            'tahun_tanam.required'   => 'Tahun tanam wajib diisi.',
            'jenis_tanah.required'   => 'Jenis tanah wajib dipilih.',
            'topografi.required'     => 'Topografi wajib dipilih.',
        ]);

        KriteriaLahan::create($validated);
        return redirect()->route('kriteria-lahan.index')->with('success', 'Kriteria lahan berhasil ditambahkan.');
    }

    public function edit(KriteriaLahan $kriteriaLahan)
    {
        $blokLahans = BlokLahan::all();
        return view('kriteria_lahan.edit', compact('kriteriaLahan', 'blokLahans'));
    }

    public function update(Request $request, KriteriaLahan $kriteriaLahan)
    {
        $validated = $request->validate([
            'blok_lahan_id' => ['required', 'exists:blok_lahans,id'],
            'tahun_tanam'   => ['required', 'integer', 'min:1990', 'max:' . now()->year],
            'jenis_tanah'   => ['required', 'in:Tanah Lempung,Tanah Lempung Berpasir,Tanah Berpasir,Tanah Liat,Tanah Gambut,Tanah Aluvial,Tanah Podsolik Merah Kuning (PMK),Tanah Laterit,Tanah Berbatu,Lainnya'],
            'topografi'     => ['required', 'in:Datar 0-15°,Bergelombang 15-30°,Curam >30°'],
        ]);

        $kriteriaLahan->update($validated);
        return redirect()->route('kriteria-lahan.index')->with('success', 'Kriteria lahan berhasil diperbarui.');
    }

    public function destroy(KriteriaLahan $kriteriaLahan)
    {
        $kriteriaLahan->delete();
        return redirect()->route('kriteria-lahan.index')->with('success', 'Kriteria lahan berhasil dihapus.');
    }
}
