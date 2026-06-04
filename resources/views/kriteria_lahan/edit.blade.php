@extends('layouts.app')

@section('title', 'Edit Kriteria Lahan')
@section('page-title', 'Edit Kriteria Lahan')
@section('page-subtitle', 'Perbarui data kriteria: {{ $kriteriaLahan->blokLahan->nama_blok }}')

@section('content')
<div class="max-w-lg">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('kriteria-lahan.update', $kriteriaLahan) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="blok_lahan_id" class="block text-sm font-medium text-slate-700 mb-2">Blok Lahan <span class="text-red-400">*</span></label>
                <select id="blok_lahan_id" name="blok_lahan_id" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @foreach($blokLahans as $blok)
                        <option value="{{ $blok->id }}" {{ old('blok_lahan_id', $kriteriaLahan->blok_lahan_id) == $blok->id ? 'selected' : '' }}>
                            {{ $blok->nama_blok }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun_tanam" class="block text-sm font-medium text-slate-700 mb-2">Tahun Tanam <span class="text-red-400">*</span></label>
                <input type="number" id="tahun_tanam" name="tahun_tanam" value="{{ old('tahun_tanam', $kriteriaLahan->tahun_tanam) }}" min="1990" max="{{ now()->year }}" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('tahun_tanam') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="jenis_tanah" class="block text-sm font-medium text-slate-700 mb-2">Jenis Tanah <span class="text-red-400">*</span></label>
                <select id="jenis_tanah" name="jenis_tanah" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @php
                        $jenisTanahList = [
                            'Tanah Lempung', 'Tanah Lempung Berpasir', 'Tanah Berpasir',
                            'Tanah Liat', 'Tanah Gambut', 'Tanah Aluvial',
                            'Tanah Podsolik Merah Kuning (PMK)', 'Tanah Laterit',
                            'Tanah Berbatu', 'Lainnya',
                        ];
                    @endphp
                    @foreach($jenisTanahList as $jenis)
                        <option value="{{ $jenis }}" {{ old('jenis_tanah', $kriteriaLahan->jenis_tanah) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="topografi" class="block text-sm font-medium text-slate-700 mb-2">Topografi <span class="text-red-400">*</span></label>
                <select id="topografi" name="topografi" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @foreach(['Datar 0-15°', 'Bergelombang 15-30°', 'Curam >30°'] as $topo)
                        <option value="{{ $topo }}" {{ old('topografi', $kriteriaLahan->topografi) == $topo ? 'selected' : '' }}>{{ $topo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
                    Perbarui Kriteria
                </button>
                <a href="{{ route('kriteria-lahan.index') }}"
                    class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
