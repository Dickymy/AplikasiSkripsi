<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use Illuminate\Http\Request;

class BlokLahanController extends Controller
{
    public function index()
    {
        $blokLahans = BlokLahan::with(['kriteriaLahan', 'rekomendasiTerbaru'])->latest()->get();
        return view('blok_lahan.index', compact('blokLahans'));
    }

    public function create()
    {
        return view('blok_lahan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_blok'          => ['required', 'string', 'max:100'],
            'nama_pemilik'       => ['required', 'string', 'max:100'],
            'luas_ha'            => ['required', 'numeric', 'min:0.01'],
            'sph'                => ['required', 'integer', 'min:1'],
            'koordinat_geojson'  => ['required', 'string'],
            'total_tonase_panen' => ['nullable', 'numeric', 'min:0'],
        ], [
            'nama_blok.required'         => 'Nama blok wajib diisi.',
            'nama_pemilik.required'      => 'Nama pemilik lahan wajib diisi.',
            'luas_ha.required'           => 'Luas lahan wajib diisi.',
            'sph.required'               => 'SPH wajib diisi.',
            'koordinat_geojson.required' => 'Koordinat GeoJSON wajib diisi.',
            'total_tonase_panen.numeric' => 'Total tonase panen harus berupa angka.',
            'total_tonase_panen.min'     => 'Total tonase panen tidak boleh negatif.',
        ]);

        // Validasi format JSON
        json_decode($validated['koordinat_geojson']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['koordinat_geojson' => 'Format GeoJSON tidak valid.'])->withInput();
        }

        // Hitung yield per hektar secara otomatis
        $tonase = $validated['total_tonase_panen'] ?? null;
        $luas   = (float) $validated['luas_ha'];
        $validated['yield_per_hektar'] = ($tonase !== null && $luas > 0)
            ? round($tonase / $luas, 2)
            : null;

        BlokLahan::create($validated);
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil ditambahkan.');
    }

    public function show(BlokLahan $blokLahan)
    {
        $blokLahan->load(['kriteriaLahan', 'rekomendasiSpks.admin', 'kondisiTerbaru', 'rekomendasiRbsTerbaru']);
        return view('blok_lahan.show', compact('blokLahan'));
    }

    public function edit(BlokLahan $blokLahan)
    {
        return view('blok_lahan.edit', compact('blokLahan'));
    }

    public function update(Request $request, BlokLahan $blokLahan)
    {
        $validated = $request->validate([
            'nama_blok'          => ['required', 'string', 'max:100'],
            'nama_pemilik'       => ['required', 'string', 'max:100'],
            'luas_ha'            => ['required', 'numeric', 'min:0.01'],
            'sph'                => ['required', 'integer', 'min:1'],
            'koordinat_geojson'  => ['required', 'string'],
            'total_tonase_panen' => ['nullable', 'numeric', 'min:0'],
        ], [
            'total_tonase_panen.numeric' => 'Total tonase panen harus berupa angka.',
            'total_tonase_panen.min'     => 'Total tonase panen tidak boleh negatif.',
        ]);

        json_decode($validated['koordinat_geojson']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['koordinat_geojson' => 'Format GeoJSON tidak valid.'])->withInput();
        }

        // Hitung yield per hektar secara otomatis
        $tonase = $validated['total_tonase_panen'] ?? null;
        $luas   = (float) $validated['luas_ha'];
        $validated['yield_per_hektar'] = ($tonase !== null && $luas > 0)
            ? round($tonase / $luas, 2)
            : null;

        $blokLahan->update($validated);
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil diperbarui.');
    }

    public function destroy(BlokLahan $blokLahan)
    {
        $blokLahan->delete();
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil dihapus.');
    }
}
