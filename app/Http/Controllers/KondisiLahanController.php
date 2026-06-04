<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\KondisiLahan;
use Illuminate\Http\Request;

class KondisiLahanController extends Controller
{
    public function index()
    {
        $data = KondisiLahan::with('blokLahan')
            ->latest('tanggal_observasi')
            ->paginate(15);

        return view('kondisi_lahan.index', compact('data'));
    }

    public function create(Request $request)
    {
        $bloks = BlokLahan::orderBy('nama_blok')->get();
        $selectedBlokId = $request->query('blok_lahan_id');

        return view('kondisi_lahan.create', compact('bloks', 'selectedBlokId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blok_lahan_id'        => ['required', 'exists:blok_lahans,id'],
            'tanggal_observasi'    => ['required', 'date'],
            'ph_tanah'             => ['nullable', 'numeric', 'min:3', 'max:8'],
            'kelembaban_tanah'     => ['nullable', 'string'],
            'curah_hujan_kategori' => ['nullable', 'string'],
            'musim_saat_ini'       => ['nullable', 'string'],
            'warna_daun'           => ['nullable', 'string'],
            'kondisi_pelepah'      => ['nullable', 'string'],
            'gejala_defisiensi'    => ['nullable', 'array'],
            'gejala_defisiensi.*'  => ['string'],
            'kondisi_tandan'       => ['nullable', 'string'],
            'kondisi_drainase'     => ['nullable', 'string'],
            'ada_gulma_dominan'    => ['nullable', 'boolean'],
            'ada_serangan_hama'    => ['nullable', 'boolean'],
            'catatan_observasi'    => ['nullable', 'string', 'max:1000'],
        ], [
            'blok_lahan_id.required'     => 'Blok lahan wajib dipilih.',
            'tanggal_observasi.required' => 'Tanggal observasi wajib diisi.',
            'ph_tanah.numeric'           => 'pH tanah harus berupa angka.',
            'ph_tanah.min'               => 'pH tanah minimal 3.0.',
            'ph_tanah.max'               => 'pH tanah maksimal 8.0.',
        ]);

        // Checkbox boolean: jika tidak dicentang maka tidak ada di request, set ke false
        $validated['ada_gulma_dominan'] = $request->boolean('ada_gulma_dominan');
        $validated['ada_serangan_hama'] = $request->boolean('ada_serangan_hama');

        // Jika gejala_defisiensi tidak dipilih sama sekali
        $validated['gejala_defisiensi'] = $validated['gejala_defisiensi'] ?? [];

        KondisiLahan::create($validated);

        return redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil disimpan.');
    }

    public function show(KondisiLahan $kondisiLahan)
    {
        // Show tidak dipakai — data kondisi dilihat melalui rbs.detail
        return redirect()->route('kondisi-lahan.index');
    }

    public function edit(KondisiLahan $kondisiLahan)
    {
        $bloks = BlokLahan::orderBy('nama_blok')->get();
        return view('kondisi_lahan.edit', compact('kondisiLahan', 'bloks'));
    }

    public function update(Request $request, KondisiLahan $kondisiLahan)
    {
        $validated = $request->validate([
            'blok_lahan_id'        => ['required', 'exists:blok_lahans,id'],
            'tanggal_observasi'    => ['required', 'date'],
            'ph_tanah'             => ['nullable', 'numeric', 'min:3', 'max:8'],
            'kelembaban_tanah'     => ['nullable', 'string'],
            'curah_hujan_kategori' => ['nullable', 'string'],
            'musim_saat_ini'       => ['nullable', 'string'],
            'warna_daun'           => ['nullable', 'string'],
            'kondisi_pelepah'      => ['nullable', 'string'],
            'gejala_defisiensi'    => ['nullable', 'array'],
            'gejala_defisiensi.*'  => ['string'],
            'kondisi_tandan'       => ['nullable', 'string'],
            'kondisi_drainase'     => ['nullable', 'string'],
            'ada_gulma_dominan'    => ['nullable', 'boolean'],
            'ada_serangan_hama'    => ['nullable', 'boolean'],
            'catatan_observasi'    => ['nullable', 'string', 'max:1000'],
        ], [
            'blok_lahan_id.required'     => 'Blok lahan wajib dipilih.',
            'tanggal_observasi.required' => 'Tanggal observasi wajib diisi.',
        ]);

        $validated['ada_gulma_dominan'] = $request->boolean('ada_gulma_dominan');
        $validated['ada_serangan_hama'] = $request->boolean('ada_serangan_hama');
        $validated['gejala_defisiensi'] = $validated['gejala_defisiensi'] ?? [];

        $kondisiLahan->update($validated);

        return redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil diperbarui.');
    }

    public function destroy(KondisiLahan $kondisiLahan)
    {
        $kondisiLahan->delete();

        return redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil dihapus.');
    }
}
