@extends('layouts.app')

@section('title', 'Anggota Kelompok Tani')
@section('page-title', 'Anggota Kelompok Tani')
@section('page-subtitle', 'Data anggota pemilik lahan')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <p class="text-sm text-slate-500">Total <span class="font-semibold text-slate-900">{{ $anggotas->count() }}</span> anggota</p>
            <input type="text" id="search-anggota" placeholder="Cari nama anggota..."
                class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors w-48">
        </div>
        <a href="{{ route('anggota.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Anggota
        </a>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Anggota</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. HP</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah Blok</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($anggotas as $i => $anggota)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $anggota->nama }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $anggota->no_hp ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 text-xs max-w-[200px] truncate">{{ $anggota->alamat ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-bold">
                                {{ $anggota->blok_lahans_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('anggota.edit', $anggota) }}" title="Edit"
                                   class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('anggota.destroy', $anggota) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" title="Hapus" onclick="confirmDelete(this.closest('form'), '{{ $anggota->nama }}')"
                                        class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            Belum ada anggota. <a href="{{ route('anggota.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a>
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
document.getElementById('search-anggota').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(function(row) {
        var nama = row.querySelector('td:nth-child(2)');
        if (nama) row.style.display = nama.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
