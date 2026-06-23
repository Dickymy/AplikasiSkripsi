@extends('layouts.app')

@section('title', 'Detail Analisis — ' . $blokLahan->nama_blok)
@section('page-title', 'Detail Analisis')
@section('page-subtitle', $blokLahan->nama_blok . ' · ' . $blokLahan->nama_pemilik)

@section('content')

<div class="mb-4">
    <a href="{{ route('rbs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 1: Ringkasan Blok (compact) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-5">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-center">
        <div>
            <p class="text-[10px] text-slate-400 uppercase font-semibold">Luas</p>
            <p class="text-sm font-bold text-slate-800">{{ $blokLahan->luas_ha }} Ha</p>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 uppercase font-semibold">SPH</p>
            <p class="text-sm font-bold text-slate-800">{{ $blokLahan->sph }} pokok/Ha</p>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 uppercase font-semibold">Umur</p>
            <p class="text-sm font-bold text-slate-800">{{ $blokLahan->umur_tanaman ?? '—' }} tahun</p>
            @if($blokLahan->kategori_umur)
            <p class="text-[9px] text-emerald-600 font-medium">{{ $blokLahan->kategori_umur }}</p>
            @endif
        </div>
        <div>
            <p class="text-[10px] text-slate-400 uppercase font-semibold">Jenis Tanah</p>
            <p class="text-xs font-bold text-slate-800 leading-tight">{{ $blokLahan->jenis_tanah ?? '—' }}</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 2: Hasil Analisis RBS (komponen utama) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@include('rbs.partials._hasil_rbs', ['blokLahan' => $blokLahan])

@if($rbs = $blokLahan->rekomendasiRbsTerbaru)

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 3: Kebutuhan Pupuk (angka besar, mudah dipahami) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($rbs->total_urea || $rbs->total_kcl)
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mt-5">
    <h3 class="text-sm font-bold text-slate-800 mb-3">🧮 Total Kebutuhan Pupuk</h3>
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
            <p class="text-[10px] text-amber-600 uppercase font-semibold mb-1">Urea</p>
            <p class="text-xl sm:text-2xl font-extrabold text-amber-800">{{ $rbs->total_urea ? number_format($rbs->total_urea, 0) : '—' }}</p>
            <p class="text-xs text-amber-600">kg ({{ $rbs->karung_urea }} karung)</p>
            @if($rbs->dosis_urea)
            <p class="text-[9px] text-amber-500 mt-1">{{ $rbs->dosis_urea }} kg/pokok</p>
            @endif
        </div>
        <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-3 text-center">
            <p class="text-[10px] text-cyan-600 uppercase font-semibold mb-1">KCl</p>
            <p class="text-xl sm:text-2xl font-extrabold text-cyan-800">{{ $rbs->total_kcl ? number_format($rbs->total_kcl, 0) : '—' }}</p>
            <p class="text-xs text-cyan-600">kg ({{ $rbs->karung_kcl }} karung)</p>
            @if($rbs->dosis_kcl)
            <p class="text-[9px] text-cyan-500 mt-1">{{ $rbs->dosis_kcl }} kg/pokok</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 4: Info Tambahan (Validitas + Confidence) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mt-5">
    <h3 class="text-sm font-bold text-slate-800 mb-3">📊 Tingkat Keyakinan Rekomendasi</h3>
    <div class="flex flex-wrap items-center gap-2 mb-3">
        {{-- Confidence --}}
        @php
            $confColor = match($rbs->confidence_label) {
                'Tinggi' => 'bg-green-100 text-green-800 border-green-200',
                'Sedang' => 'bg-blue-100 text-blue-800 border-blue-200',
                default  => 'bg-amber-100 text-amber-800 border-amber-200',
            };
            $validitasColor = match($rbs->validitas_rekomendasi) {
                'Cukup Kuat'    => 'bg-blue-100 text-blue-800 border-blue-200',
                'Terverifikasi' => 'bg-green-100 text-green-800 border-green-200',
                default         => 'bg-amber-100 text-amber-800 border-amber-200',
            };
        @endphp
        <span class="inline-flex items-center gap-1 px-2.5 py-1 border rounded-full text-xs font-semibold {{ $confColor }}">
            Keyakinan: {{ $rbs->confidence_label }} ({{ $rbs->confidence_score }}%)
        </span>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 border rounded-full text-xs font-semibold {{ $validitasColor }}">
            {{ $rbs->validitas_rekomendasi }}
        </span>
        @if($rbs->data_cukup)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 border rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border-emerald-200">✓ Data Cukup</span>
        @else
        <span class="inline-flex items-center gap-1 px-2.5 py-1 border rounded-full text-xs font-semibold bg-red-50 text-red-700 border-red-200">⚠️ Data Belum Lengkap</span>
        @endif
    </div>
    @if($rbs->catatan_confidence)
    <p class="text-xs text-slate-500 italic">{{ $rbs->catatan_confidence }}</p>
    @endif
    @if(!$rbs->data_cukup && $rbs->notifikasi_data)
    <div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 text-xs text-amber-800">
        {{ $rbs->notifikasi_data }}
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 5: Jadwal Pemupukan --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($rbs->jadwal_pemupukan && count($rbs->jadwal_pemupukan) > 0)
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5">
    <div class="px-4 sm:px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">📅 Jadwal Pemupukan</h3>
        <p class="text-xs text-slate-400 mt-0.5">Pembagian dosis per tahap aplikasi</p>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach($rbs->jadwal_pemupukan as $jadwal)
        <div class="px-4 sm:px-5 py-3">
            <div class="flex items-center justify-between gap-2 mb-1.5">
                <p class="text-xs font-bold text-slate-800">{{ $jadwal['nama_tahap'] }}</p>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">{{ $jadwal['estimasi_waktu'] }}</span>
            </div>
            <div class="flex gap-4 text-xs mb-1">
                <span class="text-amber-700 font-semibold">Urea: {{ number_format($jadwal['urea_kg'], 1) }} kg</span>
                <span class="text-cyan-700 font-semibold">KCl: {{ number_format($jadwal['kcl_kg'], 1) }} kg</span>
            </div>
            <p class="text-[10px] text-slate-500 italic">{{ $jadwal['catatan'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 6: Kondisi Observasi (collapsible) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($kondisi = $blokLahan->kondisiTerbaru)
<details class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5 group">
    <summary class="px-4 sm:px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-slate-800">🔍 Data Kondisi Observasi</h3>
            <span class="text-xs text-slate-400">{{ $kondisi->tanggal_observasi->format('d M Y') }}</span>
        </div>
        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="px-4 sm:px-5 pb-4 border-t border-slate-100 pt-3">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
            @if($kondisi->warna_daun)
            <div><span class="text-slate-400 block">Warna Daun</span><span class="font-semibold text-slate-800">{{ $kondisi->warna_daun }}</span></div>
            @endif
            @if($kondisi->ph_tanah)
            <div><span class="text-slate-400 block">pH Tanah</span><span class="font-semibold text-slate-800">{{ $kondisi->ph_tanah }} ({{ $kondisi->label_ph }})</span></div>
            @endif
            @if($kondisi->kelembaban_tanah)
            <div><span class="text-slate-400 block">Kelembaban</span><span class="font-semibold text-slate-800">{{ $kondisi->kelembaban_tanah }}</span></div>
            @endif
            @if($kondisi->musim_saat_ini)
            <div><span class="text-slate-400 block">Musim</span><span class="font-semibold text-slate-800">{{ $kondisi->musim_saat_ini }}</span></div>
            @endif
            @if($kondisi->curah_hujan_kategori)
            <div><span class="text-slate-400 block">Curah Hujan</span><span class="font-semibold text-slate-800">{{ $kondisi->curah_hujan_kategori }}</span></div>
            @endif
            @if($kondisi->kondisi_drainase)
            <div><span class="text-slate-400 block">Drainase</span><span class="font-semibold text-slate-800">{{ $kondisi->kondisi_drainase }}</span></div>
            @endif
            @if($kondisi->kondisi_pelepah)
            <div><span class="text-slate-400 block">Pelepah</span><span class="font-semibold text-slate-800">{{ $kondisi->kondisi_pelepah }}</span></div>
            @endif
            @if($kondisi->kondisi_tandan)
            <div><span class="text-slate-400 block">Tandan</span><span class="font-semibold text-slate-800">{{ $kondisi->kondisi_tandan }}</span></div>
            @endif
            @if($kondisi->ada_serangan_hama)
            <div><span class="text-slate-400 block">Hama</span><span class="font-semibold text-red-600">🐛 Ada</span></div>
            @endif
            @if($kondisi->ada_gulma_dominan)
            <div><span class="text-slate-400 block">Gulma</span><span class="font-semibold text-amber-600">🌿 Ada</span></div>
            @endif
        </div>
        @if(!empty($kondisi->gejala_defisiensi))
        <div class="mt-3 pt-3 border-t border-slate-100">
            <span class="text-slate-400 text-xs block mb-1">Dugaan Defisiensi:</span>
            <div class="flex flex-wrap gap-1">
                @foreach($kondisi->gejala_defisiensi as $def)
                <span class="px-1.5 py-0.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded font-bold">{{ $def }}</span>
                @endforeach
            </div>
        </div>
        @endif
        <div class="mt-3 pt-3 border-t border-slate-100 flex gap-3">
            <a href="{{ route('kondisi-lahan.edit', $kondisi) }}" class="text-xs text-blue-600 hover:underline font-medium">Edit kondisi →</a>
            <a href="{{ route('kondisi-lahan.create', ['blok_lahan_id' => $blokLahan->id]) }}" class="text-xs text-emerald-600 hover:underline font-medium">+ Observasi baru</a>
        </div>
    </div>
</details>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 7: Detail Rules Terpicu (collapsible) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($rbs->jumlah_rule_terpicu > 0)
<details class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5 group">
    <summary class="px-4 sm:px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-slate-800">⚙️ Rules yang Terpicu</h3>
            <span class="text-xs text-slate-400">({{ $rbs->jumlah_rule_terpicu }} aturan)</span>
        </div>
        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="border-t border-slate-100 divide-y divide-slate-50">
        @foreach($rbs->rules_terpicu as $i => $rule)
        @php
            $ruleColor = match($rule['status']) {
                'Darurat' => 'bg-red-50 text-red-700',
                'Segera'  => 'bg-orange-50 text-orange-700',
                'Normal'  => 'bg-emerald-50 text-emerald-700',
                default   => 'bg-slate-100 text-slate-600',
            };
        @endphp
        <div class="px-4 sm:px-5 py-3 flex items-start gap-3">
            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold flex items-center justify-center mt-0.5">{{ $i + 1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-slate-800">{{ $rule['indikasi'] }}</p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $ruleColor }}">{{ \App\Models\RekomendasiRbs::labelStatus($rule['status']) }}</span>
                    <span class="text-[10px] text-slate-400">Pupuk: {{ $rule['pupuk'] }}</span>
                    <span class="text-[10px] text-slate-400">Prioritas: {{ $rule['prioritas'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</details>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 8: Aksi --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="flex items-center gap-3 flex-wrap mt-5">
    @if($blokLahan->kondisiTerbaru)
    <form action="{{ route('rbs.analisis', $blokLahan) }}" method="POST">
        @csrf
        <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Jalankan Ulang Analisis
        </button>
    </form>
    @endif
    <a href="{{ route('laporan.show', $rbs) }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
        📄 Lihat Laporan
    </a>
    <a href="{{ route('laporan.pdf', $rbs) }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-red-200 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50 transition-colors">
        📥 Download PDF
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SECTION 9: Histori (hanya tampil jika ada perubahan nyata) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(isset($historiRekomendasi) && $historiRekomendasi->count() > 0)
<details class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5 group">
    <summary class="px-4 sm:px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-slate-800">📜 Histori Analisis Sebelumnya</h3>
            <span class="text-xs text-slate-400">({{ $historiRekomendasi->count() }})</span>
        </div>
        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="border-t border-slate-100 divide-y divide-slate-50">
        @foreach($historiRekomendasi as $hist)
        @php
            $hColor = match($hist->status_kebutuhan_dominan) {
                'Darurat' => 'bg-red-50 text-red-700',
                'Segera'  => 'bg-orange-50 text-orange-700',
                'Normal'  => 'bg-emerald-50 text-emerald-700',
                default   => 'bg-slate-100 text-slate-600',
            };
        @endphp
        <div class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-xs text-slate-400 font-mono flex-shrink-0">#{{ $hist->nomor_analisis }}</span>
                <div class="min-w-0">
                    <p class="text-xs text-slate-700">{{ $hist->tanggal_analisis->format('d/m/Y') }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $hColor }}">{{ \App\Models\RekomendasiRbs::labelStatus($hist->status_kebutuhan_dominan) }}</span>
                        <span class="text-[10px] text-slate-400">{{ $hist->jumlah_rule_terpicu }} rule · {{ $hist->confidence_score ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('laporan.show', $hist) }}" class="text-[10px] text-blue-600 hover:underline font-medium flex-shrink-0">Detail</a>
        </div>
        @endforeach
    </div>
</details>
@endif

@endif {{-- end if $rbs --}}

@endsection
