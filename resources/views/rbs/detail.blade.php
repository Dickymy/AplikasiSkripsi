@extends('layouts.app')

@section('title', 'Detail Analisis RBS — ' . $blokLahan->nama_blok)
@section('page-title', 'Detail Analisis RBS')
@section('page-subtitle', $blokLahan->nama_blok . ' · ' . $blokLahan->nama_pemilik)

@section('content')

<div class="mb-5">
    <a href="{{ route('rbs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Analisis RBS
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- KOLOM KIRI: Info Blok + Kondisi --}}
    <div class="space-y-5">

        {{-- Info Blok Lahan --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
                Informasi Blok Lahan
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nama Blok</span>
                    <span class="font-semibold text-slate-800">{{ $blokLahan->nama_blok }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pemilik</span>
                    <span class="text-slate-700">{{ $blokLahan->nama_pemilik }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Luas</span>
                    <span class="text-slate-700">{{ $blokLahan->luas_ha }} Ha</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">SPH</span>
                    <span class="text-slate-700">{{ $blokLahan->sph }} pokok/Ha</span>
                </div>
                @if($blokLahan->tahun_tanam)
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tahun Tanam</span>
                        <span class="text-slate-700">{{ $blokLahan->tahun_tanam }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-slate-500">Umur Tanaman</span>
                        <span class="font-semibold text-slate-800">{{ $blokLahan->umur_tanaman }} tahun</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-slate-500">Kategori</span>
                        <span class="text-emerald-700 font-medium">{{ $blokLahan->kategori_umur }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-slate-500">Jenis Tanah</span>
                        <span class="text-slate-700 text-xs text-right max-w-[120px]">{{ $blokLahan->jenis_tanah }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Kondisi Lahan Terbaru --}}
        @if($kondisi = $blokLahan->kondisiTerbaru)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Kondisi Observasi
                <span class="text-xs text-slate-400 font-normal">{{ $kondisi->tanggal_observasi->format('d M Y') }}</span>
            </h3>
            <div class="space-y-1.5 text-xs">
                @if($kondisi->warna_daun)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Warna Daun</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->warna_daun }}</span>
                </div>
                @endif
                @if($kondisi->ph_tanah)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">pH Tanah</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->ph_tanah }} <span class="text-slate-400">({{ $kondisi->label_ph }})</span></span>
                </div>
                @endif
                @if($kondisi->kelembaban_tanah)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Kelembaban</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->kelembaban_tanah }}</span>
                </div>
                @endif
                @if($kondisi->musim_saat_ini)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Musim</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->musim_saat_ini }}</span>
                </div>
                @endif
                @if($kondisi->kondisi_drainase)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Drainase</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->kondisi_drainase }}</span>
                </div>
                @endif
                @if($kondisi->kondisi_pelepah)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Kondisi Pelepah</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->kondisi_pelepah }}</span>
                </div>
                @endif
                @if($kondisi->kondisi_tandan)
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Kondisi Tandan</span>
                    <span class="font-medium text-slate-800">{{ $kondisi->kondisi_tandan }}</span>
                </div>
                @endif
                @if(!empty($kondisi->gejala_defisiensi))
                <div class="pt-1.5 border-t border-slate-100">
                    <p class="text-slate-500 mb-1">Gejala Defisiensi</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($kondisi->gejala_defisiensi as $def)
                        <span class="px-1.5 py-0.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded font-bold">{{ $def }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="pt-1.5 border-t border-slate-100 flex gap-3">
                    @if($kondisi->ada_serangan_hama)
                    <span class="text-red-600 font-medium">🐛 Ada Hama</span>
                    @endif
                    @if($kondisi->ada_gulma_dominan)
                    <span class="text-amber-600 font-medium">🌿 Ada Gulma</span>
                    @endif
                </div>
                @if($kondisi->catatan_observasi)
                <div class="pt-1.5 border-t border-slate-100">
                    <p class="text-slate-500 mb-1">Catatan</p>
                    <p class="text-slate-700 italic leading-relaxed">{{ $kondisi->catatan_observasi }}</p>
                </div>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <a href="{{ route('kondisi-lahan.edit', $kondisi) }}"
                   class="text-xs text-blue-600 hover:underline font-medium">Edit kondisi →</a>
                <span class="text-slate-200 mx-2">|</span>
                <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blokLahan->id]) }}"
                   class="text-xs text-emerald-600 hover:underline font-medium">+ Observasi baru</a>
            </div>
        </div>
        @else
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
            <p class="text-amber-700 text-sm font-medium">Belum ada data kondisi lahan</p>
            <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blokLahan->id]) }}"
               class="mt-2 inline-flex items-center gap-1.5 text-xs text-amber-700 font-semibold hover:underline">
                + Input kondisi sekarang
            </a>
        </div>
        @endif

    </div>

    {{-- KOLOM KANAN: Hasil RBS --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Komponen Hasil RBS --}}
        @include('rbs.partials._hasil_rbs', ['blokLahan' => $blokLahan])

        {{-- Detail Rules Terpicu --}}
        @if($rbs = $blokLahan->rekomendasiRbsTerbaru)
        @if($rbs->jumlah_rule_terpicu > 0)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-800">Detail Rules yang Terpicu</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $rbs->jumlah_rule_terpicu }} aturan cocok dengan kondisi lahan saat ini</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($rbs->rules_terpicu as $i => $rule)
                @php
                    $ruleStatusConfig = match($rule['status']) {
                        'Darurat' => 'bg-red-100 text-red-800',
                        'Segera'  => 'bg-orange-100 text-orange-800',
                        'Normal'  => 'bg-emerald-100 text-emerald-800',
                        'Tunda'   => 'bg-slate-100 text-slate-700',
                        default   => 'bg-blue-100 text-blue-800',
                    };
                    $ruleStatusLabel = \App\Models\RekomendasiRbs::labelStatus($rule['status']);
                @endphp
                <div class="px-5 py-3.5 flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center mt-0.5">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-medium text-slate-800">{{ $rule['indikasi'] }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $ruleStatusConfig }}">
                                {{ $ruleStatusLabel }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pupuk: <span class="font-medium text-slate-700">{{ $rule['pupuk'] }}</span>
                            <span class="text-slate-300 mx-1">·</span>
                            Prioritas: <span class="font-medium text-slate-700">{{ $rule['prioritas'] }}</span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Action: Re-run + Link kondisi --}}
        <div class="flex items-center gap-3 flex-wrap">
            @if($blokLahan->kondisiTerbaru)
            <form action="{{ route('rbs.analisis', $blokLahan) }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-emerald-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Jalankan Ulang Analisis
                </button>
            </form>
            @endif
            <a href="{{ route('blok-lahan.show', $blokLahan) }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
                Lihat Detail Blok
            </a>
        </div>
        @endif

    </div>
</div>

@endsection
