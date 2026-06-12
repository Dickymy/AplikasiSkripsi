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

        // 2. Cek apakah data kondisi cukup untuk analisis (minimal 1 field terisi)
        if (!$this->kondisiCukup($kondisi)) {
            return $this->hasilDataTidakCukup($blok, $kondisi);
        }

        // 3. Ambil kategori umur langsung dari blok (kriteria terintegrasi)
        $kategoriUmur = $blok->kategori_umur;

        // 4. Ambil semua rule aktif, urutkan dari prioritas tertinggi (nilai terkecil = lebih penting)
        $rules = RuleBaseLanjutan::aktif()->orderBy('prioritas')->get();

        // 5. Evaluasi setiap rule (Forward Chaining)
        $rulesTerpicu = [];
        foreach ($rules as $rule) {
            if ($this->evaluasiRule($rule, $kondisi, $kategoriUmur)) {
                $rulesTerpicu[] = $rule;
            }
        }

        // 6. Jika tidak ada rule terpicu, return status normal
        if (empty($rulesTerpicu)) {
            return $this->hasilNormal($blok, $kondisi);
        }

        // 7. Susun output dari semua rule terpicu
        return $this->susunHasil($blok, $kondisi, $rulesTerpicu);
    }

    /**
     * Jalankan analisis RBS untuk semua blok lahan yang memiliki kondisi.
     */
    public function analisisSemua(): array
    {
        $blokLahans = BlokLahan::whereHas('kondisiLahans')
            ->with(['kondisiTerbaru'])
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
     *
     * PENTING: Rule hanya terpicu jika minimal ada 1 kondisi dari rule
     * yang benar-benar dicocokkan dengan data input yang TERSEDIA (bukan NULL).
     * Ini mencegah rule terpicu hanya karena data input kosong.
     */
    private function evaluasiRule(RuleBaseLanjutan $rule, KondisiLahan $kondisi, ?string $kategoriUmur): bool
    {
        $jumlahKondisiDiRule = 0;   // Berapa banyak kondisi non-null di rule
        $jumlahKondisiCocok = 0;    // Berapa yang benar-benar cocok dengan data input yang ada

        // Cek warna daun
        if ($rule->kondisi_warna_daun !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->warna_daun === null) {
                return false; // Data input tidak tersedia, rule tidak bisa dinilai
            }
            if ($rule->kondisi_warna_daun !== $kondisi->warna_daun) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek range pH
        if ($rule->kondisi_ph_min !== null || $rule->kondisi_ph_max !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->ph_tanah === null) {
                return false; // Data pH tidak tersedia, rule pH tidak bisa dinilai
            }
            if ($rule->kondisi_ph_min !== null && (float) $kondisi->ph_tanah < (float) $rule->kondisi_ph_min) {
                return false;
            }
            if ($rule->kondisi_ph_max !== null && (float) $kondisi->ph_tanah > (float) $rule->kondisi_ph_max) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek kelembaban
        if ($rule->kondisi_kelembaban !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->kelembaban_tanah === null) {
                return false;
            }
            if ($rule->kondisi_kelembaban !== $kondisi->kelembaban_tanah) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek musim
        if ($rule->kondisi_musim !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->musim_saat_ini === null) {
                return false;
            }
            if ($rule->kondisi_musim !== $kondisi->musim_saat_ini) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek drainase
        if ($rule->kondisi_drainase !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->kondisi_drainase === null) {
                return false;
            }
            if ($rule->kondisi_drainase !== $kondisi->kondisi_drainase) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek defisiensi (array contains check)
        if ($rule->kondisi_defisiensi !== null) {
            $jumlahKondisiDiRule++;
            $defisiensiInput = $kondisi->gejala_defisiensi ?? [];
            if (empty($defisiensiInput)) {
                return false; // Tidak ada data defisiensi diinput
            }
            if (!in_array($rule->kondisi_defisiensi, $defisiensiInput)) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek kondisi pelepah
        if ($rule->kondisi_pelepah !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->kondisi_pelepah === null) {
                return false;
            }
            if ($rule->kondisi_pelepah !== $kondisi->kondisi_pelepah) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek serangan hama
        if ($rule->ada_serangan_hama === true) {
            $jumlahKondisiDiRule++;
            if (!$kondisi->ada_serangan_hama) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek kondisi tandan
        if ($rule->kondisi_tandan !== null) {
            $jumlahKondisiDiRule++;
            if ($kondisi->kondisi_tandan === null) {
                return false;
            }
            if ($rule->kondisi_tandan !== $kondisi->kondisi_tandan) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // Cek kategori umur (ini dari blok, bukan dari kondisi input)
        if ($rule->kondisi_kategori_umur !== null) {
            $jumlahKondisiDiRule++;
            if ($rule->kondisi_kategori_umur !== $kategoriUmur) {
                return false;
            }
            $jumlahKondisiCocok++;
        }

        // SAFETY: Rule harus punya minimal 1 kondisi yang dicocokkan
        // DAN minimal 1 kondisi yang benar-benar cocok dengan data TERSEDIA
        if ($jumlahKondisiDiRule === 0 || $jumlahKondisiCocok === 0) {
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

        // Hitung dosis numerik berdasarkan kriteria lahan (integrasi Forward Chaining)
        $dosis = $this->hitungDosisStandar($blok);

        // Tentukan catatan dosis berdasarkan status
        $catatanDosis = $this->tentukanCatatanDosis($statusDominan, $masalah);

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
                'dosis_urea'              => $dosis['dosis_urea'],
                'dosis_kcl'               => $dosis['dosis_kcl'],
                'total_urea'              => $dosis['total_urea'],
                'total_kcl'               => $dosis['total_kcl'],
                'catatan_dosis'           => $catatanDosis,
            ]
        );

        return ['sukses' => true, 'rekomendasi' => $hasil];
    }

    /**
     * Cek apakah data kondisi cukup untuk dianalisis.
     * Minimal harus ada 1 field observasi visual/tanah yang terisi.
     */
    private function kondisiCukup(KondisiLahan $kondisi): bool
    {
        // Field-field yang dianggap sebagai data observasi penting
        return $kondisi->warna_daun !== null
            || $kondisi->ph_tanah !== null
            || $kondisi->kelembaban_tanah !== null
            || $kondisi->musim_saat_ini !== null
            || $kondisi->kondisi_drainase !== null
            || $kondisi->kondisi_pelepah !== null
            || $kondisi->kondisi_tandan !== null
            || !empty($kondisi->gejala_defisiensi)
            || $kondisi->ada_serangan_hama === true;
    }

    /**
     * Return hasil ketika data kondisi tidak cukup untuk analisis.
     */
    private function hasilDataTidakCukup(BlokLahan $blok, KondisiLahan $kondisi): array
    {
        $dosis = $this->hitungDosisStandar($blok);

        $hasil = RekomendasiRbs::updateOrCreate(
            ['blok_lahan_id' => $blok->id],
            [
                'kondisi_lahan_id'        => $kondisi->id,
                'admin_id'                => Auth::guard('admin')->id(),
                'tanggal_analisis'        => now()->toDateString(),
                'rules_terpicu'           => [],
                'masalah_teridentifikasi' => ['Data kondisi lahan belum lengkap untuk analisis'],
                'rekomendasi_pupuk'       => [['jenis_utama' => 'Pupuk Standar Rutin', 'dosis' => 'Sesuai jadwal pemupukan reguler — lengkapi data kondisi untuk rekomendasi spesifik']],
                'saran_tindakan_utama'    => 'Data observasi kondisi lahan belum cukup untuk memberikan rekomendasi spesifik. Silakan lengkapi data kondisi (warna daun, pH tanah, kelembaban, kondisi drainase, dll) lalu jalankan analisis ulang.',
                'status_kebutuhan_dominan' => 'Normal',
                'jumlah_rule_terpicu'     => 0,
                'dosis_urea'              => $dosis['dosis_urea'],
                'dosis_kcl'               => $dosis['dosis_kcl'],
                'total_urea'              => $dosis['total_urea'],
                'total_kcl'               => $dosis['total_kcl'],
                'catatan_dosis'           => 'Dosis standar berdasarkan umur tanaman, jenis tanah, dan topografi. Lengkapi data kondisi lahan untuk mendapat rekomendasi yang lebih akurat.',
            ]
        );

        return ['sukses' => true, 'rekomendasi' => $hasil];
    }

    /**
     * Return status normal ketika tidak ada rule yang terpicu.
     */
    private function hasilNormal(BlokLahan $blok, KondisiLahan $kondisi): array
    {
        $dosis = $this->hitungDosisStandar($blok);

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
                'dosis_urea'              => $dosis['dosis_urea'],
                'dosis_kcl'               => $dosis['dosis_kcl'],
                'total_urea'              => $dosis['total_urea'],
                'total_kcl'               => $dosis['total_kcl'],
                'catatan_dosis'           => 'Kondisi lahan normal. Dosis dapat diaplikasikan sesuai jadwal pemupukan standar.',
            ]
        );

        return ['sukses' => true, 'rekomendasi' => $hasil];
    }

    /**
     * Tentukan catatan kontekstual untuk dosis berdasarkan status dan masalah.
     */
    private function tentukanCatatanDosis(string $statusDominan, array $masalah): string
    {
        $masalahStr = implode(' ', $masalah);

        if ($statusDominan === 'Tunda') {
            // Cek alasan spesifik tunda
            if (str_contains($masalahStr, 'Tergenang') || str_contains($masalahStr, 'Waterlogging')) {
                return 'TUNDA APLIKASI PUPUK TANAH. Lahan tergenang menyebabkan leaching. Perbaiki drainase terlebih dahulu, baru aplikasikan dosis ini setelah kondisi normal.';
            }
            if (str_contains($masalahStr, 'Kekeringan') || str_contains($masalahStr, 'Kemarau')) {
                return 'TUNDA PUPUK KIMIA. Kondisi terlalu kering — pupuk tidak akan terlarut dan berisiko membakar akar. Tunggu hujan turun, baru aplikasikan dosis ini.';
            }
            if (str_contains($masalahStr, 'Tua Renta')) {
                return 'Efisiensi penyerapan hara sangat rendah pada tanaman tua. Pertimbangkan pengurangan dosis 40-50% atau evaluasi replanting.';
            }
            return 'Pemupukan ditunda sampai kondisi lahan diperbaiki. Dosis ini dapat diaplikasikan setelah masalah teratasi.';
        }

        if ($statusDominan === 'Darurat') {
            if (str_contains($masalahStr, 'pH') || str_contains($masalahStr, 'Masam')) {
                return 'PERHATIAN: Jangan aplikasikan Urea/KCl sebelum pH tanah dinaikkan ke 5.0+. Lakukan pengapuran (Dolomit) terlebih dahulu. Dosis ini berlaku setelah pH normal.';
            }
            if (str_contains($masalahStr, 'Busuk') || str_contains($masalahStr, 'Ganoderma')) {
                return 'PRIORITASKAN penanganan penyakit terlebih dahulu. Dosis pupuk standar ini berlaku setelah kondisi tanaman membaik.';
            }
            return 'Status DARURAT — atasi masalah utama terlebih dahulu. Dosis ini adalah kebutuhan standar yang berlaku setelah kondisi diperbaiki.';
        }

        if ($statusDominan === 'Segera') {
            return 'Segera aplikasikan dosis pupuk standar ini bersamaan dengan penanganan masalah yang teridentifikasi.';
        }

        return 'Kondisi lahan normal. Dosis dapat diaplikasikan sesuai jadwal pemupukan standar.';
    }

    /**
     * Hitung dosis standar Urea & KCl berdasarkan kriteria lahan (umur, tanah, topografi).
     */
    private function hitungDosisStandar(BlokLahan $blok): array
    {
        if (!$blok->tahun_tanam || !$blok->jenis_tanah || !$blok->topografi) {
            return ['dosis_urea' => null, 'dosis_kcl' => null, 'total_urea' => null, 'total_kcl' => null];
        }

        $umur = now()->year - $blok->tahun_tanam;
        $kategoriUmur = $this->tentukanKategoriUmur($umur);

        // Base dosis per kategori umur (referensi PPKS)
        $baseDosis = match($kategoriUmur) {
            'Belum Menghasilkan' => ['urea' => 0.5,  'kcl' => 0.5],
            'Remaja'             => ['urea' => 1.5,  'kcl' => 1.0],
            'Menghasilkan Muda'  => ['urea' => 2.25, 'kcl' => 1.75],
            'Menghasilkan Tua'   => ['urea' => 2.75, 'kcl' => 2.25],
            'Tua Renta'          => ['urea' => 1.5,  'kcl' => 1.5],
            default              => ['urea' => 1.5,  'kcl' => 1.0],
        };

        // Multiplier jenis tanah
        $multiplierTanah = match($blok->jenis_tanah) {
            'Tanah Lempung'                     => ['urea' => 1.0, 'kcl' => 1.0],
            'Tanah Lempung Berpasir'            => ['urea' => 1.1, 'kcl' => 1.1],
            'Tanah Berpasir'                    => ['urea' => 1.3, 'kcl' => 1.4],
            'Tanah Liat'                        => ['urea' => 0.9, 'kcl' => 0.9],
            'Tanah Gambut'                      => ['urea' => 0.6, 'kcl' => 1.5],
            'Tanah Aluvial'                     => ['urea' => 1.0, 'kcl' => 1.0],
            'Tanah Podsolik Merah Kuning (PMK)' => ['urea' => 1.1, 'kcl' => 1.2],
            'Tanah Laterit'                     => ['urea' => 1.1, 'kcl' => 1.2],
            'Tanah Berbatu'                     => ['urea' => 1.2, 'kcl' => 1.2],
            default                             => ['urea' => 1.0, 'kcl' => 1.0],
        };

        // Multiplier topografi
        $multiplierTopo = match($blok->topografi) {
            'Datar 0-15°'         => ['urea' => 1.0, 'kcl' => 1.0],
            'Bergelombang 15-30°' => ['urea' => 1.1, 'kcl' => 1.1],
            'Curam >30°'          => ['urea' => 1.2, 'kcl' => 1.2],
            default               => ['urea' => 1.0, 'kcl' => 1.0],
        };

        // Hitung dosis akhir (bulatkan ke 0.25 terdekat)
        $dosisUrea = round($baseDosis['urea'] * $multiplierTanah['urea'] * $multiplierTopo['urea'] * 4) / 4;
        $dosisKcl  = round($baseDosis['kcl']  * $multiplierTanah['kcl']  * $multiplierTopo['kcl'] * 4) / 4;

        // Hitung total kebutuhan
        $totalUrea = $dosisUrea * $blok->sph * $blok->luas_ha;
        $totalKcl  = $dosisKcl  * $blok->sph * $blok->luas_ha;

        return [
            'dosis_urea' => $dosisUrea,
            'dosis_kcl'  => $dosisKcl,
            'total_urea' => $totalUrea,
            'total_kcl'  => $totalKcl,
        ];
    }

    /**
     * Tentukan kategori umur tanaman kelapa sawit.
     */
    private function tentukanKategoriUmur(int $umur): string
    {
        if ($umur < 3) return 'Belum Menghasilkan';
        if ($umur <= 8) return 'Remaja';
        if ($umur <= 14) return 'Menghasilkan Muda';
        if ($umur <= 25) return 'Menghasilkan Tua';
        return 'Tua Renta';
    }
}
