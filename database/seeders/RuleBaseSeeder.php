<?php

namespace Database\Seeders;

use App\Models\RuleBase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuleBaseSeeder extends Seeder
{
    /**
     * Seed tabel rule_bases dengan 150 kombinasi aturan Forward Chaining
     * berdasarkan: Kategori Umur (5) x Jenis Tanah (10) x Topografi (3)
     * 
     * Referensi pendekatan dosis PPKS:
     * - Base dosis ditentukan oleh umur tanaman.
     * - Multiplier (faktor pengali) ditentukan oleh jenis tanah dan topografi.
     */
    public function run(): void
    {
        // Hapus data lama
        RuleBase::truncate();

        $umurKategori = [
            'Belum Menghasilkan' => ['urea' => 0.5,  'kcl' => 0.5],
            'Remaja'             => ['urea' => 1.5,  'kcl' => 1.0],
            'Menghasilkan Muda'  => ['urea' => 2.25, 'kcl' => 1.75],
            'Menghasilkan Tua'   => ['urea' => 2.75, 'kcl' => 2.25],
            'Tua Renta'          => ['urea' => 1.5,  'kcl' => 1.5],
        ];

        $jenisTanah = [
            'Tanah Lempung'                     => ['urea' => 1.0, 'kcl' => 1.0],
            'Tanah Lempung Berpasir'            => ['urea' => 1.1, 'kcl' => 1.1],
            'Tanah Berpasir'                    => ['urea' => 1.3, 'kcl' => 1.4], // Pencucian tinggi
            'Tanah Liat'                        => ['urea' => 0.9, 'kcl' => 0.9], // Retensi hara baik
            'Tanah Gambut'                      => ['urea' => 0.6, 'kcl' => 1.5], // Kaya N, miskin K
            'Tanah Aluvial'                     => ['urea' => 1.0, 'kcl' => 1.0],
            'Tanah Podsolik Merah Kuning (PMK)' => ['urea' => 1.1, 'kcl' => 1.2],
            'Tanah Laterit'                     => ['urea' => 1.1, 'kcl' => 1.2],
            'Tanah Berbatu'                     => ['urea' => 1.2, 'kcl' => 1.2],
            'Lainnya'                           => ['urea' => 1.0, 'kcl' => 1.0],
        ];

        $topografi = [
            'Datar 0-15°'         => ['urea' => 1.0, 'kcl' => 1.0],
            'Bergelombang 15-30°' => ['urea' => 1.1, 'kcl' => 1.1], // Ada aliran permukaan (runoff)
            'Curam >30°'          => ['urea' => 1.2, 'kcl' => 1.2], // Runoff tinggi
        ];

        $rules = [];

        foreach ($umurKategori as $umur => $baseDosis) {
            foreach ($jenisTanah as $tanah => $multiplierTanah) {
                foreach ($topografi as $topo => $multiplierTopo) {
                    
                    // Hitung dosis akhir
                    $urea = $baseDosis['urea'] * $multiplierTanah['urea'] * $multiplierTopo['urea'];
                    $kcl  = $baseDosis['kcl']  * $multiplierTanah['kcl']  * $multiplierTopo['kcl'];

                    // Pembulatan ke 0.25 terdekat untuk memudahkan takaran praktis
                    $urea = round($urea * 4) / 4;
                    $kcl  = round($kcl * 4) / 4;

                    // Tentukan Status Pemupukan berdasarkan total multiplier
                    $totalMultiplier = ($multiplierTanah['urea'] * $multiplierTopo['urea'] + $multiplierTanah['kcl'] * $multiplierTopo['kcl']) / 2;
                    
                    if ($totalMultiplier > 1.2) {
                        $status = 'Segera Pupuk';
                    } elseif ($totalMultiplier < 0.9) {
                        $status = 'Tunda Pemupukan';
                    } else {
                        $status = 'Pemupukan Normal';
                    }

                    $rules[] = [
                        'parameter_kondisi' => "{$umur}|{$tanah}|{$topo}",
                        'takaran_urea'      => $urea,
                        'takaran_kcl'       => $kcl,
                        'status_pemupukan'  => $status,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }
        }

        // Insert menggunakan chunk agar lebih efisien (150 row)
        foreach (array_chunk($rules, 50) as $chunk) {
            RuleBase::insert($chunk);
        }
    }
}
