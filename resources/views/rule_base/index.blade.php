@extends('layouts.app')

@section('title', 'Rule Base RBS')
@section('page-title', 'Rule Base')
@section('page-subtitle', 'Aturan Rule-Based System untuk analisis pemupukan')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-slate-500"><span class="font-bold text-slate-800">{{ $rules->count() }}</span> rule terdaftar · <span class="text-emerald-600 font-semibold">{{ $rules->where('aktif', true)->count() }}</span> aktif</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('rule-base.info') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-medium rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Info
            </a>
            <a href="{{ route('rule-base.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Rule
            </a>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold text-slate-400 uppercase w-8">#</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold text-slate-400 uppercase">Kondisi (IF)</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold text-slate-400 uppercase">Masalah / Pupuk (THEN)</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold text-slate-400 uppercase w-20">Status</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold text-slate-400 uppercase w-12">P</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold text-slate-400 uppercase w-16">Aktif</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold text-slate-400 uppercase w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rules as $i => $rule)
                    <tr class="hover:bg-slate-50/50 {{ !$rule->aktif ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @if($rule->kondisi_warna_daun)
                                <span class="px-1.5 py-0.5 rounded bg-green-50 border border-green-200 text-green-700 text-[10px]">🍃 {{ $rule->kondisi_warna_daun }}</span>
                                @endif
                                @if($rule->kondisi_ph_min || $rule->kondisi_ph_max)
                                <span class="px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-700 text-[10px]">pH {{ $rule->kondisi_ph_min ?? '?' }}–{{ $rule->kondisi_ph_max ?? '?' }}</span>
                                @endif
                                @if($rule->kondisi_kelembaban)
                                <span class="px-1.5 py-0.5 rounded bg-sky-50 border border-sky-200 text-sky-700 text-[10px]">💧 {{ $rule->kondisi_kelembaban }}</span>
                                @endif
                                @if($rule->kondisi_musim)
                                <span class="px-1.5 py-0.5 rounded bg-orange-50 border border-orange-200 text-orange-700 text-[10px]">🌤 {{ \Illuminate\Support\Str::limit($rule->kondisi_musim, 10) }}</span>
                                @endif
                                @if($rule->kondisi_drainase)
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 border border-blue-200 text-blue-700 text-[10px]">🚰 {{ \Illuminate\Support\Str::limit($rule->kondisi_drainase, 10) }}</span>
                                @endif
                                @if($rule->kondisi_defisiensi)
                                <span class="px-1.5 py-0.5 rounded bg-red-50 border border-red-200 text-red-700 text-[10px] font-bold">{{ $rule->kondisi_defisiensi }}</span>
                                @endif
                                @if($rule->kondisi_kategori_umur)
                                <span class="px-1.5 py-0.5 rounded bg-purple-50 border border-purple-200 text-purple-700 text-[10px]">🌱 {{ \Illuminate\Support\Str::limit($rule->kondisi_kategori_umur, 12) }}</span>
                                @endif
                                @if($rule->kondisi_pelepah)
                                <span class="px-1.5 py-0.5 rounded bg-slate-50 border border-slate-200 text-slate-600 text-[10px]">🌿 Pelepah</span>
                                @endif
                                @if($rule->kondisi_tandan)
                                <span class="px-1.5 py-0.5 rounded bg-slate-50 border border-slate-200 text-slate-600 text-[10px]">🌰 Tandan</span>
                                @endif
                                @if($rule->ada_serangan_hama === true)
                                <span class="px-1.5 py-0.5 rounded bg-red-50 border border-red-200 text-red-700 text-[10px]">🐛 Hama</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs font-semibold text-slate-800 leading-tight">{{ \Illuminate\Support\Str::limit($rule->indikasi_masalah, 40) }}</p>
                            <p class="text-[10px] text-emerald-600 mt-0.5">{{ $rule->jenis_pupuk_utama }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @include('components.status-badge', ['status' => $rule->status_kebutuhan, 'size' => 'sm'])
                        </td>
                        <td class="px-4 py-3 text-center text-xs font-bold text-slate-600">{{ $rule->prioritas }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($rule->aktif)
                            <span class="inline-flex w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 items-center justify-center text-[10px]">✓</span>
                            @else
                            <span class="inline-flex w-5 h-5 rounded-full bg-slate-100 text-slate-400 items-center justify-center text-[10px]">✗</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                <a href="{{ route('rule-base.edit', $rule) }}" class="p-1 rounded-md bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition-all" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('rule-base.destroy', $rule) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.closest('form'), '{{ \Illuminate\Support\Str::limit($rule->indikasi_masalah, 20) }}')" class="p-1 rounded-md bg-slate-50 border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada rule. <a href="{{ route('rule-base.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="sm:hidden space-y-2">
        @forelse($rules as $i => $rule)
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm {{ !$rule->aktif ? 'opacity-50' : '' }}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $rule->indikasi_masalah }}</p>
                    <p class="text-[10px] text-emerald-600 mt-0.5">{{ $rule->jenis_pupuk_utama }}</p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    @include('components.status-badge', ['status' => $rule->status_kebutuhan, 'size' => 'sm'])
                </div>
            </div>
            <div class="flex flex-wrap gap-1 mb-2">
                @if($rule->kondisi_warna_daun)
                <span class="px-1.5 py-0.5 rounded bg-green-50 border border-green-200 text-green-700 text-[9px]">🍃 {{ $rule->kondisi_warna_daun }}</span>
                @endif
                @if($rule->kondisi_defisiensi)
                <span class="px-1.5 py-0.5 rounded bg-red-50 border border-red-200 text-red-700 text-[9px] font-bold">{{ $rule->kondisi_defisiensi }}</span>
                @endif
                @if($rule->kondisi_ph_min || $rule->kondisi_ph_max)
                <span class="px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-700 text-[9px]">pH {{ $rule->kondisi_ph_min ?? '?' }}–{{ $rule->kondisi_ph_max ?? '?' }}</span>
                @endif
                @if($rule->kondisi_musim)
                <span class="px-1.5 py-0.5 rounded bg-orange-50 border border-orange-200 text-orange-700 text-[9px]">{{ \Illuminate\Support\Str::limit($rule->kondisi_musim, 12) }}</span>
                @endif
                @if($rule->kondisi_kategori_umur)
                <span class="px-1.5 py-0.5 rounded bg-purple-50 border border-purple-200 text-purple-700 text-[9px]">{{ \Illuminate\Support\Str::limit($rule->kondisi_kategori_umur, 12) }}</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-[10px] text-slate-500">
                    <span>Prioritas: <strong>{{ $rule->prioritas }}</strong></span>
                    <span>{{ $rule->aktif ? '✅ Aktif' : '❌ Nonaktif' }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('rule-base.edit', $rule) }}" class="px-2 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-[9px] font-medium rounded-md">Edit</a>
                    <form action="{{ route('rule-base.destroy', $rule) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.closest('form'), '{{ \Illuminate\Support\Str::limit($rule->indikasi_masalah, 15) }}')" class="px-2 py-1 bg-red-50 border border-red-200 text-red-700 text-[9px] font-medium rounded-md">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <p class="text-slate-400 text-sm mb-2">Belum ada rule.</p>
            <a href="{{ route('rule-base.create') }}" class="text-emerald-600 text-sm font-semibold hover:underline">Tambah rule →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
