@extends('layouts.app')

@section('title', 'Data Kondisi Lahan')
@section('page-title', 'Kondisi Lahan')
@section('page-subtitle', 'Riwayat observasi visual tanaman & lingkungan')

@section('content')
<div class="space-y-3">

    {{-- Header: count + filter/search + button in one row --}}
    <div class="space-y-2">
        <p class="text-xs text-slate-500"><span class="font-bold text-slate-800">{{ $data->total() }}</span> data terdaftar</p>
        <form method="GET" action="{{ route('kondisi-lahan.index') }}" class="flex items-center gap-2">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <input type="text" id="search-kondisi" placeholder="Cari..."
                    class="flex-1 min-w-0 px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                <select name="anggota_id" onchange="this.form.submit()"
                    class="hidden sm:block pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 min-w-0 max-w-[150px] truncate">
                    <option value="">Pemilik</option>
                    @foreach($anggotas as $anggota)
                        <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                    @endforeach
                </select>
                <select name="blok_lahan_id" onchange="this.form.submit()"
                    class="hidden sm:block pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 min-w-0 max-w-[140px] truncate">
                    <option value="">Blok</option>
                    @foreach($bloks as $blok)
                        <option value="{{ $blok->id }}" {{ request('blok_lahan_id') == $blok->id ? 'selected' : '' }}>{{ $blok->nama_blok }}</option>
                    @endforeach
                </select>
                @if(request()->hasAny(['anggota_id','blok_lahan_id']))
                    <a href="{{ route('kondisi-lahan.index') }}" class="hidden sm:inline text-xs text-slate-500 hover:text-slate-700 font-medium flex-shrink-0">✕</a>
                @endif
            </div>
            <a href="{{ route('kondisi-lahan.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-lg transition-all flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Input Kondisi</span>
                <span class="sm:hidden">Input</span>
            </a>
        </form>
        {{-- Mobile filters --}}
        <form method="GET" action="{{ route('kondisi-lahan.index') }}" class="flex items-center gap-2 sm:hidden">
            <select name="anggota_id" onchange="this.form.submit()"
                class="flex-1 min-w-0 pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">Semua Pemilik</option>
                @foreach($anggotas as $anggota)
                    <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                @endforeach
            </select>
            <select name="blok_lahan_id" onchange="this.form.submit()"
                class="flex-1 min-w-0 pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">Semua Blok</option>
                @foreach($bloks as $blok)
                    <option value="{{ $blok->id }}" {{ request('blok_lahan_id') == $blok->id ? 'selected' : '' }}>{{ $blok->nama_blok }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['anggota_id','blok_lahan_id']))
                <a href="{{ route('kondisi-lahan.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium flex-shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        @if($data->isEmpty())
            <div class="flex flex-col items-center justify-center py-14 text-center px-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-slate-500 text-sm">Belum ada data kondisi lahan.</p>
                <a href="{{ route('kondisi-lahan.create') }}" class="mt-2 text-emerald-600 text-sm font-medium hover:underline">Input data pertama →</a>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="overflow-x-auto hidden sm:block rounded-t-2xl">
                <table class="w-full text-sm" id="table-kondisi">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Blok Lahan</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Warna Daun</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">pH</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Musim</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Gejala</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($data as $kondisi)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-slate-800 text-xs">{{ $kondisi->blokLahan->nama_blok ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $kondisi->blokLahan->anggota?->nama ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 text-xs">{{ $kondisi->tanggal_observasi->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5">
                                @if($kondisi->warna_daun)
                                    @php $wc = match($kondisi->warna_daun) { 'Hijau Normal' => 'bg-emerald-100 text-emerald-800', 'Hijau Pucat' => 'bg-lime-100 text-lime-800', 'Kuning Merata','Kuning Tepi','Kuning Antar Tulang' => 'bg-yellow-100 text-yellow-800', 'Oranye/Kemerahan' => 'bg-orange-100 text-orange-900', 'Coklat Ujung' => 'bg-amber-200 text-amber-900', 'Bercak Nekrotik' => 'bg-red-100 text-red-800', default => 'bg-slate-100 text-slate-700' }; @endphp
                                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $wc }}">{{ $kondisi->warna_daun }}</span>
                                @else <span class="text-slate-400 text-xs">—</span> @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs">@if($kondisi->ph_tanah)<span class="font-semibold">{{ $kondisi->ph_tanah }}</span>@else — @endif</td>
                            <td class="px-4 py-3.5 text-slate-600 text-xs">{{ $kondisi->musim_saat_ini ?? '—' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-0.5">
                                    @if(!empty($kondisi->gejala_defisiensi))@foreach($kondisi->gejala_defisiensi as $def)<span class="px-1 py-0.5 bg-red-50 border border-red-200 text-red-700 text-[10px] rounded font-bold">{{ $def }}</span>@endforeach @endif
                                    @if($kondisi->ada_serangan_hama)<span class="px-1 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] rounded">🐛</span>@endif
                                    @if($kondisi->ada_gulma_dominan)<span class="px-1 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] rounded">🌿</span>@endif
                                    @if(empty($kondisi->gejala_defisiensi) && !$kondisi->ada_serangan_hama && !$kondisi->ada_gulma_dominan)<span class="text-slate-400 text-xs">—</span>@endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('kondisi-lahan.edit', $kondisi) }}" title="Edit" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                    <form method="POST" action="{{ route('kondisi-lahan.destroy', $kondisi) }}" class="inline">@csrf @method('DELETE')<button type="button" onclick="confirmDelete(this.closest('form'), '{{ $kondisi->blokLahan->nama_blok ?? 'data' }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card Layout --}}
            <div class="sm:hidden divide-y divide-slate-100" id="mobile-kondisi">
                @foreach($data as $kondisi)
                <div class="p-3.5 space-y-1.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 text-sm truncate">{{ $kondisi->blokLahan->nama_blok ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $kondisi->blokLahan->anggota?->nama ?? '-' }} · {{ $kondisi->tanggal_observasi->format('d/m/Y') }}</p>
                        </div>
                        @if($kondisi->warna_daun)
                        @php $wc = match($kondisi->warna_daun) { 'Hijau Normal' => 'bg-emerald-100 text-emerald-800', 'Hijau Pucat' => 'bg-lime-100 text-lime-800', 'Kuning Merata','Kuning Tepi','Kuning Antar Tulang' => 'bg-yellow-100 text-yellow-800', 'Oranye/Kemerahan' => 'bg-orange-100 text-orange-900', default => 'bg-slate-100 text-slate-700' }; @endphp
                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $wc }} flex-shrink-0">{{ $kondisi->warna_daun }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
                        @if($kondisi->ph_tanah)<span>🧪 pH {{ $kondisi->ph_tanah }}</span>@endif
                        @if($kondisi->musim_saat_ini)<span>🌤️ {{ $kondisi->musim_saat_ini }}</span>@endif
                        @if($kondisi->ada_serangan_hama)<span class="text-red-600 font-medium">🐛 Hama</span>@endif
                        @if($kondisi->ada_gulma_dominan)<span class="text-amber-600 font-medium">🌿 Gulma</span>@endif
                    </div>
                    @if(!empty($kondisi->gejala_defisiensi))
                    <div class="flex flex-wrap gap-1">
                        @foreach($kondisi->gejala_defisiensi as $def)<span class="px-1.5 py-0.5 bg-red-50 border border-red-200 text-red-700 text-[10px] rounded font-bold">{{ $def }}</span>@endforeach
                    </div>
                    @endif
                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('kondisi-lahan.edit', $kondisi) }}" class="inline-flex items-center px-2.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">Edit</a>
                        <form method="POST" action="{{ route('kondisi-lahan.destroy', $kondisi) }}" class="inline">@csrf @method('DELETE')<button type="button" onclick="confirmDelete(this.closest('form'), '{{ $kondisi->blokLahan->nama_blok ?? 'data' }}')" class="inline-flex items-center px-2.5 py-1.5 bg-red-50 text-red-700 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">Hapus</button></form>
                    </div>
                </div>
                @endforeach
            </div>

            @if($data->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $data->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('search-kondisi').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#table-kondisi tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    document.querySelectorAll('#mobile-kondisi > div').forEach(function(card) {
        card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
