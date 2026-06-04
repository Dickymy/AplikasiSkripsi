<?php

namespace App\Services;

use App\Models\BlokLahan;
use App\Models\KondisiLahan;
use App\Models\RuleBaseLanjutan;
use App\Models\RekomendasiRbs;
use Illuminate\Support\Facades\Auth;

class RbsService
{
    /**
     * Jalankan analisis RBS untuk satu blok lahan berdasarkan kondisi terbaru.
     *
     * @throws \Exception
     */
    public function analisis(BlokLahan $blok): array
    {
        // 1. Ambil kondisi lahan terbaru
        $kondisi = $blok->kondisiTerbaru;
        if (!$kondisi) {
            throw new \Exception("Data kondisi lahan belum tersedia untuk blok '{$blok->nama_blok}'.");
        }

        // 2. Ambil kategori umur dari KriteriaLahan (gunakan sistem existing)
        $kategoriUmur = $blok->kriteriaLahan?->kategori_umur ?? null;

        // 3. Ambil semua rule aktif, urutkan dari prioritas tertinggi (nilai terkecil = lebih penting)
        $rules = RuleBaseLanjutan::aktif()->orderBy('prioritas')->get();

        // 4. Evaluasi setiap rule (Forward Chaining)
        $rulesTerpicu = [];
        foreach ($rules as $rule) {
            if ($this->evaluasiRule($rule, $kondisi, $kategoriUmur)) {
                $rulesTerpicu[] = $rule;
            }
        }

        // 5. Jika tidak ada rule terpicu, return status normal
        if (empty($rulesTerpicu)) {
            return $this->hasilNormal($blok, $kondisi);
        }

        // 6. Susun output dari semua rule terpicu
        return $this->susunHasil($blok, $kondisi, $rulesTerpicu);
    }

    /**
     * Jalankan analisis RBS untuk semua blok lahan yang memiliki kondisi.
     */
    public function analisisSemua(): array
    {
        $blokLahans = BlokLahan::whereHas('kondisiLahans')
            ->with(['kondisiTerbaru', 'kriteriaLahan'])
            ->get();
        $results = [];
        $errors  = [];

        foreach ($blokLahans as $blok) {
            try {
                $results[] = [
                    'blok'   => $blok,
                    'result' => $this->analisis($blok),
                ];
            } catch (\Exception $e) {
                $errors[] = "Blok {$blok->nama_blok}: " . $e->getMessage();
            }
        }

        return ['results' => $results, 'errors' => $errors];
    }

    /**
     * Evaluasi apakah sebuah rule cocok dengan kondisi saat ini.
     * Semua kondisi yang diisi di rule harus terpenuhi (AND logic).
     * Kondisi NULL di rule = tidak relevan / diabaikan.
     */
    private function evaluasiRule(RuleBaseLanjutan $rule, KondisiLahan $kondisi, ?string $kategoriUmur): bool
    {
        // Cek warna daun
        if ($rule->kondisi_warna_daun !== null && $rule->kondisi_warna_daun !== $kondisi->warna_daun) {
            return false;
        }

        // Cek range pH (hanya evaluasi jika kondisi punya data pH)
        if ($rule->kondisi_ph_min !== null && $kondisi->ph_tanah !== null) {
            if ((float) $kondisi->ph_tanah < (float) $rule->kondisi_ph_min) {
                return false;
            }
        }
        if ($rule->kondisi_ph_max !== null && $kondisi->ph_tanah !== null) {
            if ((float) $kondisi->ph_tanah > (float) $rule->kondisi_ph_max) {
                return false;
            }
        }

        // Cek kelembaban
        if ($rule->kondisi_kelembaban !== null && $rule->kondisi_kelembaban !== $kondisi->kelembaban_tanah) {
            return false;
        }

        // Cek musim
        if ($rule->kondisi_musim !== null && $rule->kondisi_musim !== $kondisi->musim_saat_ini) {
            return false;
        }

        // Cek drainase
        if ($rule->kondisi_drainase !== null && $rule->kondisi_drainase !== $kondisi->kondisi_drainase) {
            return false;
        }

        // Cek defisiensi (array contains check)
        if ($rule->kondisi_defisiensi !== null) {
            $defisiensiInput = $kondisi->gejala_defisiensi ?? [];
            if (!in_array($rule->kondisi_defisiensi, $defisiensiInput)) {
                return false;
            }
        }

        // Cek kondisi pelepah
        if ($rule->kondisi_pelepah !== null && $rule->kondisi_pelepah !== $kondisi->kondisi_pelepah) {
            return false;
        }

        // Cek serangan hama (hanya cek jika rule menentukan harus ada hama)
        if ($rule->ada_serangan_hama === true) {
            if (!$kondisi->ada_serangan_hama) {
                return false;
            }
        }

        // Cek kondisi tandan
        if ($rule->kondisi_tandan !== null && $rule->kondisi_tandan !== $kondisi->kondisi_tandan) {
            return false;
        }

        // Cek kategori umur (opsional)
        if ($rule->kondisi_kategori_umur !== null && $rule->kondisi_kategori_umur !== $kategoriUmur) {
            return false;
        }

        return true;
    }

    /**
     * Susun hasil analisis dari rule-rule yang terpicu.
     */
    private function susunHasil(BlokLahan $blok, KondisiLahan $kondisi, array $rules): array
    {
        // Tentukan status dominan (prioritaskan Darurat > Segera > Normal > Tunda)
        $hierarki = ['Darurat' => 4, 'Segera' => 3, 'Normal' => 2, 'Tunda' => 1];
        $statusDominan = collect($rules)
            ->sortByDesc(fn($r) => $hierarki[$r->status_kebutuhan] ?? 0)
            ->first()
            ->status_kebutuhan;

        // Kumpulkan masalah unik
        $masalah = collect($rules)->pluck('indikasi_masalah')->unique()->values()->toArray();

        // Kumpulkan rekomendasi pupuk (deduplicate by jenis_pupuk_utama)
        $pupuk = collect($rules)
            ->unique('jenis_pupuk_utama')
            ->map(fn($r) => [
                'jenis_utama'     => $r->jenis_pupuk_utama,
                'jenis_pendukung' => $r->jenis_pupuk_pendukung,
                'dosis'           => $r->dosis_anjuran,
                'metode'          => $r->metode_aplikasi,
                'waktu'           => $r->waktu_aplikasi,
            ])
            ->values()
            ->toArray();

        // Gabungkan saran tindakan dari rule prioritas tertinggi (3 teratas)
        $saranUtama = collect($rules)
            ->sortBy('prioritas')
            ->take(3)
            ->pluck('saran_tindakan')
            ->implode(' | ');

        // Simpan ke database (satu record aktif per blok)
        $hasil = RekomendasiRbs::updateOrCreate(
            ['blok_lahan_id' => $blok->id],
            [
                'kondisi_lahan_id'        => $kondisi->id,
                'admin_id'                => Auth::guard('admin')->id(),
                'tanggal_analisis'        => now()->toDateString(),
                'rules_terpicu'           => collect($rules)->map(fn($r) => [
                    'rule_id'   => $r->id,
                    'indikasi'  => $r->indikasi_masalah,
                    'pupuk'     => $r->jenis_pupuk_utama,
                    'status'    => $r->status_kebutuhan,
                    'prioritas' => $r->prioritas,
                ])->toArray(),
                'masalah_teridentifikasi' => $masalah,
                'rekomendasi_pupuk'       => $pupuk,
                'saran_tindakan_utama'    => $saranUtama,
                'status_kebutuhan_dominan' => $statusDominan,
                'jumlah_rule_terpicu'     => count($rules),
            ]
        );

        return ['sukses' => true, 'rekomendasi' => $hasil];
    }

    /**
     * Return status normal ketika tidak ada rule yang terpicu.
     */
    private function hasilNormal(BlokLahan $blok, KondisiLahan $kondisi): array
    {
        $hasil = RekomendasiRbs::updateOrCreate(
            ['blok_lahan_id' => $blok->id],
            [
                'kondisi_lahan_id'        => $kondisi->id,
                'admin_id'                => Auth::guard('admin')->id(),
                'tanggal_analisis'        => now()->toDateString(),
                'rules_terpicu'           => [],
                'masalah_teridentifikasi' => ['Tidak ada masalah teridentifikasi'],
                'rekomendasi_pupuk'       => [['jenis_utama' => 'Pupuk Standar Rutin', 'dosis' => 'Sesuai jadwal pemupukan reguler']],
                'saran_tindakan_utama'    => 'Lanjutkan program pemupukan standar. Kondisi lahan dalam batas normal.',
                'status_kebutuhan_dominan' => 'Normal',
                'jumlah_rule_terpicu'     => 0,
            ]
        );

        return ['sukses' => true, 'rekomendasi' => $hasil];
    }
}
