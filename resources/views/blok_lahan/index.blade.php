@extends('layouts.app')

@section('title', 'Manajemen Blok Lahan')
@section('page-title', 'Manajemen Blok Lahan')
@section('page-subtitle', 'Data master blok lahan kelapa sawit')

@section('content')
<div class="space-y-3">

    {{-- Header: count + filter/search + button in one row --}}
    <div class="space-y-2">
        <p class="text-xs text-slate-500"><span class="font-bold text-slate-800">{{ $blokLahans->total() }}</span> blok terdaftar</p>
        <form method="GET" action="{{ route('blok-lahan.index') }}" class="flex items-center gap-2">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <input type="text" id="search-blok" placeholder="Cari..."
                    class="flex-1 min-w-0 px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                <select name="anggota_id" onchange="this.form.submit()"
                    class="hidden sm:block pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 min-w-0 max-w-[150px] truncate">
                    <option value="">Pemilik</option>
                    @foreach($anggotas as $anggota)
                        <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()"
                    class="hidden sm:block pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 min-w-0 max-w-[120px]">
                    <option value="">Status</option>
                    @foreach(['Darurat','Segera','Normal','Tunda','Belum'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                @if(request()->hasAny(['anggota_id','status']))
                    <a href="{{ route('blok-lahan.index') }}" class="hidden sm:inline text-xs text-slate-500 hover:text-slate-700 font-medium flex-shrink-0">✕</a>
                @endif
            </div>
            <a href="{{ route('blok-lahan.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold rounded-lg transition-all flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Tambah Blok</span>
                <span class="sm:hidden">Tambah</span>
            </a>
        </form>
        {{-- Mobile filters (below search on small screens) --}}
        <form method="GET" action="{{ route('blok-lahan.index') }}" class="flex items-center gap-2 sm:hidden">
            <select name="anggota_id" onchange="this.form.submit()"
                class="flex-1 min-w-0 pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">Semua Pemilik</option>
                @foreach($anggotas as $anggota)
                    <option value="{{ $anggota->id }}" {{ request('anggota_id') == $anggota->id ? 'selected' : '' }}>{{ $anggota->nama }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()"
                class="flex-1 min-w-0 pl-2.5 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">Semua Status</option>
                @foreach(['Darurat','Segera','Normal','Tunda','Belum'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['anggota_id','status']))
                <a href="{{ route('blok-lahan.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium flex-shrink-0">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table + Cards --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl">
        {{-- Desktop Table --}}
        <div class="overflow-x-auto hidden sm:block rounded-t-2xl">
            <table class="w-full text-sm" id="table-blok">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama Blok</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Pemilik</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Luas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kriteria</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($blokLahans as $blok)
                    <tr class="hover:bg-slate-50/50 transition-colors" data-search="{{ strtolower($blok->nama_blok . ' ' . ($blok->anggota?->nama ?? '')) }}">
                        <td class="px-4 py-3.5 text-slate-400">{{ $loop->iteration + ($blokLahans->currentPage() - 1) * $blokLahans->perPage() }}</td>
                        <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $blok->nama_blok }}</td>
                        <td class="px-4 py-3.5 text-slate-600 text-xs">{{ $blok->anggota?->nama ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-slate-600 text-xs">{{ number_format($blok->luas_ha, 2) }} Ha</td>
                        <td class="px-4 py-3.5">
                            @if($blok->tahun_tanam)
                                <span class="inline-flex px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-medium">{{ $blok->umur_tanaman }} thn</span>
                            @else <span class="text-xs text-slate-400">—</span> @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @php $status = $blok->rekomendasiRbsTerbaru?->status_kebutuhan_dominan ?? 'Belum'; $sc = match($status) { 'Darurat' => 'bg-red-50 text-red-700 border-red-100', 'Segera' => 'bg-orange-50 text-orange-700 border-orange-100', 'Normal' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'Tunda' => 'bg-slate-100 text-slate-600 border-slate-200', default => 'bg-slate-50 text-slate-500 border-slate-200' }; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium border {{ $sc }}">{{ $status }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('blok-lahan.show', $blok) }}" title="Detail" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                                <a href="{{ route('blok-lahan.edit', $blok) }}" title="Edit" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <form method="POST" action="{{ route('blok-lahan.destroy', $blok) }}" class="inline">@csrf @method('DELETE')<button type="button" title="Hapus" onclick="confirmDelete(this.closest('form'), '{{ $blok->nama_blok }}')" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada blok lahan. <a href="{{ route('blok-lahan.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card Layout --}}
        <div class="sm:hidden divide-y divide-slate-100" id="mobile-blok">
            @forelse($blokLahans as $blok)
            @php $status = $blok->rekomendasiRbsTerbaru?->status_kebutuhan_dominan ?? 'Belum'; $sc = match($status) { 'Darurat' => 'bg-red-100 text-red-800', 'Segera' => 'bg-orange-100 text-orange-800', 'Normal' => 'bg-emerald-100 text-emerald-800', 'Tunda' => 'bg-slate-100 text-slate-700', default => 'bg-slate-100 text-slate-500' }; @endphp
            <div class="p-3.5 space-y-1.5" data-search="{{ strtolower($blok->nama_blok . ' ' . ($blok->anggota?->nama ?? '')) }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 text-sm truncate">{{ $blok->nama_blok }}</p>
                        <p class="text-[11px] text-slate-400">{{ $blok->anggota?->nama ?? '—' }} · {{ number_format($blok->luas_ha, 2) }} Ha</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc }} flex-shrink-0">{{ $status }}</span>
                </div>
                @if($blok->tahun_tanam)
                <p class="text-[11px] text-slate-500">🌴 {{ $blok->umur_tanaman }} thn · {{ $blok->kategori_umur }}</p>
                @endif
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('blok-lahan.show', $blok) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-50 text-slate-700 border border-slate-200 text-xs font-medium rounded-lg hover:bg-slate-100 transition-colors">Detail</a>
                    <a href="{{ route('blok-lahan.edit', $blok) }}" class="inline-flex items-center px-2.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">Edit</a>
                    <form method="POST" action="{{ route('blok-lahan.destroy', $blok) }}" class="inline">@csrf @method('DELETE')<button type="button" onclick="confirmDelete(this.closest('form'), '{{ $blok->nama_blok }}')" class="inline-flex items-center px-2.5 py-1.5 bg-red-50 text-red-700 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">Hapus</button></form>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-slate-400">Belum ada blok lahan. <a href="{{ route('blok-lahan.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a></div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($blokLahans->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $blokLahans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('search-blok').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#table-blok tbody tr[data-search]').forEach(function(row) {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
    document.querySelectorAll('#mobile-blok > div[data-search]').forEach(function(card) {
        card.style.display = card.dataset.search.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
