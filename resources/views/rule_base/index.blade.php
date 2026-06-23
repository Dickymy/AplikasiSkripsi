@extends('layouts.app')

@section('title', 'Rule Base RBS')
@section('page-title', 'Rule Base')
@section('page-subtitle', 'Aturan diagnosis kondisi tanaman & rekomendasi pemupukan')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-slate-500"><span class="font-bold text-slate-800">{{ $rules->count() }}</span> aturan · <span class="text-emerald-600 font-semibold">{{ $rules->where('aktif', true)->count() }}</span> aktif</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('rule-base.info') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-medium rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Info
            </a>
            <a href="{{ route('rule-base.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Aturan
            </a>
        </div>
    </div>

    {{-- Penjelasan singkat --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700 leading-relaxed">
        💡 Setiap aturan berisi <strong>JIKA</strong> (kondisi yang harus terpenuhi) <strong>MAKA</strong> (rekomendasi yang diberikan). Sistem mencocokkan kondisi lahan dengan aturan-aturan ini untuk menghasilkan rekomendasi pemupukan.
    </div>

    {{-- Rules List — Card Format IF-THEN --}}
    <div class="space-y-3">
        @forelse($rules as $i => $rule)
        @php
            $statusColor = match($rule->status_kebutuhan) {
                'Darurat' => 'border-l-red-500 bg-red-50/30',
                'Segera'  => 'border-l-orange-400 bg-orange-50/30',
                'Normal'  => 'border-l-emerald-400 bg-emerald-50/20',
                'Tunda'   => 'border-l-slate-400 bg-slate-50/50',
                default   => 'border-l-blue-400',
            };
            $statusBadge = match($rule->status_kebutuhan) {
                'Darurat' => 'bg-red-100 text-red-800',
                'Segera'  => 'bg-orange-100 text-orange-800',
                'Normal'  => 'bg-emerald-100 text-emerald-800',
                'Tunda'   => 'bg-slate-100 text-slate-700',
                default   => 'bg-blue-100 text-blue-800',
            };
        @endphp
        <div class="bg-white border border-slate-200 border-l-4 {{ $statusColor }} rounded-xl shadow-sm overflow-hidden {{ !$rule->aktif ? 'opacity-50' : '' }}">
            <div class="p-4">
                {{-- Header: Status + Prioritas + Aksi --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusBadge }}">{{ \App\Models\RekomendasiRbs::labelStatus($rule->status_kebutuhan) }}</span>
                        <span class="text-[10px] text-slate-400">Prioritas {{ $rule->prioritas }}</span>
                        @if(!$rule->aktif)
                        <span class="text-[10px] text-red-500 font-medium">● Nonaktif</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a href="{{ route('rule-base.edit', $rule) }}" class="p-1.5 rounded-md text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition-all" title="Edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form action="{{ route('rule-base.destroy', $rule) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="button" onclick="confirmDelete(this.closest('form'), '{{ \Illuminate\Support\Str::limit($rule->indikasi_masalah, 20) }}')" class="p-1.5 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- IF Section --}}
                <div class="mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">JIKA</p>
                    <p class="text-xs text-slate-700 leading-relaxed">
                        @php $conditions = []; @endphp
                        @if($rule->kondisi_warna_daun) @php $conditions[] = "warna daun <strong>{$rule->kondisi_warna_daun}</strong>"; @endphp @endif
                        @if($rule->kondisi_ph_min || $rule->kondisi_ph_max) @php $conditions[] = "pH tanah <strong>" . ($rule->kondisi_ph_min ?? '?') . " – " . ($rule->kondisi_ph_max ?? '?') . "</strong>"; @endphp @endif
                        @if($rule->kondisi_kelembaban) @php $conditions[] = "kelembaban <strong>{$rule->kondisi_kelembaban}</strong>"; @endphp @endif
                        @if($rule->kondisi_curah_hujan_kategori) @php $conditions[] = "curah hujan <strong>{$rule->kondisi_curah_hujan_kategori}</strong>"; @endphp @endif
                        @if($rule->kondisi_musim) @php $conditions[] = "musim <strong>{$rule->kondisi_musim}</strong>"; @endphp @endif
                        @if($rule->kondisi_drainase) @php $conditions[] = "drainase <strong>{$rule->kondisi_drainase}</strong>"; @endphp @endif
                        @if($rule->kondisi_defisiensi) @php $conditions[] = "ada dugaan defisiensi <strong>{$rule->kondisi_defisiensi}</strong>"; @endphp @endif
                        @if($rule->kondisi_kategori_umur) @php $conditions[] = "umur tanaman <strong>{$rule->kondisi_kategori_umur}</strong>"; @endphp @endif
                        @if($rule->kondisi_pelepah) @php $conditions[] = "pelepah <strong>{$rule->kondisi_pelepah}</strong>"; @endphp @endif
                        @if($rule->kondisi_tandan) @php $conditions[] = "tandan <strong>{$rule->kondisi_tandan}</strong>"; @endphp @endif
                        @if($rule->ada_serangan_hama === true) @php $conditions[] = "<strong>ada serangan hama</strong>"; @endphp @endif
                        @if($rule->ada_gulma_dominan === true) @php $conditions[] = "<strong>ada gulma dominan</strong>"; @endphp @endif
                        {!! implode(' <span class="text-slate-400">DAN</span> ', $conditions) !!}
                    </p>
                </div>

                {{-- THEN Section --}}
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MAKA</p>
                    <p class="text-xs font-semibold text-slate-800 mb-1">{{ $rule->indikasi_masalah }}</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-600">
                        <span>💊 <strong>{{ $rule->jenis_pupuk_utama }}</strong></span>
                        @if($rule->jenis_pupuk_pendukung)
                        <span class="text-slate-400">+ {{ $rule->jenis_pupuk_pendukung }}</span>
                        @endif
                    </div>
                    @if($rule->dosis_anjuran)
                    <p class="text-[10px] text-slate-500 mt-1">Dosis: {{ $rule->dosis_anjuran }}</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <p class="text-slate-400 text-sm mb-2">Belum ada aturan.</p>
            <a href="{{ route('rule-base.create') }}" class="text-emerald-600 text-sm font-semibold hover:underline">Tambah aturan →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
