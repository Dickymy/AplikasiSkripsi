<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\KondisiLahan;
use Illuminate\Http\Request;

class KondisiLahanController extends Controller
{
    public function index(Request $request)
    {
        $query = KondisiLahan::with('blokLahan.anggota')
            ->latest('tanggal_observasi');

        // Filter by blok lahan
        if ($request->filled('blok_lahan_id')) {
            $query->where('blok_lahan_id', $request->blok_lahan_id);
        }

        // Filter by anggota (through blokLahan)
        if ($request->filled('anggota_id')) {
            $query->whereHas('blokLahan', function ($q) use ($request) {
                $q->where('anggota_id', $request->anggota_id);
            });
        }

        $allData = $query->get();

        // Group by anggota — sort: terbaru di atas
        $grouped = $allData->groupBy(function ($k) {
            return $k->blokLahan->anggota_id ?? 0;
        })->map(function ($items) {
            $anggota = $items->first()->blokLahan->anggota;
            return [
                'anggota'         => $anggota,
                'items'           => $items,
                'latest_activity' => $items->max(fn($k) => $k->created_at?->timestamp ?? 0),
            ];
        })->sortByDesc('latest_activity')->values();

        $anggotas = \App\Models\Anggota::orderBy('nama')->get();
        $bloks = \App\Models\BlokLahan::orderBy('nama_blok')->get();

        return view('kondisi_lahan.index', compact('grouped', 'anggotas', 'bloks'));
    }

    public function create(Request $request)
    {
        $bloks = BlokLahan::with('anggota')->latest()->get();
        $anggotas = \App\Models\Anggota::orderBy('nama')->get();
        $selectedBlokId = $request->query('blok_lahan_id');

        // Build bloks data as JSON for cascading filter JS
        $bloksJson = $bloks->map(function ($b) {
            return [
                'id'          => $b->id,
                'nama_blok'   => $b->nama_blok,
                'anggota_id'  => $b->anggota_id,
                'anggota_nama'=> $b->anggota?->nama ?? '-',
                'luas_ha'     => $b->luas_ha,
                'kategori'    => $b->kategori_umur ?? '-',
                'updated_at'  => $b->updated_at?->timestamp ?? 0,
            ];
        })->values();

        return view('kondisi_lahan.create', compact('bloks', 'anggotas', 'selectedBlokId', 'bloksJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blok_lahan_id'              => ['required', 'exists:blok_lahans,id'],
            'tanggal_observasi'          => ['required', 'date'],
            'tanggal_pemupukan_terakhir' => ['nullable', 'date', 'before_or_equal:today'],
            'ph_tanah'                   => ['nullable', 'numeric', 'min:3', 'max:8'],
            'kelembaban_tanah'           => ['nullable', 'string'],
            'curah_hujan_kategori'       => ['nullable', 'string'],
            'musim_saat_ini'             => ['nullable', 'string'],
            'warna_daun'                 => ['nullable', 'string'],
            'kondisi_pelepah'            => ['nullable', 'string'],
            'gejala_defisiensi'          => ['nullable', 'array'],
            'gejala_defisiensi.*'        => ['string'],
            'kondisi_tandan'             => ['nullable', 'string'],
            'kondisi_drainase'           => ['nullable', 'string'],
            'ada_gulma_dominan'          => ['nullable', 'boolean'],
            'ada_serangan_hama'          => ['nullable', 'boolean'],
            'catatan_observasi'          => ['nullable', 'string', 'max:1000'],
        ], [
            'blok_lahan_id.required'     => 'Blok lahan wajib dipilih.',
            'tanggal_observasi.required' => 'Tanggal observasi wajib diisi.',
            'ph_tanah.numeric'           => 'pH tanah harus berupa angka.',
            'ph_tanah.min'               => 'pH tanah minimal 3.0.',
            'ph_tanah.max'               => 'pH tanah maksimal 8.0.',
        ]);

        // Checkbox boolean
        $validated['ada_gulma_dominan'] = $request->boolean('ada_gulma_dominan');
        $validated['ada_serangan_hama'] = $request->boolean('ada_serangan_hama');
        $validated['gejala_defisiensi'] = $validated['gejala_defisiensi'] ?? [];

        // Validasi konsistensi logis lintas-field (A4)
        $warnings = $this->validasiKonsistensi($validated);

        KondisiLahan::create($validated);

        $redirect = redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil disimpan.');

        if (!empty($warnings)) {
            $redirect = $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function show(KondisiLahan $kondisiLahan)
    {
        // Show tidak dipakai — data kondisi dilihat melalui rbs.detail
        return redirect()->route('kondisi-lahan.index');
    }

    public function edit(KondisiLahan $kondisiLahan)
    {
        $bloks = BlokLahan::with('anggota')->orderBy('nama_blok')->get();
        return view('kondisi_lahan.edit', compact('kondisiLahan', 'bloks'));
    }

    public function update(Request $request, KondisiLahan $kondisiLahan)
    {
        $validated = $request->validate([
            'blok_lahan_id'              => ['required', 'exists:blok_lahans,id'],
            'tanggal_observasi'          => ['required', 'date'],
            'tanggal_pemupukan_terakhir' => ['nullable', 'date', 'before_or_equal:today'],
            'ph_tanah'                   => ['nullable', 'numeric', 'min:3', 'max:8'],
            'kelembaban_tanah'           => ['nullable', 'string'],
            'curah_hujan_kategori'       => ['nullable', 'string'],
            'musim_saat_ini'             => ['nullable', 'string'],
            'warna_daun'                 => ['nullable', 'string'],
            'kondisi_pelepah'            => ['nullable', 'string'],
            'gejala_defisiensi'          => ['nullable', 'array'],
            'gejala_defisiensi.*'        => ['string'],
            'kondisi_tandan'             => ['nullable', 'string'],
            'kondisi_drainase'           => ['nullable', 'string'],
            'ada_gulma_dominan'          => ['nullable', 'boolean'],
            'ada_serangan_hama'          => ['nullable', 'boolean'],
            'catatan_observasi'          => ['nullable', 'string', 'max:1000'],
        ], [
            'blok_lahan_id.required'     => 'Blok lahan wajib dipilih.',
            'tanggal_observasi.required' => 'Tanggal observasi wajib diisi.',
        ]);

        $validated['ada_gulma_dominan'] = $request->boolean('ada_gulma_dominan');
        $validated['ada_serangan_hama'] = $request->boolean('ada_serangan_hama');
        $validated['gejala_defisiensi'] = $validated['gejala_defisiensi'] ?? [];

        // Validasi konsistensi logis lintas-field (A4)
        $warnings = $this->validasiKonsistensi($validated);

        $kondisiLahan->update($validated);

        $redirect = redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil diperbarui.');

        if (!empty($warnings)) {
            $redirect = $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function destroy(KondisiLahan $kondisiLahan)
    {
        $kondisiLahan->delete();

        return redirect()->route('kondisi-lahan.index')
            ->with('success', 'Data kondisi lahan berhasil dihapus.');
    }

    /**
     * Validasi konsistensi logis lintas-field (A4).
     * Tidak menggagalkan simpan, hanya return array warning.
     */
    private function validasiKonsistensi(array $data): array
    {
        $warnings = [];

        $musim = $data['musim_saat_ini'] ?? null;
        $kelembaban = $data['kelembaban_tanah'] ?? null;
        $curahHujan = $data['curah_hujan_kategori'] ?? null;
        $drainase = $data['kondisi_drainase'] ?? null;
        $warnaDaun = $data['warna_daun'] ?? null;
        $defisiensi = $data['gejala_defisiensi'] ?? [];

        // Musim kemarau tapi kelembaban tinggi
        if ($musim === 'Musim Kemarau' && in_array($kelembaban, ['Lembab', 'Sangat Lembab'])) {
            $warnings[] = 'Musim kemarau tapi kelembaban tinggi — mohon verifikasi data.';
        }

        // Musim hujan tapi kelembaban rendah
        if ($musim === 'Musim Hujan' && in_array($kelembaban, ['Kering', 'Sangat Kering'])) {
            $warnings[] = 'Musim hujan tapi kelembaban rendah — mohon verifikasi data.';
        }

        // Drainase tergenang tapi curah hujan sangat rendah
        if ($drainase === 'Buruk — Tergenang' && $curahHujan === 'Sangat Rendah') {
            $warnings[] = 'Drainase tergenang tapi curah hujan sangat rendah — kondisi ini jarang terjadi. Pastikan data sudah benar atau tambahkan catatan penjelasan.';
        }

        // Drainase tergenang tapi musim kemarau
        if ($drainase === 'Buruk — Tergenang' && $musim === 'Musim Kemarau') {
            $warnings[] = 'Drainase tergenang saat musim kemarau — situasi tidak lazim. Jika benar, mungkin ada masalah saluran drainase yang perlu dicatat.';
        }

        // Curah hujan sangat tinggi tapi kelembaban sangat kering
        if ($curahHujan === 'Sangat Tinggi' && in_array($kelembaban, ['Kering', 'Sangat Kering'])) {
            $warnings[] = 'Curah hujan sangat tinggi tapi kelembaban rendah — data ini kontradiktif, mohon verifikasi.';
        }

        // Curah hujan sangat rendah tapi kelembaban sangat lembab
        if ($curahHujan === 'Sangat Rendah' && in_array($kelembaban, ['Lembab', 'Sangat Lembab'])) {
            $warnings[] = 'Curah hujan sangat rendah tapi kelembaban tinggi — mohon verifikasi apakah ada sumber air lain.';
        }

        // Daun hijau normal tapi ada gejala defisiensi
        if ($warnaDaun === 'Hijau Normal' && !empty($defisiensi)) {
            $warnings[] = 'Warna daun normal tapi ada dugaan unsur hara kurang — mohon verifikasi.';
        }

        // Musim hujan + curah hujan sangat rendah
        if ($musim === 'Musim Hujan' && $curahHujan === 'Sangat Rendah') {
            $warnings[] = 'Musim hujan tapi curah hujan sangat rendah — mohon pastikan data musim atau curah hujan sudah benar.';
        }

        // Musim kemarau + curah hujan sangat tinggi
        if ($musim === 'Musim Kemarau' && $curahHujan === 'Sangat Tinggi') {
            $warnings[] = 'Musim kemarau tapi curah hujan sangat tinggi — data ini tidak lazim, mohon verifikasi.';
        }

        return $warnings;
    }
}
