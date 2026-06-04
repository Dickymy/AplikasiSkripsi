@extends('layouts.app')

@section('title', 'Analisis SPK')
@section('page-title', 'Analisis SPK Forward Chaining')
@section('page-subtitle', 'Hasilkan rekomendasi dosis pemupukan untuk setiap blok lahan')

@section('content')
<div class="space-y-5">

    {{-- Analisis Semua --}}
    <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-100 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <h3 class="text-sm font-bold text-emerald-800">Analisis Semua Blok Sekaligus</h3>
            <p class="text-xs text-slate-600 mt-0.5">Jalankan Forward Chaining untuk semua blok yang sudah memiliki data kriteria</p>
        </div>
        <form method="POST" action="{{ route('spk.analisis-semua') }}" onsubmit="return confirm('Jalankan analisis SPK untuk semua blok? Data rekomendasi lama akan tetap tersimpan sebagai riwayat.')">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-md hover:shadow-emerald-600/10 hover:-translate-y-0.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Analisis Semua Blok
            </button>
        </form>
    </div>

    {{-- Table per blok --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Status Analisis Per Blok Lahan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Blok Lahan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Luas / SPH</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kriteria</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Hasil Terakhir</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($blokLahans as $blok)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">{{ $blok->nama_blok }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">Total pohon: {{ number_format($blok->sph * $blok->luas_ha) }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 text-xs">
                            <div class="font-medium text-slate-800">{{ number_format($blok->luas_ha, 2) }} Ha</div>
                            <div class="text-slate-400">{{ number_format($blok->sph) }} ph/Ha</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($blok->kriteriaLahan)
                                <div class="space-y-1">
                                    <div class="text-xs font-medium text-slate-700">{{ now()->year - $blok->kriteriaLahan->tahun_tanam }} tahun • {{ $blok->kriteriaLahan->kategori_umur }}</div>
                                    <div class="text-xs text-slate-400">{{ $blok->kriteriaLahan->jenis_tanah }} • {{ $blok->kriteriaLahan->topografi }}</div>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded">
                                    ⚠ Belum ada kriteria
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($blok->rekomendasiTerbaru)
                                <div class="text-xs space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Urea: <span class="text-amber-800 font-bold bg-amber-50 border border-amber-200/50 px-1 py-0.2 rounded">{{ $blok->rekomendasiTerbaru->dosis_urea }} kg/pk</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                                        KCl: <span class="text-cyan-800 font-bold bg-cyan-50 border border-cyan-100 px-1 py-0.2 rounded">{{ $blok->rekomendasiTerbaru->dosis_kcl }} kg/pk</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1 pl-3">{{ $blok->rekomendasiTerbaru->tanggal_analisis->format('d/m/Y') }}</div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-normal">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php $status = $blok->rekomendasiTerbaru?->status_akhir ?? 'Belum Dianalisis'; @endphp
                            @php $sc = match($status) {
                                'Segera Pupuk' => 'bg-rose-50 text-rose-700 border border-rose-100',
                                'Pemupukan Normal' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                'Tunda Pemupukan' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                default => 'bg-slate-100 text-slate-600 border border-slate-200'
                            }; @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $sc }}">{{ $status }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($blok->kriteriaLahan)
                                <form method="POST" action="{{ route('spk.analisis', $blok) }}">
                                    @csrf
                                    <button type="submit" title="Jalankan Analisis"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Analisis
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('kriteria-lahan.create') }}"
                                   class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 text-xs font-medium rounded-lg transition-colors">
                                    Input Kriteria
                                </a>
                                @endif
                                @if($blok->rekomendasiTerbaru)
                                <a href="{{ route('spk.detail', $blok) }}" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200 transition-all" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            Belum ada blok lahan. <a href="{{ route('blok-lahan.create') }}" class="text-emerald-600 font-semibold hover:text-emerald-700 hover:underline">Tambah blok lahan</a> terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
