<?php

namespace App\Http\Controllers;

use App\Models\RealisasiPemupukan;
use App\Models\RekomendasiRbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealisasiPemupukanController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rekomendasi_rbs_id'    => ['required', 'exists:rekomendasi_rbs,id'],
            'tanggal_realisasi'     => ['required', 'date'],
            'jumlah_urea_realisasi' => ['required', 'numeric', 'min:0'],
            'jumlah_kcl_realisasi'  => ['required', 'numeric', 'min:0'],
            'catatan_pelaksana'     => ['nullable', 'string', 'max:1000'],
        ], [
            'tanggal_realisasi.required'     => 'Tanggal realisasi wajib diisi.',
            'jumlah_urea_realisasi.required' => 'Jumlah Urea wajib diisi.',
            'jumlah_kcl_realisasi.required'  => 'Jumlah KCl wajib diisi.',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();

        // Update or create (1 realisasi per rekomendasi)
        RealisasiPemupukan::updateOrCreate(
            ['rekomendasi_rbs_id' => $validated['rekomendasi_rbs_id']],
            $validated
        );

        return redirect()->back()->with('success', 'Realisasi pemupukan berhasil dicatat.');
    }

    public function destroy(RealisasiPemupukan $realisasiPemupukan)
    {
        $realisasiPemupukan->delete();
        return redirect()->back()->with('success', 'Data realisasi berhasil dihapus.');
    }
}
