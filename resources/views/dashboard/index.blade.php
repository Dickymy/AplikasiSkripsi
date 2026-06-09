@extends('layouts.app')

@section('title', 'Dashboard WebGIS')
@section('page-title', 'Peta Lahan Kelapa Sawit')
@section('page-subtitle', 'WebGIS — Visualisasi Blok Lahan & Status Pemupukan')

@push('styles')
<style>
    #map { height: calc(100vh - 280px); min-height: 400px; border-radius: 12px; }
    .leaflet-popup-content-wrapper { background: #ffffff; border: 1px solid #e2e8f0; color: #1e293b; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .leaflet-popup-tip { background: #ffffff; }
    .leaflet-popup-content { margin: 14px 16px; }
    .popup-title { font-size: 14px; font-weight: 700; color: #059669; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
    .popup-row { display: flex; justify-content: space-between; gap: 12px; font-size: 12px; padding: 3px 0; }
    .popup-label { color: #64748b; }
    .popup-value { color: #1e293b; font-weight: 600; }
    .popup-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-segera { background: #fee2e2; color: #991b1b; }
    .badge-normal { background: #dcfce7; color: #166534; }
    .badge-tunda { background: #fef9c3; color: #854d0e; }
    .badge-belum { background: #f1f5f9; color: #475569; }
    .map-legend { position: absolute; bottom: 30px; right: 10px; z-index: 1000; background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; backdrop-filter: blur(8px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 11px; color: #64748b; padding: 2px 0; }
    .legend-dot { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }
</style>
@endpush

@section('content')

{{-- Stats Cards (akan di-update via JS saat filter berubah) --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6" id="stats-cards">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500 mb-1">Total Blok Lahan</p>
        <p class="text-2xl font-bold text-slate-900" id="stat-total-blok">{{ $stats['total_blok'] }}</p>
        <p class="text-xs text-slate-400 mt-1">blok terdaftar</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500 mb-1">Total Luas</p>
        <p class="text-2xl font-bold text-emerald-600" id="stat-total-luas">{{ number_format($stats['total_luas'], 2) }}</p>
        <p class="text-xs text-slate-400 mt-1">hektar</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500 mb-1">Sudah Dianalisis</p>
        <p class="text-2xl font-bold text-blue-600" id="stat-sudah-analisis">{{ $stats['sudah_analisis'] }}</p>
        <p class="text-xs text-slate-400 mt-1">dari {{ $stats['total_blok'] }} blok</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-l-4 border-l-red-500">
        <p class="text-xs text-slate-500 mb-1">Status Darurat</p>
        <p class="text-2xl font-bold text-red-600" id="stat-darurat">{{ $stats['darurat'] }}</p>
        <p class="text-xs text-slate-400 mt-1">penanganan segera</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-l-4 border-l-orange-400">
        <p class="text-xs text-slate-500 mb-1">Status Segera</p>
        <p class="text-2xl font-bold text-orange-500" id="stat-segera">{{ $stats['segera'] }}</p>
        <p class="text-xs text-slate-400 mt-1">tindakan cepat</p>
    </div>
</div>

{{-- Map Container --}}
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden relative shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-sm font-semibold text-slate-800">Peta Interaktif Blok Lahan</span>
            </div>
            {{-- Dropdown Filter Pemilik --}}
            <div class="relative min-w-[220px]">
                <select id="filter-pemilik" class="w-full pl-3 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors cursor-pointer appearance-none">
                    <option value="">Semua Pemilik Lahan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
        </div>
        <a href="{{ route('blok-lahan.create') }}" class="text-xs px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center gap-1.5 font-medium shadow-sm self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Blok
        </a>
    </div>
    <div class="p-4 relative">
        <div id="map"></div>

        {{-- Legend --}}
        <div class="map-legend">
            <p class="text-xs font-semibold text-slate-700 mb-2">Status RBS</p>
            <div class="legend-item"><div class="legend-dot" style="background:#dc2626;"></div>Darurat</div>
            <div class="legend-item"><div class="legend-dot" style="background:#f97316;"></div>Segera</div>
            <div class="legend-item"><div class="legend-dot" style="background:#22c55e;"></div>Normal</div>
            <div class="legend-item"><div class="legend-dot" style="background:#94a3b8;"></div>Tunda</div>
            <div class="legend-item"><div class="legend-dot" style="background:#475569;"></div>Belum Dianalisis</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Reset path icon default Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

const mapData = @json($mapData);

// Inisialisasi peta
const map = L.map('map', {
    center: [-2.5489, 118.0149],
    zoom: 5,
    zoomControl: true,
});

const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
});
osm.addTo(map);

const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri',
    maxZoom: 19
});
L.control.layers({'Peta': osm, 'Satelit': satellite}).addTo(map);

// Warna poligon berdasarkan status RBS (prioritas utama)
function getColorRbs(statusRbs) {
    const colors = {
        'Darurat':          '#dc2626',
        'Segera':           '#f97316',
        'Normal':           '#22c55e',
        'Tunda':            '#94a3b8',
        'Belum Dianalisis': '#475569',
    };
    return colors[statusRbs] || '#475569';
}

// Badge style inline untuk popup
function getBadgeStyleRbs(status) {
    const styles = {
        'Darurat':          'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;',
        'Segera':           'background:#ffedd5;color:#9a3412;border:1px solid #fdba74;',
        'Normal':           'background:#dcfce7;color:#166534;border:1px solid #86efac;',
        'Tunda':            'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;',
        'Belum Dianalisis': 'background:#eff6ff;color:#1e40af;border:1px solid #93c5fd;',
    };
    return styles[status] || styles['Belum Dianalisis'];
}

// Populasi filter pemilik
const selectEl = document.getElementById('filter-pemilik');
const pemilikList = [...new Set(mapData.map(b => b.nama_pemilik).filter(Boolean))].sort();
pemilikList.forEach(pemilik => {
    const opt = document.createElement('option');
    opt.value = pemilik;
    opt.textContent = pemilik;
    selectEl.appendChild(opt);
});

let mapLayers = [];

function updateStats(data) {
    var totalBlok = data.length;
    var totalLuas = data.reduce(function(sum, b) { return sum + (b.luas_ha || 0); }, 0);
    var sudahAnalisis = data.filter(function(b) { return b.status_rbs && b.status_rbs !== 'Belum Dianalisis'; }).length;
    var darurat = data.filter(function(b) { return b.status_rbs === 'Darurat'; }).length;
    var segera = data.filter(function(b) { return b.status_rbs === 'Segera'; }).length;

    document.getElementById('stat-total-blok').textContent = totalBlok;
    document.getElementById('stat-total-luas').textContent = totalLuas.toFixed(2);
    document.getElementById('stat-sudah-analisis').textContent = sudahAnalisis;
    document.getElementById('stat-darurat').textContent = darurat;
    document.getElementById('stat-segera').textContent = segera;
}

function buildPopupContent(blok) {
    const statusRbs    = blok.status_rbs    || 'Belum Dianalisis';
    const masalahRbs   = blok.masalah_rbs   || [];
    const pupukRbs     = blok.pupuk_rbs     || [];
    const saranRbs     = blok.saran_rbs     || '';
    const tglRbs       = blok.tgl_analisis_rbs || '-';
    const jumlahRule   = blok.jumlah_rule   || 0;

    const badgeStyle = getBadgeStyleRbs(statusRbs);

    const masalahHtml = masalahRbs.length
        ? masalahRbs.map(m => `<div style="font-size:11px;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:2px 8px;display:inline-block;margin:1px 2px 1px 0;">${m}</div>`).join('')
        : '<span style="font-size:11px;color:#9ca3af">Tidak ada masalah</span>';

    const pupukHtml = pupukRbs.slice(0, 2).map(p => `
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:6px 8px;margin-top:4px;">
            <div style="font-size:12px;font-weight:600;color:#15803d;">🌿 ${p.jenis_utama}</div>
            ${p.dosis ? `<div style="font-size:11px;color:#4b5563;margin-top:2px;">${p.dosis}</div>` : ''}
        </div>`).join('');

    const saranHtml = saranRbs
        ? `<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:6px 8px;margin-top:6px;">
               <div style="font-size:10px;font-weight:600;color:#92400e;text-transform:uppercase;margin-bottom:2px;">Saran</div>
               <div style="font-size:11px;color:#78350f;line-height:1.4;">${saranRbs.substring(0, 130)}${saranRbs.length > 130 ? '...' : ''}</div>
           </div>`
        : '';

    return `
        <div style="min-width:240px;max-width:290px;font-family:system-ui,sans-serif;">
            <div style="font-weight:700;font-size:14px;color:#0f172a;padding-bottom:8px;border-bottom:1px solid #f1f5f9;margin-bottom:8px;">
                🌴 ${blok.nama_blok}
            </div>
            <div style="font-size:11px;color:#64748b;margin-bottom:6px;">
                ${blok.nama_pemilik || '-'} · ${blok.luas_ha} Ha · ${blok.umur_tanaman !== null ? blok.umur_tanaman + ' thn' : '-'}
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Status RBS</span>
                <span style="${badgeStyle}font-size:11px;font-weight:700;padding:2px 8px;border-radius:9999px;">${statusRbs}</span>
            </div>

            <div style="margin-bottom:6px;">
                <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Masalah Teridentifikasi</div>
                <div>${masalahHtml}</div>
            </div>

            ${pupukRbs.length ? `
            <div style="margin-bottom:4px;">
                <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Rekomendasi Pupuk</div>
                ${pupukHtml}
            </div>` : ''}

            ${saranHtml}

            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;margin-top:6px;border-top:1px solid #f1f5f9;">
                <span style="font-size:10px;color:#9ca3af;">${jumlahRule} rule · ${tglRbs}</span>
                <a href="/rbs/detail/${blok.id}" style="font-size:11px;color:#059669;font-weight:700;text-decoration:none;">
                    Detail RBS →
                </a>
            </div>
        </div>`;
}

function renderMapLayers(selectedPemilik = '') {
    mapLayers.forEach(item => map.removeLayer(item.layer));
    mapLayers = [];

    const activeLayers = [];
    const filteredData = selectedPemilik
        ? mapData.filter(blok => blok.nama_pemilik === selectedPemilik)
        : mapData;

    // Update stats cards berdasarkan data yang difilter
    updateStats(filteredData);

    filteredData.forEach(blok => {
        if (!blok.geojson) return;

        const color = getColorRbs(blok.status_rbs || 'Belum Dianalisis');

        const layer = L.geoJSON(blok.geojson, {
            style: {
                fillColor:   color,
                fillOpacity: 0.45,
                color:       color,
                weight:      2,
                opacity:     0.9,
            }
        });

        layer.bindPopup(buildPopupContent(blok), { maxWidth: 300 });

        layer.on('mouseover', function(e) {
            e.target.setStyle({ fillOpacity: 0.7, weight: 3 });
        });
        layer.on('mouseout', function(e) {
            e.target.setStyle({ fillOpacity: 0.45, weight: 2 });
        });

        layer.addTo(map);
        mapLayers.push({ id: blok.id, layer });
        activeLayers.push(layer);
    });

    if (activeLayers.length > 0) {
        const group = L.featureGroup(activeLayers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Render awal
renderMapLayers();

// Filter pemilik
selectEl.addEventListener('change', function(e) {
    renderMapLayers(e.target.value);
});
</script>
@endpush
