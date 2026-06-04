@extends('layouts.app')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan SPK')
@section('page-subtitle', $rekomendasiSpk->blokLahan->nama_blok . ' — ' . $rekomendasiSpk->tanggal_analisis->format('d F Y'))

@section('content')
<div class="space-y-5 max-w-3xl">
    <a href="{{ route('laporan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Laporan
    </a>

    {{-- Status Banner --}}
    @php $sc = match($rekomendasiSpk->status_akhir) {
        'Segera Pupuk' => 'from-red-50 to-rose-50/30 border-red-200 text-red-950 shadow-red-50/50',
        'Pemupukan Normal' => 'from-emerald-50 to-green-50/30 border-emerald-200 text-emerald-950 shadow-emerald-50/50',
        'Tunda Pemupukan' => 'from-amber-50 to-yellow-50/30 border-amber-200 text-amber-950 shadow-amber-50/50',
        default => 'from-slate-50 to-slate-100/50 border-slate-200 text-slate-900'
    }; @endphp
    <div class="bg-gradient-to-r {{ $sc }} border rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
            <p class="text-xs text-slate-500 font-semibold tracking-wider uppercase">Rekomendasi SPK Forward Chaining</p>
            <p class="text-xl font-extrabold mt-0.5">{{ $rekomendasiSpk->status_akhir }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">{{ $rekomendasiSpk->blokLahan->nama_blok }} • {{ $rekomendasiSpk->tanggal_analisis->format('d F Y') }} • Oleh: <span class="font-semibold text-slate-700">{{ $rekomendasiSpk->admin->nama_lengkap }}</span></p>
        </div>
    </div>

    {{-- Detail Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-50 pb-2">Info Lahan</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Luas</span><span class="text-slate-800 font-bold">{{ number_format($rekomendasiSpk->blokLahan->luas_ha, 2) }} Ha</span></div>
                <div class="flex justify-between"><span class="text-slate-500">SPH</span><span class="text-slate-800 font-medium">{{ number_format($rekomendasiSpk->blokLahan->sph) }} ph/Ha</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total Pohon</span><span class="text-slate-900 font-bold">{{ number_format($rekomendasiSpk->blokLahan->sph * $rekomendasiSpk->blokLahan->luas_ha) }}</span></div>
            </div>
        </div>

        @if($rekomendasiSpk->blokLahan->kriteriaLahan)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-50 pb-2">Fakta SPK</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Umur Sawit</span><span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.2 rounded border border-emerald-100/50">{{ $rekomendasiSpk->blokLahan->kriteriaLahan->umur_tanaman }} tahun</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kategori</span><span class="text-slate-800 font-semibold">{{ $rekomendasiSpk->blokLahan->kriteriaLahan->kategori_umur }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Jenis Tanah</span><span class="text-slate-800 font-medium">{{ $rekomendasiSpk->blokLahan->kriteriaLahan->jenis_tanah }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Topografi</span><span class="text-slate-800 font-medium">{{ $rekomendasiSpk->blokLahan->kriteriaLahan->topografi }}</span></div>
            </div>
        </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-50 pb-2">Dosis Per Pokok</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500 font-medium">Dosis Urea</p>
                    <p class="text-lg font-extrabold text-amber-700 bg-amber-50/60 border border-amber-100/60 px-2 py-0.5 rounded">{{ $rekomendasiSpk->dosis_urea }} <span class="text-[10px] font-semibold text-slate-500">kg</span></p>
                </div>
                <div class="flex items-center justify-between border-t border-slate-50 pt-2">
                    <p class="text-xs text-slate-500 font-medium">Dosis KCl</p>
                    <p class="text-lg font-extrabold text-cyan-700 bg-cyan-50/60 border border-cyan-100/60 px-2 py-0.5 rounded">{{ $rekomendasiSpk->dosis_kcl }} <span class="text-[10px] font-semibold text-slate-500">kg</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Logistik --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <h3 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Kebutuhan Logistik Pupuk
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-amber-50/60 border border-amber-100/80 rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-amber-800 font-semibold mb-1">Total Urea</p>
                <p class="text-2xl font-extrabold text-amber-700">{{ number_format($rekomendasiSpk->total_urea, 1) }}</p>
                <p class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">kilogram (kg)</p>
            </div>
            <div class="bg-amber-50/60 border border-amber-100/80 rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-amber-800 font-semibold mb-1">Urea @50kg</p>
                <p class="text-2xl font-extrabold text-amber-800">{{ $rekomendasiSpk->karung_urea }}</p>
                <p class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">karung</p>
            </div>
            <div class="bg-cyan-50/60 border border-cyan-100/80 rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-cyan-800 font-semibold mb-1">Total KCl</p>
                <p class="text-2xl font-extrabold text-cyan-700">{{ number_format($rekomendasiSpk->total_kcl, 1) }}</p>
                <p class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">kilogram (kg)</p>
            </div>
            <div class="bg-cyan-50/60 border border-cyan-100/80 rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-cyan-800 font-semibold mb-1">KCl @50kg</p>
                <p class="text-2xl font-extrabold text-cyan-700">{{ $rekomendasiSpk->karung_kcl }}</p>
                <p class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">karung</p>
            </div>
        </div>
        <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600">
            <strong class="text-slate-800 font-bold block mb-1">Formula & Rumus Perhitungan:</strong>
            <div class="flex flex-col gap-1 text-[11px] font-medium text-slate-500">
                <div>• Total Dosis = Dosis/pohon × SPH (Stand Per Hectare) × Luas Lahan (Ha)</div>
                <div class="text-slate-700">• Urea: {{ $rekomendasiSpk->dosis_urea }} kg × {{ $rekomendasiSpk->blokLahan->sph }} SPH × {{ $rekomendasiSpk->blokLahan->luas_ha }} Ha = <span class="font-bold text-amber-700">{{ number_format($rekomendasiSpk->total_urea, 2) }} kg</span></div>
                <div class="text-slate-700">• KCl: {{ $rekomendasiSpk->dosis_kcl }} kg × {{ $rekomendasiSpk->blokLahan->sph }} SPH × {{ $rekomendasiSpk->blokLahan->luas_ha }} Ha = <span class="font-bold text-cyan-700">{{ number_format($rekomendasiSpk->total_kcl, 2) }} kg</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
