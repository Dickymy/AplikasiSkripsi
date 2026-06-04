@extends('layouts.app')

@section('title', 'Tambah Blok Lahan')
@section('page-title', 'Tambah Blok Lahan')
@section('page-subtitle', 'Tambah data blok lahan baru')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('blok-lahan.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="nama_pemilik" class="block text-sm font-medium text-slate-700 mb-2">Nama Pemilik Lahan <span class="text-red-400">*</span></label>
                <input type="text" id="nama_pemilik" name="nama_pemilik" value="{{ old('nama_pemilik') }}" required placeholder="Nama pemilik lahan"
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('nama_pemilik') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                @error('nama_pemilik') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nama_blok" class="block text-sm font-medium text-slate-700 mb-2">Nama Blok <span class="text-red-400">*</span></label>
                <input type="text" id="nama_blok" name="nama_blok" value="{{ old('nama_blok') }}" required placeholder="contoh: Blok A, Blok Utara"
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('nama_blok') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                @error('nama_blok') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="luas_ha" class="block text-sm font-medium text-slate-700 mb-2">Luas Lahan (Ha) <span class="text-red-400">*</span></label>
                    <input type="number" id="luas_ha" name="luas_ha" value="{{ old('luas_ha') }}" step="0.01" min="0.01" required placeholder="contoh: 12.50"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('luas_ha') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                    @error('luas_ha') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sph" class="block text-sm font-medium text-slate-700 mb-2">SPH (Standar Pohon/Ha) <span class="text-red-400">*</span></label>
                    <input type="number" id="sph" name="sph" value="{{ old('sph', 136) }}" min="1" required placeholder="contoh: 136"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('sph') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                    @error('sph') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Total Tonase Panen --}}
            <div>
                <label for="total_tonase_panen" class="block text-sm font-medium text-slate-700 mb-2">
                    Total Tonase Panen
                    <span class="text-xs text-slate-400 font-normal ml-1">(opsional)</span>
                </label>
                <div class="relative">
                    <input type="number" id="total_tonase_panen" name="total_tonase_panen"
                        value="{{ old('total_tonase_panen') }}"
                        step="0.01" min="0"
                        placeholder="Masukkan total tonase panen"
                        class="w-full px-4 py-3 pr-14 bg-white border {{ $errors->has('total_tonase_panen') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 pointer-events-none">ton</span>
                </div>
                @error('total_tonase_panen') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-slate-400">Yield per hektar akan dihitung otomatis dari tonase panen ÷ luas lahan.</p>
            </div>

            <div>
                <label for="koordinat_geojson" class="block text-sm font-medium text-slate-700 mb-2">
                    Koordinat GeoJSON <span class="text-red-400">*</span>
                    <span class="text-xs text-slate-500 font-normal ml-2">(Format Polygon GeoJSON)</span>
                </label>
                <textarea id="koordinat_geojson" name="koordinat_geojson" rows="8" required placeholder='{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[108.5,0.5],[108.6,0.5],[108.6,0.6],[108.5,0.6],[108.5,0.5]]]}}'
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('koordinat_geojson') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors font-mono text-xs leading-relaxed resize-y">{{ old('koordinat_geojson') }}</textarea>
                @error('koordinat_geojson') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-slate-500">Tempel raw string GeoJSON dari GeoJSON.io atau QGIS</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
                    Simpan Blok Lahan
                </button>
                <a href="{{ route('blok-lahan.index') }}"
                    class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
