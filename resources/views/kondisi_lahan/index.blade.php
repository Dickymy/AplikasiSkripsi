@extends('layouts.app')

@section('title', 'Data Kondisi Lahan')
@section('page-title', 'Kondisi Lahan')
@section('page-subtitle', 'Kondisi terbaru setiap blok lahan')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-slate-500"><span class="font-bold text-slate-800">{{ $grouped->sum(fn($g) => $g['bloks']->count()) }}</span> blok memiliki data kondisi</p>
        <a href="{{ route('kondisi-lahan.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Kondisi
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('kondisi-lahan.index') }}" id="kondisi-filter-form" data-no-prevent-double="true" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
        <div class="flex-1 sm:max-w-[220px]">
            @include('components.filter-searchable', [
                'name' => 'anggota_id',
                'placeholder' => 'Cari pemilik...',
                'options' => $anggotas,
                'displayField' => 'nama',
                'selected' => request('anggota_id'),
                'formId' => 'kondisi-filter-form',
            ])
        </div>
        @if(request()->hasAny(['anggota_id']))
            <a href="{{ route('kondisi-lahan.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium px-2 py-1.5">Reset</a>
        @endif
    </form>

    {{-- Grouped by Anggota --}}
    @forelse($grouped as $group)
    @php $anggota = $group['anggota']; $bloks = $group['bloks']; @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Header anggota --}}
        <div class="px-4 sm:px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr($anggota->nama ?? '?', 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">{{ $anggota->nama ?? 'Tidak Diketahui' }}</p>
                <p class="text-[10px] text-slate-500">{{ $bloks->count() }} blok</p>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">Blok</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">Observasi Terakhir</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">Warna Daun</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">pH</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">Musim</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-slate-400 uppercase">Drainase</th>
                        <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-slate-400 uppercase">Status</th>
                        <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($bloks as $blok)
                    @php
                        $kondisi = $blok->kondisiTerbaru;
                        $rbs = $blok->rekomendasiRbsTerbaru;
                        $perluAnalisisUlang = $kondisi && $rbs && $kondisi->updated_at->gt($rbs->updated_at);
                        $belumDianalisis = $kondisi && !$rbs;
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 text-xs">{{ $blok->nama_blok }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600">{{ $kondisi->tanggal_observasi->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600">{{ $kondisi->warna_daun ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600">{{ $kondisi->ph_tanah ? number_format($kondisi->ph_tanah, 1) : '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600">{{ $kondisi->musim_saat_ini ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600">{{ $kondisi->kondisi_drainase ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @php
                                $statusConfig = match($rbs?->status_kebutuhan_dominan) {
                                    'Darurat' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Defisiensi Berat'],
                                    'Segera'  => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'label' => 'Perlu Pupuk'],
                                    'Normal'  => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Sehat'],
                                    'Tunda'   => ['bg' => 'bg-slate-50 text-slate-600 border-slate-200', 'label' => 'Tunda Pupuk'],
                                    default   => ['bg' => 'bg-blue-50 text-blue-600 border-blue-200', 'label' => 'Belum Dianalisis'],
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-semibold {{ $statusConfig['bg'] }} border">
                                {{ $statusConfig['label'] }}
                            </span>
                            @if($perluAnalisisUlang)
                            <span class="block mt-1 text-[8px] text-amber-600 font-semibold" title="Kondisi diperbarui setelah analisis terakhir">⚠️ Perlu Analisis Ulang</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <div class="flex items-center gap-1.5 justify-end">
                                <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blok->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 text-[10px] font-bold rounded-lg transition-all shadow-sm" title="Input Observasi Baru">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Observasi Baru
                                </a>
                                <a href="{{ route('kondisi-lahan.edit', $kondisi) }}" class="p-1 rounded-md bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition-all" title="Edit Observasi Terakhir">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('kondisi-lahan.destroy', $kondisi) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.closest('form'))" class="p-1 rounded-md bg-slate-50 border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="sm:hidden divide-y divide-slate-100">
            @foreach($bloks as $blok)
            @php
                $kondisi = $blok->kondisiTerbaru;
                $rbs = $blok->rekomendasiRbsTerbaru;
                $perluAnalisisUlang = $kondisi && $rbs && $kondisi->updated_at->gt($rbs->updated_at);
                $belumDianalisis = $kondisi && !$rbs;
            @endphp
            <div class="px-4 py-3">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <p class="font-semibold text-slate-800 text-xs">{{ $blok->nama_blok }} <span class="font-normal text-slate-400">· {{ $kondisi->tanggal_observasi->format('d/m/Y') }}</span></p>
                    @php
                        $statusConfig = match($rbs?->status_kebutuhan_dominan) {
                            'Darurat' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Defisiensi Berat'],
                            'Segera'  => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'label' => 'Perlu Pupuk'],
                            'Normal'  => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Sehat'],
                            'Tunda'   => ['bg' => 'bg-slate-50 text-slate-600 border-slate-200', 'label' => 'Tunda Pupuk'],
                            default   => ['bg' => 'bg-blue-50 text-blue-600 border-blue-200', 'label' => 'Belum'],
                        };
                    @endphp
                    <div class="flex flex-col items-end gap-0.5">
                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[8px] font-semibold {{ $statusConfig['bg'] }} border flex-shrink-0">{{ $statusConfig['label'] }}</span>
                        @if($perluAnalisisUlang)
                        <span class="text-[8px] text-amber-600 font-semibold flex-shrink-0">⚠️ Perlu Ulang</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-x-2 text-[10px] text-slate-500">
                        @if($kondisi->warna_daun)<span>🌿 {{ $kondisi->warna_daun }}</span>@endif
                        @if($kondisi->ph_tanah)<span>pH {{ number_format($kondisi->ph_tanah, 1) }}</span>@endif
                        @if($kondisi->musim_saat_ini)<span>{{ $kondisi->musim_saat_ini }}</span>@endif
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blok->id]) }}" class="px-2 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-bold rounded-md" title="Input Observasi Baru">Baru</a>
                        <a href="{{ route('kondisi-lahan.edit', $kondisi) }}" class="px-2 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-[9px] font-medium rounded-md">Edit</a>
                        <form action="{{ route('kondisi-lahan.destroy', $kondisi) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="button" onclick="confirmDelete(this.closest('form'))" class="px-2 py-1 bg-red-50 border border-red-200 text-red-700 text-[9px] font-medium rounded-md">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
        <p class="text-slate-400 text-sm mb-2">Belum ada data kondisi lahan.</p>
        <a href="{{ route('kondisi-lahan.create') }}" class="text-emerald-600 text-sm font-semibold hover:underline">Input kondisi lahan →</a>
    </div>
    @endforelse
</div>
@endsection
