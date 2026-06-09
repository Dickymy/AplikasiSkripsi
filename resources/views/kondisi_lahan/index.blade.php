@extends('layouts.app')

@section('title', 'Data Kondisi Lahan')
@section('page-title', 'Kondisi Lahan')
@section('page-subtitle', 'Riwayat observasi visual tanaman & lingkungan')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <p class="text-sm text-slate-500">Total <span class="font-semibold text-slate-800">{{ $data->total() }}</span> data</p>
        <input type="text" id="search-kondisi" placeholder="Cari blok / pemilik..."
            class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-44 sm:w-48">
    </div>
    <a href="{{ route('kondisi-lahan.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-emerald-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="hidden sm:inline">Input Kondisi Baru</span>
        <span class="sm:hidden">Input</span>
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    @if($data->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-slate-500 text-sm">Belum ada data kondisi lahan.</p>
            <a href="{{ route('kondisi-lahan.create') }}" class="mt-3 text-emerald-600 text-sm font-medium hover:underline">
                Input data pertama sekarang →
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Blok Lahan</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Tgl Observasi</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Warna Daun</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">pH Tanah</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Musim</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hide-mobile">Gejala</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $kondisi)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-800">{{ $kondisi->blokLahan->nama_blok ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $kondisi->blokLahan->nama_pemilik ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600 hide-mobile">
                            {{ $kondisi->tanggal_observasi->format('d M Y') }}
                        </td>
                        <td class="px-4 py-4 hide-mobile">
                            @if($kondisi->warna_daun)
                                @php
                                    $warnaColor = match($kondisi->warna_daun) {
                                        'Hijau Normal'       => 'bg-emerald-100 text-emerald-800',
                                        'Hijau Pucat'        => 'bg-lime-100 text-lime-800',
                                        'Kuning Merata'      => 'bg-yellow-100 text-yellow-800',
                                        'Kuning Tepi'        => 'bg-amber-100 text-amber-800',
                                        'Kuning Antar Tulang'=> 'bg-orange-100 text-orange-800',
                                        'Oranye/Kemerahan'   => 'bg-orange-100 text-orange-900',
                                        'Coklat Ujung'       => 'bg-amber-200 text-amber-900',
                                        'Bercak Nekrotik'    => 'bg-red-100 text-red-800',
                                        default              => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $warnaColor }}">
                                    {{ $kondisi->warna_daun }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 hide-mobile">
                            @if($kondisi->ph_tanah)
                                <span class="font-semibold text-slate-800">{{ $kondisi->ph_tanah }}</span>
                                <span class="text-xs text-slate-400 ml-1">({{ $kondisi->label_ph }})</span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-600 text-xs hide-mobile">
                            {{ $kondisi->musim_saat_ini ?? '—' }}
                        </td>
                        <td class="px-4 py-4 hide-mobile">
                            <div class="flex flex-wrap gap-1">
                                @if(!empty($kondisi->gejala_defisiensi))
                                    @foreach($kondisi->gejala_defisiensi as $def)
                                        <span class="inline-flex px-1.5 py-0.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded font-medium">{{ $def }}</span>
                                    @endforeach
                                @endif
                                @if($kondisi->ada_serangan_hama)
                                    <span class="inline-flex px-1.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded font-medium">🐛 Hama</span>
                                @endif
                                @if($kondisi->ada_gulma_dominan)
                                    <span class="inline-flex px-1.5 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded font-medium">🌿 Gulma</span>
                                @endif
                                @if(empty($kondisi->gejala_defisiensi) && !$kondisi->ada_serangan_hama && !$kondisi->ada_gulma_dominan)
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kondisi-lahan.edit', $kondisi) }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('kondisi-lahan.destroy', $kondisi) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.closest('form'), '{{ $kondisi->blokLahan->nama_blok ?? 'data' }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $data->links() }}
        </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
document.getElementById('search-kondisi').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('table tbody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
