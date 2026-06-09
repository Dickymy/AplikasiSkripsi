@extends('layouts.app')

@section('title', 'Tambah Blok Lahan')
@section('page-title', 'Tambah Blok Lahan')
@section('page-subtitle', 'Tambah data blok lahan baru')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<style>
    #draw-map { height: 450px; border-radius: 12px; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('blok-lahan.store') }}" class="space-y-5" id="form-blok-lahan">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    @include('components.searchable-select', [
                        'name' => 'anggota_id',
                        'label' => 'Pemilik Lahan',
                        'placeholder' => 'Cari nama anggota...',
                        'options' => $anggotas,
                        'displayField' => 'nama',
                        'selected' => old('anggota_id'),
                        'required' => true,
                        'error' => $errors->first('anggota_id'),
                        'helpText' => 'Belum ada? <a href="' . route('anggota.create') . '" class="text-emerald-600 font-medium hover:underline">Tambah anggota →</a>',
                    ])
                </div>
                <div>
                    <label for="nama_blok" class="block text-sm font-medium text-slate-700 mb-2">Nama Blok <span class="text-red-400">*</span></label>
                    <input type="text" id="nama_blok" name="nama_blok" value="{{ old('nama_blok') }}" required placeholder="contoh: Blok A, Blok Utara"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('nama_blok') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('nama_blok') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="luas_ha" class="block text-sm font-medium text-slate-700 mb-2">Luas Lahan (Ha) <span class="text-red-400">*</span></label>
                    <input type="number" id="luas_ha" name="luas_ha" value="{{ old('luas_ha') }}" step="0.01" min="0.01" required placeholder="contoh: 12.50"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('luas_ha') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('luas_ha') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sph" class="block text-sm font-medium text-slate-700 mb-2">SPH (Standar Pohon/Ha) <span class="text-red-400">*</span></label>
                    <input type="number" id="sph" name="sph" value="{{ old('sph', 136) }}" min="1" required placeholder="contoh: 136"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('sph') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('sph') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Kriteria Agronomis --}}
            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">🌱</span>
                    Kriteria Agronomis
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tahun_tanam" class="block text-sm font-medium text-slate-700 mb-2">Tahun Tanam <span class="text-red-400">*</span></label>
                        <input type="number" id="tahun_tanam" name="tahun_tanam" value="{{ old('tahun_tanam') }}" min="1990" max="{{ now()->year }}" required placeholder="contoh: 2015"
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('tahun_tanam') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        @error('tahun_tanam') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-emerald-600 font-semibold" id="umur-preview"></p>
                    </div>
                    <div>
                        <label for="jenis_tanah" class="block text-sm font-medium text-slate-700 mb-2">Jenis Tanah <span class="text-red-400">*</span></label>
                        <select id="jenis_tanah" name="jenis_tanah" required
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('jenis_tanah') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            <option value="">— Pilih —</option>
                            @foreach(['Tanah Lempung','Tanah Lempung Berpasir','Tanah Berpasir','Tanah Liat','Tanah Gambut','Tanah Aluvial','Tanah Podsolik Merah Kuning (PMK)','Tanah Laterit','Tanah Berbatu','Lainnya'] as $jt)
                                <option value="{{ $jt }}" {{ old('jenis_tanah') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                            @endforeach
                        </select>
                        @error('jenis_tanah') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="topografi" class="block text-sm font-medium text-slate-700 mb-2">Topografi <span class="text-red-400">*</span></label>
                        <select id="topografi" name="topografi" required
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('topografi') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            <option value="">— Pilih —</option>
                            @foreach(['Datar 0-15°','Bergelombang 15-30°','Curam >30°'] as $tp)
                                <option value="{{ $tp }}" {{ old('topografi') == $tp ? 'selected' : '' }}>{{ $tp }}</option>
                            @endforeach
                        </select>
                        @error('topografi') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Koordinat GeoJSON --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Koordinat Blok Lahan <span class="text-red-400">*</span>
                </label>

                {{-- Tab Buttons --}}
                <div class="flex gap-1 mb-3 bg-slate-100 p-1 rounded-xl">
                    <button type="button" id="tab-draw" onclick="switchTab('draw')"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm">
                        🗺️ Gambar di Peta
                    </button>
                    <button type="button" id="tab-json" onclick="switchTab('json')"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800">
                        📝 Input GeoJSON Manual
                    </button>
                </div>

                {{-- Panel: Gambar di Peta --}}
                <div id="panel-draw" class="space-y-3">
                    <div id="draw-map"></div>

                    <div class="flex items-start gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p>Klik tombol <strong>poligon</strong> (segi lima) di kiri atas peta untuk menggambar batas blok lahan. Area <span class="text-amber-600 font-semibold">kuning</span> adalah lahan yang sudah terdaftar.</p>
                        </div>
                    </div>
                </div>

                {{-- Panel: Input JSON Manual --}}
                <div id="panel-json" class="hidden">
                    <textarea id="textarea_geojson" rows="8"
                        placeholder='{"type":"Polygon","coordinates":[[[108.5,-0.5],[108.6,-0.5],[108.6,-0.4],[108.5,-0.4],[108.5,-0.5]]]}'
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-mono text-xs leading-relaxed resize-y">{{ old('koordinat_geojson') }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500">Tempel raw GeoJSON Polygon dari GeoJSON.io atau QGIS</p>
                </div>

                <input type="hidden" name="koordinat_geojson" id="koordinat_geojson" value="{{ old('koordinat_geojson') }}">
                @error('koordinat_geojson') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
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

@push('scripts')
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
var currentTab = 'draw';
var existingBloks = @json($existingBloks);

function switchTab(tab) {
    currentTab = tab;
    document.getElementById('panel-draw').classList.toggle('hidden', tab !== 'draw');
    document.getElementById('panel-json').classList.toggle('hidden', tab !== 'json');
    var tabDraw = document.getElementById('tab-draw');
    var tabJson = document.getElementById('tab-json');
    if (tab === 'draw') {
        tabDraw.className = 'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm';
        tabJson.className = 'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800';
        setTimeout(function() { drawMap.invalidateSize(); }, 100);
    } else {
        tabJson.className = 'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm';
        tabDraw.className = 'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800';
    }
}

// ─── MAP ─────────────────────────────────────────────────────────
var drawMap = L.map('draw-map', { center: [-1.5, 110.0], zoom: 10 });
var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' });
osmLayer.addTo(drawMap);
var satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
L.control.layers({'Peta': osmLayer, 'Satelit': satLayer}).addTo(drawMap);

// Tampilkan existing polygons (kuning)
existingBloks.forEach(function(blok) {
    if (!blok.geojson) return;
    L.geoJSON(blok.geojson, {
        style: { color: '#d97706', fillColor: '#fbbf24', fillOpacity: 0.25, weight: 1.5, dashArray: '4 4' }
    }).bindTooltip(blok.nama, { sticky: true, className: 'text-xs' }).addTo(drawMap);
});

// Drawing
var drawnItems = new L.FeatureGroup();
drawMap.addLayer(drawnItems);
var drawControl = new L.Control.Draw({
    position: 'topleft',
    draw: {
        polygon: { allowIntersection: false, shapeOptions: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } },
        rectangle: { shapeOptions: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } },
        polyline: false, circle: false, circlemarker: false, marker: false
    },
    edit: { featureGroup: drawnItems, remove: true }
});
drawMap.addControl(drawControl);

drawMap.on(L.Draw.Event.CREATED, function(e) {
    drawnItems.clearLayers();
    drawnItems.addLayer(e.layer);
    updateGeoJson();
});
drawMap.on(L.Draw.Event.EDITED, updateGeoJson);
drawMap.on(L.Draw.Event.DELETED, updateGeoJson);

function updateGeoJson() {
    var layers = drawnItems.getLayers();
    if (layers.length > 0) {
        var geo = JSON.stringify(layers[0].toGeoJSON().geometry);
        document.getElementById('koordinat_geojson').value = geo;
        document.getElementById('textarea_geojson').value = geo;
    } else {
        document.getElementById('koordinat_geojson').value = '';
        document.getElementById('textarea_geojson').value = '';
    }
}

// Form submit sync
document.getElementById('form-blok-lahan').addEventListener('submit', function() {
    if (currentTab === 'json') {
        document.getElementById('koordinat_geojson').value = document.getElementById('textarea_geojson').value.trim();
    }
});

// Load old value jika ada
var oldGeojson = document.getElementById('koordinat_geojson').value;
if (oldGeojson) {
    try {
        var parsed = JSON.parse(oldGeojson);
        L.geoJSON(parsed, { style: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } })
            .eachLayer(function(l) { drawnItems.addLayer(l); });
        drawMap.fitBounds(drawnItems.getBounds().pad(0.2));
        document.getElementById('textarea_geojson').value = oldGeojson;
    } catch(e) {}
}

// Geolocation
if (navigator.geolocation && !oldGeojson && existingBloks.length === 0) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        drawMap.setView([pos.coords.latitude, pos.coords.longitude], 14);
    });
} else if (existingBloks.length > 0 && !oldGeojson) {
    var allBounds = L.featureGroup();
    existingBloks.forEach(function(b) {
        if (b.geojson) L.geoJSON(b.geojson).eachLayer(function(l) { allBounds.addLayer(l); });
    });
    if (allBounds.getLayers().length > 0) drawMap.fitBounds(allBounds.getBounds().pad(0.1));
}

// Preview umur tanaman
document.getElementById('tahun_tanam').addEventListener('input', function() {
    var tahun = parseInt(this.value);
    var sekarang = new Date().getFullYear();
    if (tahun >= 1990 && tahun <= sekarang) {
        var umur = sekarang - tahun;
        var kategori = umur < 3 ? 'Belum Menghasilkan' : umur <= 8 ? 'Remaja' : umur <= 14 ? 'Menghasilkan Muda' : umur <= 25 ? 'Menghasilkan Tua' : 'Tua Renta';
        document.getElementById('umur-preview').textContent = 'Umur: ' + umur + ' tahun — ' + kategori;
    } else {
        document.getElementById('umur-preview').textContent = '';
    }
});
</script>
@endpush
