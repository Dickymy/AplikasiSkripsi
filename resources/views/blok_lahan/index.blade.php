@extends('layouts.app')

@section('title', 'Manajemen Blok Lahan')
@section('page-title', 'Manajemen Blok Lahan')
@section('page-subtitle', 'Data master blok lahan kelapa sawit')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <p class="text-sm text-slate-500">Total <span class="font-semibold text-slate-900">{{ $blokLahans->count() }}</span> blok</p>
            <input type="text" id="search-blok" placeholder="Cari blok / pemilik..."
                class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-52">
        </div>
        <a href="{{ route('blok-lahan.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Blok Lahan
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="table-blok">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Blok</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pemilik</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Luas (Ha)</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">SPH</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kriteria</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($blokLahans as $i => $blok)
                    <tr class="hover:bg-slate-50/50 transition-colors" data-search="{{ strtolower($blok->nama_blok . ' ' . ($blok->anggota?->nama ?? '')) }}">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $blok->nama_blok }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $blok->anggota?->nama ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format($blok->luas_ha, 2) }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format($blok->sph) }}</td>
                        <td class="px-5 py-4">
                            @if($blok->tahun_tanam)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-medium">
                                    {{ $blok->umur_tanaman }} thn · {{ $blok->kategori_umur }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php $status = $blok->rekomendasiRbsTerbaru?->status_kebutuhan_dominan ?? 'Belum'; @endphp
                            @php $sc = match($status) {
                                'Darurat' => 'bg-red-50 text-red-700 border-red-100',
                                'Segera' => 'bg-orange-50 text-orange-700 border-orange-100',
                                'Normal' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'Tunda' => 'bg-slate-100 text-slate-600 border-slate-200',
                                default => 'bg-slate-50 text-slate-500 border-slate-200'
                            }; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium border {{ $sc }}">{{ $status }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('blok-lahan.show', $blok) }}" title="Detail" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('blok-lahan.edit', $blok) }}" title="Edit" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('blok-lahan.destroy', $blok) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" title="Hapus" onclick="confirmDelete(this.closest('form'), '{{ $blok->nama_blok }}')"
                                        class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            Belum ada blok lahan. <a href="{{ route('blok-lahan.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
});
</script>
@endpush
