@extends('layouts.app')

@section('title', 'Laporan Pemupukan')
@section('page-title', 'Laporan & Rekap Pemupukan')
@section('page-subtitle', 'Rekapitulasi rekomendasi dan kebutuhan logistik pupuk')

@push('styles')
<style>
    @media print {
        aside, header, .no-print, nav, .sidebar { display: none !important; }
        button[type="button"] { display: none !important; }
        .lg\:ml-64, [data-main-content] { margin-left: 0 !important; }
        main { padding: 0 !important; }
        .shadow-sm, .shadow-lg { box-shadow: none !important; }
        .rounded-2xl, .rounded-xl { border-radius: 4px !important; }
        body { font-size: 11px !important; }
        table th, table td { padding: 4px 8px !important; }
        table { width: 100% !important; }
        .hide-mobile { display: table-cell !important; }
        .sm\:hidden { display: none !important; }
        .hidden.sm\:block { display: block !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Data</p>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ $rekap->count() }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">rekomendasi</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Urea</p>
            <p class="text-xl sm:text-2xl font-extrabold text-amber-700">{{ number_format($totalUrea, 1) }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">kg</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total KCl</p>
            <p class="text-xl sm:text-2xl font-extrabold text-cyan-700">{{ number_format($totalKcl, 1) }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">kg</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 border-l-4 border-l-emerald-600 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Karung (50kg)</p>
            <p class="text-xs font-bold text-slate-800">Urea: <span class="text-amber-700 font-extrabold">{{ $karungUrea }}</span></p>
            <p class="text-xs font-bold text-slate-800 mt-0.5">KCl: <span class="text-cyan-700 font-extrabold">{{ $karungKcl }}</span></p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm no-print">
        <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-col sm:flex-row flex-wrap items-start sm:items-end gap-2 sm:gap-3">
            <div class="w-full sm:w-auto">
                <label class="block text-xs text-slate-500 font-semibold mb-1">Pemilik</label>
                <select name="anggota_id" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors sm:min-w-[180px]">
                    <option value="">Semua Anggota</option>
                    @foreach($anggotas as $anggota)
                        <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                    @endforeach
                </select>
            </div>

            @if($blokFilter->isNotEmpty())
            <div class="w-full sm:w-auto">
                <label class="block text-xs text-slate-500 font-semibold mb-1">Blok</label>
                <select name="blok_lahan_id" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors sm:min-w-[150px]">
                    <option value="">Semua Blok</option>
                    @foreach($blokFilter as $bf)
                        <option value="{{ $bf->id }}" {{ request('blok_lahan_id') == $bf->id ? 'selected' : '' }}>{{ $bf->nama_blok }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-full sm:w-auto">
                <label class="block text-xs text-slate-500 font-semibold mb-1">Status</label>
                <select name="status_kebutuhan_dominan" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors sm:min-w-[130px]">
                    <option value="">Semua Status</option>
                    @foreach(['Darurat', 'Segera', 'Normal', 'Tunda'] as $s)
                        <option value="{{ $s }}" {{ request('status_kebutuhan_dominan') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-auto pt-1 sm:pt-0">
                @if(request()->hasAny(['status_kebutuhan_dominan', 'anggota_id', 'blok_lahan_id']))
                <a href="{{ route('laporan.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-medium rounded-lg transition-colors">Reset</a>
                @endif
                <button type="button" onclick="window.print()"
                    class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm flex items-center gap-1.5 ml-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak
                </button>
            </div>
        </form>
    </div>

    {{-- Desktop Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Blok Lahan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Urea</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">KCl</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3.5 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800">{{ $r->blokLahan->nama_blok }}</div>
                            <div class="text-[10px] text-slate-500 font-medium">{{ $r->blokLahan->nama_pemilik }} · {{ number_format($r->blokLahan->luas_ha, 2) }} Ha</div>
                        </td>
                        <td class="px-4 py-3.5 text-slate-600 text-xs font-medium">{{ $r->tanggal_analisis->format('d/m/Y') }}</td>
                        <td class="px-4 py-3.5 text-center">
                            @if($r->total_urea)
                            <span class="text-amber-800 font-bold text-xs">{{ number_format($r->total_urea, 1) }} kg</span>
                            @else <span class="text-slate-400 text-xs">—</span> @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($r->total_kcl)
                            <span class="text-cyan-800 font-bold text-xs">{{ number_format($r->total_kcl, 1) }} kg</span>
                            @else <span class="text-slate-400 text-xs">—</span> @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @php $sc = match($r->status_kebutuhan_dominan) {
                                'Darurat' => 'bg-red-50 text-red-700 border-red-100',
                                'Segera'  => 'bg-orange-50 text-orange-700 border-orange-100',
                                'Normal'  => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                default   => 'bg-slate-100 text-slate-600 border-slate-200'
                            }; @endphp
                            <span class="inline-flex px-2 py-0.5 border rounded-lg text-xs font-medium {{ $sc }}">{{ $r->status_kebutuhan_dominan }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('laporan.show', $r) }}" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200 transition-all" title="Detail">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('laporan.pdf', $r) }}" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-all" title="PDF">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            Belum ada data laporan. <a href="{{ route('rbs.index') }}" class="text-emerald-600 font-semibold hover:underline">Jalankan analisis</a> terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="3" class="px-4 py-3 text-xs font-bold text-slate-700 uppercase">TOTAL</td>
                        <td class="px-4 py-3 text-center font-extrabold text-amber-700 text-sm">{{ number_format($totalUrea, 1) }} kg</td>
                        <td class="px-4 py-3 text-center font-extrabold text-cyan-700 text-sm">{{ number_format($totalKcl, 1) }} kg</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Mobile Card Layout --}}
    <div class="sm:hidden space-y-3">
        @forelse($rekap as $r)
        <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-sm space-y-2">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold text-slate-800 text-sm">{{ $r->blokLahan->nama_blok }}</p>
                    <p class="text-[11px] text-slate-500">{{ $r->blokLahan->nama_pemilik }} · {{ number_format($r->blokLahan->luas_ha, 2) }} Ha</p>
                </div>
                @php $sc = match($r->status_kebutuhan_dominan) {
                    'Darurat' => 'bg-red-100 text-red-800',
                    'Segera'  => 'bg-orange-100 text-orange-800',
                    'Normal'  => 'bg-emerald-100 text-emerald-800',
                    default   => 'bg-slate-100 text-slate-700'
                }; @endphp
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc }} flex-shrink-0">{{ $r->status_kebutuhan_dominan }}</span>
            </div>
            <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-600">
                <span>📅 {{ $r->tanggal_analisis->format('d/m/Y') }}</span>
                @if($r->total_urea)<span class="text-amber-700 font-semibold">Urea: {{ number_format($r->total_urea, 1) }} kg</span>@endif
                @if($r->total_kcl)<span class="text-cyan-700 font-semibold">KCl: {{ number_format($r->total_kcl, 1) }} kg</span>@endif
                @if($r->jumlah_rule_terpicu)<span>⚡ {{ $r->jumlah_rule_terpicu }} rule</span>@endif
            </div>
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                <a href="{{ route('laporan.show', $r) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-50 text-slate-700 border border-slate-200 text-xs font-medium rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Detail
                </a>
                <a href="{{ route('laporan.pdf', $r) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 text-red-700 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <p class="text-slate-400 text-sm">Belum ada data laporan.</p>
            <a href="{{ route('rbs.index') }}" class="text-emerald-600 text-sm font-semibold hover:underline mt-2 inline-block">Jalankan analisis →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
