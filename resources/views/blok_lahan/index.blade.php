@extends('layouts.app')

@section('title', 'Manajemen Blok Lahan')
@section('page-title', 'Manajemen Blok Lahan')
@section('page-subtitle', 'Data master blok lahan kelapa sawit')

@section('content')
<div class="space-y-5">

    {{-- Header action --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Total <span class="font-semibold text-slate-900">{{ $blokLahans->count() }}</span> blok terdaftar</p>
        <a href="{{ route('blok-lahan.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Blok Lahan
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Blok</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pemilik Lahan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Luas (Ha)</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">SPH</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tonase Panen</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tonase per Hektar</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kriteria</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status SPK</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($blokLahans as $i => $blok)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $blok->nama_blok }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $blok->nama_pemilik }}
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format($blok->luas_ha, 2) }} Ha</td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format($blok->sph) }} /Ha</td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $blok->total_tonase_panen !== null ? number_format($blok->total_tonase_panen, 2).' ton' : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($blok->yield_per_hektar !== null)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-semibold">
                                    {{ number_format($blok->yield_per_hektar, 2) }} ton/ha
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($blok->kriteriaLahan)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Ada ({{ now()->year - $blok->kriteriaLahan->tahun_tanam }} tahun)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 border border-slate-200/60 text-xs">Belum Ada</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php $status = $blok->rekomendasiTerbaru?->status_akhir ?? 'Belum Dianalisis'; @endphp
                            @php $statusClass = match($status) {
                                'Segera Pupuk' => 'bg-rose-50 text-rose-700 border border-rose-100',
                                'Pemupukan Normal' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                'Tunda Pemupukan' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                default => 'bg-slate-100 text-slate-500 border border-slate-200/60'
                            }; @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('blok-lahan.show', $blok) }}" title="Detail"
                                   class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('blok-lahan.edit', $blok) }}" title="Edit"
                                   class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('blok-lahan.destroy', $blok) }}" onsubmit="return confirm('Yakin ingin menghapus blok \'{{ $blok->nama_blok }}\'? Semua data terkait akan ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus"
                                            class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <p>Belum ada blok lahan. <a href="{{ route('blok-lahan.create') }}" class="text-emerald-600 hover:underline font-semibold">Tambah sekarang</a></p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
