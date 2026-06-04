<?php

namespace App\Services;

use App\Models\BlokLahan;
use App\Models\RekomendasiSpk;
use App\Models\RuleBase;
use Illuminate\Support\Facades\Auth;

class SpkService
{
    /**
     * Jalankan analisis SPK Forward Chaining untuk satu blok lahan.
     *
     * @param  BlokLahan $blok
     * @return array hasil analisis
     * @throws \Exception jika kriteria atau rule tidak ditemukan
     */
    public function analisis(BlokLahan $blok): array
    {
        // 1. Ambil data kriteria lahan
        $kriteria = $blok->kriteriaLahan;
        if (!$kriteria) {
            throw new \Exception("Blok lahan '{$blok->nama_blok}' belum memiliki data kriteria lahan.");
        }

        // 2. Forward Chaining — Tahap Penentuan Fakta
        $umurTanaman  = now()->year - $kriteria->tahun_tanam;
        $kategoriUmur = $this->tentukanKategoriUmur($umurTanaman);
        $jenisTanah   = $kriteria->jenis_tanah;
        $topografi    = $kriteria->topografi;

        // 3. Forward Chaining — Tahap Pencocokan Rule (Pattern Matching)
        $parameterKondisi = "{$kategoriUmur}|{$jenisTanah}|{$topografi}";
        $rule = RuleBase::where('parameter_kondisi', $parameterKondisi)->first();

        if (!$rule) {
            throw new \Exception(
                "Tidak ditemukan rule untuk kondisi: {$parameterKondisi}. " .
                "Pastikan tabel rule_bases memiliki aturan yang sesuai."
            );
        }

        // 4. Forward Chaining — Tahap Eksekusi (Firing) & Kalkulasi Logistik
        $dosisUrea = $rule->takaran_urea;
        $dosisKcl  = $rule->takaran_kcl;
        $totalUrea = $dosisUrea * $blok->sph * $blok->luas_ha;
        $totalKcl  = $dosisKcl  * $blok->sph * $blok->luas_ha;

        // 5. Simpan konklusi ke tabel rekomendasi_spks
        $rekomendasi = RekomendasiSpk::updateOrCreate(
            ['blok_lahan_id'   => $blok->id],
            [
                'admin_id'        => Auth::guard('admin')->id(),
                'tanggal_analisis' => now()->toDateString(),
                'dosis_urea'      => $dosisUrea,
                'dosis_kcl'       => $dosisKcl,
                'total_urea'      => $totalUrea,
                'total_kcl'       => $totalKcl,
                'status_akhir'    => $rule->status_pemupukan,
            ]
        );

        return [
            'rekomendasi'     => $rekomendasi,
            'kategori_umur'   => $kategoriUmur,
            'umur_tanaman'    => $umurTanaman,
            'parameter_rule'  => $parameterKondisi,
            'karung_urea'     => (int) ceil($totalUrea / 50),
            'karung_kcl'      => (int) ceil($totalKcl / 50),
        ];
    }

    /**
     * Jalankan analisis SPK untuk semua blok lahan yang memiliki kriteria.
     */
    public function analisisSemua(): array
    {
        $blokLahans = BlokLahan::whereHas('kriteriaLahan')
            ->with('kriteriaLahan')
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
     * Tentukan kategori umur tanaman kelapa sawit berdasarkan tahun tanam.
     */
    private function tentukanKategoriUmur(int $umur): string
    {
        if ($umur >= 3 && $umur <= 8) {
            return 'Remaja';
        } elseif ($umur >= 9 && $umur <= 14) {
            return 'Menghasilkan Muda';
        } elseif ($umur >= 15 && $umur <= 25) {
            return 'Menghasilkan Tua';
        } elseif ($umur < 3) {
            return 'Belum Menghasilkan';
        } else {
            return 'Tua Renta';
        }
    }
}
