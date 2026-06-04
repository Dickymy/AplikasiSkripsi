@extends('layouts.app')

@section('title', 'Kriteria Lahan')
@section('page-title', 'Kriteria Lahan')
@section('page-subtitle', 'Data karakteristik agronomis setiap blok lahan')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Total <span class="font-semibold text-slate-900">{{ $kriteriaLahans->count() }}</span> data kriteria</p>
        <a href="{{ route('kriteria-lahan.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kriteria
        </a>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-200 bg-slate-50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Blok Lahan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Tahun Tanam</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Umur</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Kategori Umur</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Jenis Tanah</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Topografi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kriteriaLahans as $i => $k)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $k->blokLahan->nama_blok }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $k->tahun_tanam }}</td>
                        <td class="px-5 py-4 text-emerald-600 font-semibold">{{ $k->umur_tanaman }} tahun</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200/60 text-xs font-medium">{{ $k->kategori_umur }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $k->jenis_tanah }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $k->topografi }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('kriteria-lahan.edit', $k) }}"
                                   class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('kriteria-lahan.destroy', $k) }}" onsubmit="return confirm('Hapus kriteria blok ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">
                        Belum ada kriteria lahan. <a href="{{ route('kriteria-lahan.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
