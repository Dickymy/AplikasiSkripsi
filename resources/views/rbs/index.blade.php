@extends('layouts.app')

@section('title', 'Analisis RBS')
@section('page-title', 'Analisis Rule-Based System')
@section('page-subtitle', 'Diagnostik kondisi tanaman & rekomendasi pemupukan berbasis gejala visual')

@section('content')

{{-- Stats Cards --}}
@php
    $totalBlok     = $bloks->count();
    $sudahAnalisis = $bloks->filter(fn($b) => $b->rekomendasiRbsTerbaru)->count();
    $darurat       = $bloks->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Darurat')->count();
    $segera        = $bloks->filter(fn($b) => $b->rekomendasiRbsTerbaru?->status_kebutuhan_dominan === 'Segera')->count();
    $belumKondisi  = $bloks->filter(fn($b) => !$b->kondisiTerbaru)->count();
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500 mb-1">Total Blok</p>
        <p class="text-2xl font-bold text-slate-900">{{ $totalBlok }}</p>
        <p class="text-xs text-slate-400 mt-1">blok terdaftar</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500 mb-1">Sudah Dianalisis</p>
        <p class="text-2xl font-bold text-blue-600">{{ $sudahAnalisis }}</p>
        <p class="text-xs text-slate-400 mt-1">dari {{ $totalBlok }} blok</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-l-4 border-l-red-500">
        <p class="text-xs text-slate-500 mb-1">Status Darurat</p>
        <p class="text-2xl font-bold text-red-600">{{ $darurat }}</p>
        <p class="text-xs text-slate-400 mt-1">butuh penanganan segera</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-l-4 border-l-orange-400">
        <p class="text-xs text-slate-500 mb-1">Status Segera</p>
        <p class="text-2xl font-bold text-orange-500">{{ $segera }}</p>
        <p class="text-xs text-slate-400 mt-1">perlu tindakan cepat</p>
    </div>
</div>

{{-- Action Bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-3">
        {{-- Filter Anggota & Blok --}}
        <form method="GET" action="{{ route('rbs.index') }}" id="filter-form" class="flex flex-wrap items-center gap-2">
            <select name="anggota_id" onchange="this.form.submit()"
                class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors min-w-[180px]">
                <option value="">Semua Anggota</option>
                @foreach($anggotas as $anggota)
                    <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                @endforeach
            </select>

            @if($blokFilter->isNotEmpty())
            <select name="blok_lahan_id" onchange="this.form.submit()"
                class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors min-w-[160px]">
                <option value="">Semua Blok</option>
                @foreach($blokFilter as $bf)
                    <option value="{{ $bf->id }}" {{ request('blok_lahan_id') == $bf->id ? 'selected' : '' }}>{{ $bf->nama_blok }}</option>
                @endforeach
            </select>
            @endif

            @if(request()->hasAny(['anggota_id', 'blok_lahan_id']))
                <a href="{{ route('rbs.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Reset</a>
            @endif
        </form>

        @if($belumKondisi > 0)
        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg">
            ⚠ {{ $belumKondisi }} blok belum ada kondisi. <a href="{{ route('kondisi-lahan.create') }}" class="font-semibold underline">Input →</a>
        </p>
        @endif
    </div>
    <form action="{{ route('rbs.analisisSemua') }}" method="POST" id="form-analisis-semua">
        @csrf
        <button type="button" onclick="showConfirm('Jalankan analisis RBS untuk semua blok yang memiliki data kondisi?', function(){ document.getElementById('form-analisis-semua').submit(); })"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-emerald-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Analisis Semua Blok
        </button>
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Blok Lahan</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Umur / Kategori</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Kondisi Terbaru</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status RBS</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Rule Terpicu</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($bloks as $blok)
                @php
                    $rbs     = $blok->rekomendasiRbsTerbaru;
                    $kondisi = $blok->kondisiTerbaru;

                    $statusConfig = match($rbs?->status_kebutuhan_dominan) {
                        'Darurat' => ['bg' => 'bg-red-100',    'text' => 'text-red-800',    'label' => 'Darurat'],
                        'Segera'  => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Segera'],
                        'Normal'  => ['bg' => 'bg-emerald-100','text' => 'text-emerald-800','label' => 'Normal'],
                        'Tunda'   => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'label' => 'Tunda'],
                        default   => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'label' => 'Belum Dianalisis'],
                    };
                @endphp
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ $blok->nama_blok }}</p>
                        <p class="text-xs text-slate-400">{{ $blok->nama_pemilik }} · {{ $blok->luas_ha }} Ha</p>
                    </td>
                    <td class="px-4 py-4 hide-mobile">
                        @if($blok->tahun_tanam)
                            <p class="text-slate-700 font-medium">{{ $blok->umur_tanaman }} tahun</p>
                            <p class="text-xs text-slate-400">{{ $blok->kategori_umur }}</p>
                        @else
                            <span class="text-xs text-slate-400">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 hide-mobile">
                        @if($kondisi)
                            <p class="text-slate-700 text-xs">{{ $kondisi->tanggal_observasi->format('d M Y') }}</p>
                            @if($kondisi->warna_daun)
                                <p class="text-xs text-slate-500">{{ $kondisi->warna_daun }}</p>
                            @endif
                            @if($kondisi->ph_tanah)
                                <p class="text-xs text-slate-400">pH: {{ $kondisi->ph_tanah }}</p>
                            @endif
                        @else
                            <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blok->id]) }}"
                               class="text-xs text-amber-600 font-medium hover:underline">
                                + Input kondisi
                            </a>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                        @if($rbs)
                            <p class="text-xs text-slate-400 mt-1">{{ $rbs->tanggal_analisis->format('d M Y') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center hide-mobile">
                        @if($rbs)
                            <span class="text-lg font-bold text-slate-700">{{ $rbs->jumlah_rule_terpicu }}</span>
                            <span class="text-xs text-slate-400 ml-1">rule</span>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @if($kondisi)
                            <form method="POST" action="{{ route('rbs.analisis', $blok) }}">
                                @csrf
                                <button type="submit" title="Jalankan Analisis"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @if($rbs)
                            <a href="{{ route('rbs.detail', $blok) }}" title="Lihat Detail"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <p class="text-slate-400 text-sm">Belum ada data blok lahan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
