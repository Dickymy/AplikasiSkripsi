<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\BlokLahan;
use Illuminate\Http\Request;

class BlokLahanController extends Controller
{
    public function index()
    {
        $blokLahans = BlokLahan::with(['anggota', 'rekomendasiRbsTerbaru', 'kondisiTerbaru'])->latest()->get();
        return view('blok_lahan.index', compact('blokLahans'));
    }

    public function create()
    {
        $anggotas = Anggota::orderBy('nama')->get();
        $existingBloks = BlokLahan::select('id', 'nama_blok', 'koordinat_geojson')->get()
            ->map(fn($b) => ['nama' => $b->nama_blok, 'geojson' => json_decode($b->koordinat_geojson, true)])
            ->filter(fn($b) => $b['geojson'] !== null)->values();

        return view('blok_lahan.create', compact('anggotas', 'existingBloks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id'        => ['required', 'exists:anggotas,id'],
            'nama_blok'         => ['required', 'string', 'max:100'],
            'luas_ha'           => ['required', 'numeric', 'min:0.01'],
            'sph'               => ['required', 'integer', 'min:1'],
            'koordinat_geojson' => ['required', 'string'],
            'tahun_tanam'       => ['required', 'integer', 'min:1990', 'max:' . now()->year],
            'jenis_tanah'       => ['required', 'in:Tanah Lempung,Tanah Lempung Berpasir,Tanah Berpasir,Tanah Liat,Tanah Gambut,Tanah Aluvial,Tanah Podsolik Merah Kuning (PMK),Tanah Laterit,Tanah Berbatu,Lainnya'],
            'topografi'         => ['required', 'in:Datar 0-15°,Bergelombang 15-30°,Curam >30°'],
        ], [
            'anggota_id.required'        => 'Pemilik lahan wajib dipilih.',
            'nama_blok.required'         => 'Nama blok wajib diisi.',
            'luas_ha.required'           => 'Luas lahan wajib diisi.',
            'sph.required'               => 'SPH wajib diisi.',
            'koordinat_geojson.required' => 'Koordinat GeoJSON wajib diisi.',
            'tahun_tanam.required'       => 'Tahun tanam wajib diisi.',
            'jenis_tanah.required'       => 'Jenis tanah wajib dipilih.',
            'topografi.required'         => 'Topografi wajib dipilih.',
        ]);

        json_decode($validated['koordinat_geojson']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['koordinat_geojson' => 'Format GeoJSON tidak valid.'])->withInput();
        }

        BlokLahan::create($validated);
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil ditambahkan.');
    }

    public function show(BlokLahan $blokLahan)
    {
        $blokLahan->load(['anggota', 'kondisiTerbaru', 'rekomendasiRbsTerbaru']);
        return view('blok_lahan.show', compact('blokLahan'));
    }

    public function edit(BlokLahan $blokLahan)
    {
        $anggotas = Anggota::orderBy('nama')->get();
        $existingBloks = BlokLahan::where('id', '!=', $blokLahan->id)
            ->select('id', 'nama_blok', 'koordinat_geojson')->get()
            ->map(fn($b) => ['nama' => $b->nama_blok, 'geojson' => json_decode($b->koordinat_geojson, true)])
            ->filter(fn($b) => $b['geojson'] !== null)->values();

        return view('blok_lahan.edit', compact('blokLahan', 'anggotas', 'existingBloks'));
    }

    public function update(Request $request, BlokLahan $blokLahan)
    {
        $validated = $request->validate([
            'anggota_id'        => ['required', 'exists:anggotas,id'],
            'nama_blok'         => ['required', 'string', 'max:100'],
            'luas_ha'           => ['required', 'numeric', 'min:0.01'],
            'sph'               => ['required', 'integer', 'min:1'],
            'koordinat_geojson' => ['required', 'string'],
            'tahun_tanam'       => ['required', 'integer', 'min:1990', 'max:' . now()->year],
            'jenis_tanah'       => ['required', 'in:Tanah Lempung,Tanah Lempung Berpasir,Tanah Berpasir,Tanah Liat,Tanah Gambut,Tanah Aluvial,Tanah Podsolik Merah Kuning (PMK),Tanah Laterit,Tanah Berbatu,Lainnya'],
            'topografi'         => ['required', 'in:Datar 0-15°,Bergelombang 15-30°,Curam >30°'],
        ]);

        json_decode($validated['koordinat_geojson']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['koordinat_geojson' => 'Format GeoJSON tidak valid.'])->withInput();
        }

        $blokLahan->update($validated);
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil diperbarui.');
    }

    public function destroy(BlokLahan $blokLahan)
    {
        $blokLahan->delete();
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil dihapus.');
    }
}
