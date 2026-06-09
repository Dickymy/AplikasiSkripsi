@extends('layouts.app')

@section('title', 'Tambah Blok Lahan')
@section('page-title', 'Tambah Blok Lahan')
@section('page-subtitle', 'Tambah data blok lahan baru')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<style>
    #draw-map { height: 450px; border-radius: 12px; border: 1px solid #e2e8f0; }
    @media (max-width: 640px) { #draw-map { height: 300px; } }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-4 sm:p-6">
        <form method="POST" action="{{ route('blok-lahan.store') }}" class="space-y-5" id="form-blok-lahan">
            @csrf

            {{-- Baris 1: Pemilik + Nama Blok --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            {{-- Baris 2: SPH saja (luas otomatis dari peta) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sph" class="block text-sm font-medium text-slate-700 mb-2">SPH (Standar Pohon/Ha) <span class="text-red-400">*</span></label>
                    <input type="number" id="sph" name="sph" value="{{ old('sph', 136) }}" min="1" required placeholder="contoh: 136"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('sph') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('sph') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">Umumnya 136 pohon/Ha untuk jarak tanam 9×9m</p>
                </div>
                <div></div>
            </div>

            {{-- Kriteria Agronomis --}}
            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">🌱</span>
                    Kriteria Agronomis
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tahun_tanam" class="block text-sm font-medium text-slate-700 mb-2">Tahun Tanam <span class="text-red-400">*</span></label>
                        <input type="number" id="tahun_tanam" name="tahun_tanam" value="{{ old('tahun_tanam') }}" min="1990" max="{{ now()->year }}" required placeholder="2015"
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
            <div class="border-t border-slate-100 pt-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Koordinat Blok Lahan <span class="text-red-400">*</span>
                </label>

                <div class="flex gap-1 mb-3 bg-slate-100 p-1 rounded-xl">
                    <button type="button" id="tab-draw" onclick="switchTab('draw')"
                        class="flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm">
                        🗺️ Gambar di Peta
                    </button>
                    <button type="button" id="tab-json" onclick="switchTab('json')"
                        class="flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800">
                        📝 GeoJSON Manual
                    </button>
                </div>

                <div id="panel-draw" class="space-y-2">
                    <div class="relative" id="draw-map-wrapper">
                        <div id="draw-map"></div>
                        {{-- Tombol fullscreen — di bawah peta (di luar peta agar tidak tertutup) --}}
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs text-slate-500 flex-1">
                            Klik tombol <strong>poligon</strong> di kiri atas peta. Area <span class="text-amber-600 font-semibold">kuning</span> = lahan terdaftar.
                        </p>
                        <button type="button" onclick="toggleDrawFullscreen()" id="btn-expand"
                            class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span id="btn-expand-text">Perluas Peta</span>
                        </button>
                    </div>
                </div>

                <div id="panel-json" class="hidden">
                    <textarea id="textarea_geojson" rows="6"
                        placeholder='{"type":"Polygon","coordinates":[[[lng,lat],[lng,lat],...]]}'
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-mono text-xs leading-relaxed resize-y">{{ old('koordinat_geojson') }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500">Paste GeoJSON lalu klik di luar textarea — luas akan otomatis terhitung.</p>
                </div>

                <input type="hidden" name="koordinat_geojson" id="koordinat_geojson" value="{{ old('koordinat_geojson') }}">
                @error('koordinat_geojson') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror

                {{-- Luas Lahan — langsung di bawah peta --}}
                <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Luas Lahan</p>
                        <p class="text-xs text-emerald-600" id="luas-info">Gambar polygon untuk menghitung luas otomatis</p>
                    </div>
                    <div class="text-right">
                        <input type="number" id="luas_ha" name="luas_ha" value="{{ old('luas_ha') }}" step="0.01" min="0.01" required readonly
                            class="w-24 px-3 py-2 bg-white border border-emerald-300 rounded-lg text-sm text-emerald-800 font-bold text-right cursor-not-allowed">
                        <p class="text-xs text-emerald-600 mt-0.5">Hektar</p>
                    </div>
                </div>
                @error('luas_ha') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20">
                    Simpan Blok Lahan
                </button>
                <a href="{{ route('blok-lahan.index') }}"
                    class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors text-center">
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
        tabDraw.className = 'flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm';
        tabJson.className = 'flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800';
        setTimeout(function() { drawMap.invalidateSize(); }, 100);
    } else {
        tabJson.className = 'flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all bg-white text-emerald-700 shadow-sm';
        tabDraw.className = 'flex-1 px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all text-slate-600 hover:text-slate-800';
    }
}

// ─── FUNGSI HITUNG LUAS POLYGON (Geodesic - Shoelace formula) ────
function calculateAreaHa(geojson) {
    try {
        var coords;
        if (geojson.type === 'Polygon') {
            coords = geojson.coordinates[0];
        } else if (geojson.type === 'Feature' && geojson.geometry.type === 'Polygon') {
            coords = geojson.geometry.coordinates[0];
        } else {
            return 0;
        }

        // Haversine-based area calculation (approximate for small polygons)
        var area = 0;
        var n = coords.length;
        for (var i = 0; i < n - 1; i++) {
            var j = (i + 1) % n;
            var xi = coords[i][0] * Math.PI / 180;
            var yi = coords[i][1] * Math.PI / 180;
            var xj = coords[j][0] * Math.PI / 180;
            var yj = coords[j][1] * Math.PI / 180;
            area += (xj - xi) * (2 + Math.sin(yi) + Math.sin(yj));
        }
        area = Math.abs(area * 6378137 * 6378137 / 2);
        // Convert m² to Ha (1 Ha = 10000 m²)
        return Math.round(area / 10000 * 100) / 100;
    } catch(e) {
        return 0;
    }
}

function updateLuas(geojson) {
    var ha = calculateAreaHa(geojson);
    var luasEl = document.getElementById('luas_ha');
    var infoEl = document.getElementById('luas-info');
    if (ha > 0) {
        luasEl.value = ha;
        infoEl.textContent = '✓ Luas terhitung: ' + ha + ' Ha dari polygon yang digambar';
        infoEl.className = 'mt-1 text-xs text-emerald-600 font-medium';
    } else {
        luasEl.value = '';
        infoEl.textContent = 'Luas dihitung otomatis saat polygon digambar';
        infoEl.className = 'mt-1 text-xs text-slate-400';
    }
}

// ─── MAP ─────────────────────────────────────────────────────────
var drawMap = L.map('draw-map', { center: [-1.5, 110.0], zoom: 10 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' }).addTo(drawMap);
var satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
L.control.layers({'Peta': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}), 'Satelit': satLayer}).addTo(drawMap);

existingBloks.forEach(function(blok) {
    if (!blok.geojson) return;
    L.geoJSON(blok.geojson, {
        style: { color: '#d97706', fillColor: '#fbbf24', fillOpacity: 0.25, weight: 1.5, dashArray: '4 4' }
    }).bindTooltip(blok.nama, { sticky: true, className: 'text-xs' }).addTo(drawMap);
});

var drawnItems = new L.FeatureGroup();
drawMap.addLayer(drawnItems);
drawMap.addControl(new L.Control.Draw({
    position: 'topleft',
    draw: {
        polygon: { allowIntersection: false, shapeOptions: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } },
        rectangle: { shapeOptions: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } },
        polyline: false, circle: false, circlemarker: false, marker: false
    },
    edit: { featureGroup: drawnItems, remove: true }
}));

drawMap.on(L.Draw.Event.CREATED, function(e) { drawnItems.clearLayers(); drawnItems.addLayer(e.layer); syncGeoJson(); });
drawMap.on(L.Draw.Event.EDITED, syncGeoJson);
drawMap.on(L.Draw.Event.DELETED, syncGeoJson);

function syncGeoJson() {
    var layers = drawnItems.getLayers();
    if (layers.length > 0) {
        var geojson = layers[0].toGeoJSON().geometry;
        var geoStr = JSON.stringify(geojson);
        document.getElementById('koordinat_geojson').value = geoStr;
        document.getElementById('textarea_geojson').value = geoStr;
        updateLuas(geojson);
    } else {
        document.getElementById('koordinat_geojson').value = '';
        document.getElementById('textarea_geojson').value = '';
        updateLuas({});
    }
}

// ─── TEXTAREA GeoJSON: hitung luas saat blur ─────────────────────
document.getElementById('textarea_geojson').addEventListener('blur', function() {
    var val = this.value.trim();
    if (!val) { updateLuas({}); return; }
    try {
        var parsed = JSON.parse(val);
        document.getElementById('koordinat_geojson').value = val;
        updateLuas(parsed);
        // Render di peta juga
        drawnItems.clearLayers();
        L.geoJSON(parsed, { style: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } })
            .eachLayer(function(l) { drawnItems.addLayer(l); });
        drawMap.fitBounds(drawnItems.getBounds().pad(0.2));
    } catch(e) {
        updateLuas({});
    }
});

// ─── FORM SUBMIT ─────────────────────────────────────────────────
document.getElementById('form-blok-lahan').addEventListener('submit', function() {
    if (currentTab === 'json') {
        document.getElementById('koordinat_geojson').value = document.getElementById('textarea_geojson').value.trim();
    }
});

// ─── LOAD OLD VALUE ──────────────────────────────────────────────
var oldGeojson = document.getElementById('koordinat_geojson').value;
if (oldGeojson) {
    try {
        var parsed = JSON.parse(oldGeojson);
        L.geoJSON(parsed, { style: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } })
            .eachLayer(function(l) { drawnItems.addLayer(l); });
        drawMap.fitBounds(drawnItems.getBounds().pad(0.2));
        document.getElementById('textarea_geojson').value = oldGeojson;
        updateLuas(parsed);
    } catch(e) {}
} else if (existingBloks.length > 0) {
    var allBounds = L.featureGroup();
    existingBloks.forEach(function(b) { if (b.geojson) L.geoJSON(b.geojson).eachLayer(function(l) { allBounds.addLayer(l); }); });
    if (allBounds.getLayers().length > 0) drawMap.fitBounds(allBounds.getBounds().pad(0.1));
} else if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) { drawMap.setView([pos.coords.latitude, pos.coords.longitude], 14); });
}

// ─── PREVIEW UMUR ────────────────────────────────────────────────
document.getElementById('tahun_tanam').addEventListener('input', function() {
    var tahun = parseInt(this.value), sekarang = new Date().getFullYear();
    if (tahun >= 1990 && tahun <= sekarang) {
        var umur = sekarang - tahun;
        var kat = umur < 3 ? 'Belum Menghasilkan' : umur <= 8 ? 'Remaja' : umur <= 14 ? 'Menghasilkan Muda' : umur <= 25 ? 'Menghasilkan Tua' : 'Tua Renta';
        document.getElementById('umur-preview').textContent = 'Umur: ' + umur + ' tahun — ' + kat;
    } else { document.getElementById('umur-preview').textContent = ''; }
});

// ─── FULLSCREEN PETA DRAW ────────────────────────────────────────
var isDrawFullscreen = false;
function toggleDrawFullscreen() {
    var mapEl = document.getElementById('draw-map');
    var panelDraw = document.getElementById('panel-draw');
    var mapWrapper = document.getElementById('draw-map-wrapper');
    var sidebar = document.getElementById('sidebar');
    var btnText = document.getElementById('btn-expand-text');
    var btnExpand = document.getElementById('btn-expand');
    isDrawFullscreen = !isDrawFullscreen;
    if (isDrawFullscreen) {
        panelDraw.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9500;background:#fff;padding:0;margin:0;';
        mapWrapper.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;';
        mapEl.style.cssText = 'height:100%!important;width:100%!important;border-radius:0;';
        document.body.style.overflow = 'hidden';
        if (sidebar) sidebar.style.display = 'none';
        btnText.textContent = 'Kecilkan';
        btnExpand.className = 'flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-lg transition-all fixed top-3 right-3 z-[9600]';
    } else {
        panelDraw.style.cssText = '';
        mapWrapper.style.cssText = '';
        mapEl.style.cssText = '';
        document.body.style.overflow = '';
        if (sidebar) sidebar.style.display = '';
        btnText.textContent = 'Perluas Peta';
        btnExpand.className = 'flex-shrink-0 flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all';
    }
    setTimeout(function() { drawMap.invalidateSize(); }, 250);
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && isDrawFullscreen) toggleDrawFullscreen(); });
</script>
@endpush
