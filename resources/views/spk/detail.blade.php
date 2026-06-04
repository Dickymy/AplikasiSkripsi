@extends('layouts.app')

@section('title', 'Detail SPK ' . $blokLahan->nama_blok)
@section('page-title', 'Detail Analisis SPK')
@section('page-subtitle', $blokLahan->nama_blok)

@section('content')
<div class="space-y-5">
    <a href="{{ route('spk.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    {{-- Info Blok --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-bold text-emerald-700 mb-4">Data Blok Lahan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Nama Blok</span><span class="text-slate-900 font-bold">{{ $blokLahan->nama_blok }}</span></div>
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Luas</span><span class="text-slate-800 font-medium">{{ number_format($blokLahan->luas_ha, 2) }} Ha</span></div>
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">SPH</span><span class="text-slate-800">{{ number_format($blokLahan->sph) }} pohon/Ha</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total Pohon</span><span class="text-slate-900 font-semibold">{{ number_format($blokLahan->sph * $blokLahan->luas_ha) }} pohon</span></div>
            </div>
        </div>
        @if($blokLahan->kriteriaLahan)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-bold text-emerald-700 mb-4">Kriteria Fakta SPK</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Tahun Tanam</span><span class="text-slate-800">{{ $blokLahan->kriteriaLahan->tahun_tanam }}</span></div>
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Umur Tanaman</span><span class="text-emerald-700 font-bold bg-emerald-50 border border-emerald-100/50 px-2 py-0.5 rounded">{{ $blokLahan->kriteriaLahan->umur_tanaman }} tahun</span></div>
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Kategori Umur</span><span class="text-slate-800">{{ $blokLahan->kriteriaLahan->kategori_umur }}</span></div>
                <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-500">Jenis Tanah</span><span class="text-slate-800">{{ $blokLahan->kriteriaLahan->jenis_tanah }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Topografi</span><span class="text-slate-800">{{ $blokLahan->kriteriaLahan->topografi }}</span></div>
            </div>
        </div>
        @endif
    </div>

    {{-- History --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Analisis SPK</h3>
            <form method="POST" action="{{ route('spk.analisis', $blokLahan) }}">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Analisis Ulang
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Dosis Urea</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Dosis KCl</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total Urea (kg)</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total KCl (kg)</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Karung Urea</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Karung KCl</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($blokLahan->rekomendasiSpks as $r)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3 text-slate-600 text-xs font-medium">{{ $r->tanggal_analisis->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="text-amber-800 font-bold bg-amber-50 border border-amber-200/50 px-2 py-0.5 rounded">{{ $r->dosis_urea }} kg/pk</span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="text-cyan-800 font-bold bg-cyan-50 border border-cyan-100 px-2 py-0.5 rounded">{{ $r->dosis_kcl }} kg/pk</span>
                        </td>
                        <td class="px-5 py-3 text-center text-slate-700 text-xs font-medium">{{ number_format($r->total_urea, 1) }}</td>
                        <td class="px-5 py-3 text-center text-slate-700 text-xs font-medium">{{ number_format($r->total_kcl, 1) }}</td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="font-bold text-amber-800 bg-amber-50/50 border border-amber-100/50 px-1.5 py-0.5 rounded">{{ $r->karung_urea }} karung</span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="font-bold text-cyan-800 bg-cyan-50/50 border border-cyan-100/50 px-1.5 py-0.5 rounded">{{ $r->karung_kcl }} karung</span>
                        </td>
                        <td class="px-5 py-3 text-xs">
                            @php $sc = match($r->status_akhir) { 
                                'Segera Pupuk' => 'bg-rose-50 text-rose-700 border border-rose-100', 
                                'Pemupukan Normal' => 'bg-emerald-50 text-emerald-700 border border-emerald-100', 
                                'Tunda Pemupukan' => 'bg-amber-50 text-amber-700 border border-amber-200/60', 
                                default => 'bg-slate-100 text-slate-600 border border-slate-200' 
                            }; @endphp
                            <span class="inline-flex px-2.5 py-0.5 border rounded-lg font-medium {{ $sc }}">{{ $r->status_akhir }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs font-medium">{{ $r->admin?->nama_lengkap ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-slate-400 text-sm">
                            Belum ada riwayat analisis.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
