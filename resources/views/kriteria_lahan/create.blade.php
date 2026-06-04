@extends('layouts.app')

@section('title', 'Tambah Kriteria Lahan')
@section('page-title', 'Tambah Kriteria Lahan')
@section('page-subtitle', 'Input karakteristik agronomis blok lahan')

@section('content')
<div class="max-w-lg">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('kriteria-lahan.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="blok_lahan_id" class="block text-sm font-medium text-slate-700 mb-2">Blok Lahan <span class="text-red-400">*</span></label>
                <select id="blok_lahan_id" name="blok_lahan_id" required
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('blok_lahan_id') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 transition-colors">
                    <option value="">-- Pilih Blok Lahan --</option>
                    @foreach($blokLahans as $blok)
                        <option value="{{ $blok->id }}" {{ old('blok_lahan_id') == $blok->id ? 'selected' : '' }}>
                            {{ $blok->nama_blok }} ({{ number_format($blok->luas_ha, 2) }} Ha)
                        </option>
                    @endforeach
                </select>
                @error('blok_lahan_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                @if($blokLahans->isEmpty())
                    <p class="mt-1.5 text-xs text-amber-600 font-medium">Semua blok lahan sudah memiliki kriteria, atau belum ada blok terdaftar.</p>
                @endif
            </div>

            <div>
                <label for="tahun_tanam" class="block text-sm font-medium text-slate-700 mb-2">Tahun Tanam <span class="text-red-400">*</span></label>
                <input type="number" id="tahun_tanam" name="tahun_tanam" value="{{ old('tahun_tanam') }}" min="1990" max="{{ now()->year }}" required placeholder="contoh: 2015"
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('tahun_tanam') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 transition-colors">
                @error('tahun_tanam') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-emerald-600 font-semibold" id="umur-preview"></p>
            </div>

            <div>
                <label for="jenis_tanah" class="block text-sm font-medium text-slate-700 mb-2">Jenis Tanah <span class="text-red-400">*</span></label>
                <select id="jenis_tanah" name="jenis_tanah" required
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('jenis_tanah') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 transition-colors">
                    <option value="">-- Pilih Jenis Tanah --</option>
                    <option value="Tanah Lempung"              {{ old('jenis_tanah') == 'Tanah Lempung'              ? 'selected' : '' }}>Tanah Lempung</option>
                    <option value="Tanah Lempung Berpasir"     {{ old('jenis_tanah') == 'Tanah Lempung Berpasir'     ? 'selected' : '' }}>Tanah Lempung Berpasir</option>
                    <option value="Tanah Berpasir"             {{ old('jenis_tanah') == 'Tanah Berpasir'             ? 'selected' : '' }}>Tanah Berpasir</option>
                    <option value="Tanah Liat"                 {{ old('jenis_tanah') == 'Tanah Liat'                 ? 'selected' : '' }}>Tanah Liat</option>
                    <option value="Tanah Gambut"               {{ old('jenis_tanah') == 'Tanah Gambut'               ? 'selected' : '' }}>Tanah Gambut</option>
                    <option value="Tanah Aluvial"              {{ old('jenis_tanah') == 'Tanah Aluvial'              ? 'selected' : '' }}>Tanah Aluvial</option>
                    <option value="Tanah Podsolik Merah Kuning (PMK)" {{ old('jenis_tanah') == 'Tanah Podsolik Merah Kuning (PMK)' ? 'selected' : '' }}>Tanah Podsolik Merah Kuning (PMK)</option>
                    <option value="Tanah Laterit"              {{ old('jenis_tanah') == 'Tanah Laterit'              ? 'selected' : '' }}>Tanah Laterit</option>
                    <option value="Tanah Berbatu"              {{ old('jenis_tanah') == 'Tanah Berbatu'              ? 'selected' : '' }}>Tanah Berbatu</option>
                    <option value="Lainnya"                    {{ old('jenis_tanah') == 'Lainnya'                    ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('jenis_tanah') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="topografi" class="block text-sm font-medium text-slate-700 mb-2">Topografi <span class="text-red-400">*</span></label>
                <select id="topografi" name="topografi" required
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('topografi') ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 transition-colors">
                    <option value="">-- Pilih Topografi --</option>
                    <option value="Datar 0-15°" {{ old('topografi') == 'Datar 0-15°' ? 'selected' : '' }}>Datar 0-15°</option>
                    <option value="Bergelombang 15-30°" {{ old('topografi') == 'Bergelombang 15-30°' ? 'selected' : '' }}>Bergelombang 15-30°</option>
                    <option value="Curam >30°" {{ old('topografi') == 'Curam >30°' ? 'selected' : '' }}>Curam &gt;30°</option>
                </select>
                @error('topografi') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">
                    Simpan Kriteria
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

@push('scripts')
<script>
document.getElementById('tahun_tanam').addEventListener('input', function() {
    const tahun = parseInt(this.value);
    const sekarang = new Date().getFullYear();
    if (tahun >= 1990 && tahun <= sekarang) {
        const umur = sekarang - tahun;
        let kategori = '';
        if (umur < 3) kategori = 'Belum Menghasilkan';
        else if (umur <= 8) kategori = 'Remaja';
        else if (umur <= 14) kategori = 'Menghasilkan Muda';
        else if (umur <= 25) kategori = 'Menghasilkan Tua';
        else kategori = 'Tua Renta';
        document.getElementById('umur-preview').textContent = `Umur: ${umur} tahun — Kategori: ${kategori}`;
    } else {
        document.getElementById('umur-preview').textContent = '';
    }
});
</script>
@endpush
