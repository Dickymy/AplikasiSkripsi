@extends('layouts.app')

@section('title', 'Laporan Pemupukan')
@section('page-title', 'Laporan & Rekap Pemupukan')
@section('page-subtitle', 'Rekapitulasi rekomendasi dan kebutuhan logistik pupuk')

@push('styles')
<style>
    @media print {
        aside, header, .map-legend, button[type="button"], a[href], form button[type="submit"] { display: none !important; }
        .lg\:ml-64 { margin-left: 0 !important; }
        main { padding: 0 !important; }
        .shadow-sm, .shadow-lg { box-shadow: none !important; }
        .rounded-2xl, .rounded-xl { border-radius: 4px !important; }
        body { font-size: 11px !important; }
        table th, table td { padding: 4px 8px !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Data</p>
            <p class="text-2xl font-extrabold text-slate-900">{{ $rekap->count() }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">rekomendasi</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Kebutuhan Urea</p>
            <p class="text-2xl font-extrabold text-amber-700">{{ number_format($totalUrea, 1) }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">kilogram (kg)</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Kebutuhan KCl</p>
            <p class="text-2xl font-extrabold text-cyan-700">{{ number_format($totalKcl, 1) }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">kilogram (kg)</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 border-l-4 border-l-emerald-600 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Karung (@50kg)</p>
            <p class="text-xs font-bold text-slate-800">Urea: <span class="text-amber-700 font-extrabold">{{ $karungUrea }}</span> karung</p>
            <p class="text-xs font-bold text-slate-800 mt-0.5">KCl: <span class="text-cyan-700 font-extrabold">{{ $karungKcl }}</span> karung</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-slate-500 font-semibold mb-1.5">Filter Pemilik Lahan</label>
                <select name="anggota_id" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors min-w-[200px]">
                    <option value="">Semua Anggota</option>
                    @foreach($anggotas as $anggota)
                        <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                    @endforeach
                </select>
            </div>

            @if($blokFilter->isNotEmpty())
            <div>
                <label class="block text-xs text-slate-500 font-semibold mb-1.5">Filter Blok</label>
                <select name="blok_lahan_id" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors min-w-[160px]">
                    <option value="">Semua Blok</option>
                    @foreach($blokFilter as $bf)
                        <option value="{{ $bf->id }}" {{ request('blok_lahan_id') == $bf->id ? 'selected' : '' }}>{{ $bf->nama_blok }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs text-slate-500 font-semibold mb-1.5">Filter Status</label>
                <select name="status_kebutuhan_dominan" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors min-w-[150px]">
                    <option value="">Semua Status</option>
                    @foreach(['Darurat', 'Segera', 'Normal', 'Tunda'] as $s)
                        <option value="{{ $s }}" {{ request('status_kebutuhan_dominan') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->hasAny(['status_kebutuhan_dominan', 'anggota_id', 'blok_lahan_id']))
            <a href="{{ route('laporan.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 text-sm font-medium rounded-lg transition-colors">Reset</a>
            @endif

            {{-- Tombol Cetak --}}
            <button type="button" onclick="window.print()"
                class="ml-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">No</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Blok Lahan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Tgl Analisis</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Dosis Urea</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Dosis KCl</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Total Urea</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Total KCl</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Masalah</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-4 text-slate-400 font-medium hide-mobile">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">{{ $r->blokLahan->nama_blok }}</div>
                            <div class="text-[10px] text-slate-500 font-medium mt-0.5">{{ $r->blokLahan->nama_pemilik }}</div>
                            <div class="text-[10px] text-slate-400">{{ number_format($r->blokLahan->luas_ha, 2) }} Ha · {{ number_format($r->blokLahan->sph) }} ph/Ha</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 text-xs font-medium hide-mobile">{{ $r->tanggal_analisis->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-center hide-mobile">
                            @if($r->dosis_urea)
                            <span class="text-amber-800 font-bold bg-amber-50 border border-amber-200/50 px-2 py-0.5 rounded text-xs">{{ $r->dosis_urea }} kg/pk</span>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center hide-mobile">
                            @if($r->dosis_kcl)
                            <span class="text-cyan-800 font-bold bg-cyan-50 border border-cyan-100 px-2 py-0.5 rounded text-xs">{{ $r->dosis_kcl }} kg/pk</span>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center text-slate-700 font-medium text-xs hide-mobile">{{ $r->total_urea ? number_format($r->total_urea, 1) : '—' }}</td>
                        <td class="px-5 py-4 text-center text-slate-700 font-medium text-xs hide-mobile">{{ $r->total_kcl ? number_format($r->total_kcl, 1) : '—' }}</td>
                        <td class="px-5 py-4 text-xs">
                            @php $sc = match($r->status_kebutuhan_dominan) {
                                'Darurat' => 'bg-red-50 text-red-700 border border-red-100',
                                'Segera'  => 'bg-orange-50 text-orange-700 border border-orange-100',
                                'Normal'  => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                'Tunda'   => 'bg-slate-100 text-slate-600 border border-slate-200',
                                default   => 'bg-slate-100 text-slate-600 border border-slate-200'
                            }; @endphp
                            <span class="inline-flex px-2.5 py-0.5 border rounded-lg font-medium {{ $sc }}">{{ $r->status_kebutuhan_dominan }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600 max-w-[200px] hide-mobile">
                            @if($r->masalah_teridentifikasi)
                                {{ implode(', ', array_slice($r->masalah_teridentifikasi, 0, 2)) }}
                                @if(count($r->masalah_teridentifikasi) > 2)
                                    <span class="text-slate-400">+{{ count($r->masalah_teridentifikasi) - 2 }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('laporan.show', $r) }}" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200 transition-all inline-block" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-slate-400">
                            Belum ada data laporan. <a href="{{ route('rbs.index') }}" class="text-emerald-600 font-semibold hover:text-emerald-700 hover:underline">Jalankan analisis</a> terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="5" class="px-5 py-4 text-xs font-bold text-slate-700 uppercase tracking-wider">TOTAL KESELURUHAN</td>
                        <td class="px-5 py-4 text-center font-extrabold text-amber-700 text-sm">{{ number_format($totalUrea, 1) }} kg</td>
                        <td class="px-5 py-4 text-center font-extrabold text-cyan-700 text-sm">{{ number_format($totalKcl, 1) }} kg</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
