@extends('layouts.app')

@section('title', 'Dashboard WebGIS')
@section('page-title', 'Peta Lahan Kelapa Sawit')
@section('page-subtitle', 'WebGIS — Visualisasi Blok Lahan & Status Pemupukan')

@push('styles')
<style>
    #map { height: calc(100vh - 320px); min-height: 300px; border-radius: 12px; }
    @media (max-width: 640px) { #map { height: 300px; min-height: 250px; border-radius: 8px; } }
    @media (min-width: 1024px) { #map { min-height: 420px; } }

    .leaflet-tooltip-label { background: transparent !important; border: none !important; box-shadow: none !important; color: #1e293b; font-size: 10px; font-weight: 700; text-shadow: 0 0 3px #fff, 0 0 3px #fff, 0 0 3px #fff; padding: 0 !important; }
    .leaflet-popup-content-wrapper { background: #fff; border: 1px solid #e2e8f0; color: #1e293b; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .leaflet-popup-tip { background: #fff; }
    .leaflet-popup-content { margin: 10px 12px; max-height: 200px; overflow-y: auto; }
    @media (max-width: 640px) {
        .leaflet-popup-content { margin: 8px 10px; max-height: 160px; font-size: 11px; }
        .leaflet-popup-content-wrapper { max-width: 230px !important; }
    }

    /* Legend */
    .map-legend { position: absolute; bottom: 10px; right: 10px; z-index: 42; background: rgba(255,255,255,0.93); border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; backdrop-filter: blur(8px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    @media (max-width: 640px) { .map-legend { bottom: 6px; right: 6px; left: 6px; padding: 5px 8px; } .map-legend .legend-items { display: flex; flex-wrap: wrap; gap: 4px 10px; } }
    .legend-item { display: flex; align-items: center; gap: 5px; font-size: 10px; color: #64748b; padding: 1px 0; }
    .legend-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

    /* Filter status */
    .status-filter-btn { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; border: 1.5px solid; cursor: pointer; transition: all 0.15s; user-select: none; white-space: nowrap; line-height: 1.4; }
    @media (max-width: 640px) { .status-filter-btn { padding: 2.5px 7px; font-size: 9px; } }
    .status-filter-btn.active { opacity: 1; }
    .status-filter-btn.inactive { opacity: 0.35; }
    .status-filter-btn[data-status="Darurat"] { border-color: #fca5a5; background: #fee2e2; color: #991b1b; }
    .status-filter-btn[data-status="Darurat"].active { background: #dc2626; color: #fff; border-color: #dc2626; }
    .status-filter-btn[data-status="Segera"] { border-color: #fdba74; background: #ffedd5; color: #9a3412; }
    .status-filter-btn[data-status="Segera"].active { background: #f97316; color: #fff; border-color: #f97316; }
    .status-filter-btn[data-status="Normal"] { border-color: #86efac; background: #dcfce7; color: #166534; }
    .status-filter-btn[data-status="Normal"].active { background: #22c55e; color: #fff; border-color: #22c55e; }
    .status-filter-btn[data-status="Tunda"] { border-color: #cbd5e1; background: #f1f5f9; color: #475569; }
    .status-filter-btn[data-status="Tunda"].active { background: #94a3b8; color: #fff; border-color: #94a3b8; }
    .status-filter-btn[data-status="Belum Dianalisis"] { border-color: #93c5fd; background: #eff6ff; color: #1e40af; }
    .status-filter-btn[data-status="Belum Dianalisis"].active { background: #475569; color: #fff; border-color: #475569; }

    /* Luas per status */
    .luas-status-item { display: flex; align-items: center; gap: 5px; padding: 5px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 10px; }
    .luas-status-item .luas-dot { width: 7px; height: 7px; border-radius: 2px; flex-shrink: 0; }
    .luas-status-item .luas-label { color: #64748b; white-space: nowrap; }
    .luas-status-item .luas-value { font-weight: 700; color: #1e293b; margin-left: auto; white-space: nowrap; }

    /* Stat cards */
    @media (max-width: 640px) {
        #stats-cards .stat-card { padding: 8px 10px; }
        #stats-cards .stat-value { font-size: 1.2rem; }
        #stats-cards .stat-label { font-size: 9px; }
        #stats-cards .stat-sub { font-size: 8px; }
    }

    select:disabled { opacity: 0.5; cursor: not-allowed; background: #f1f5f9; }

    /* Action buttons */
    .btn-map { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.15s; white-space: nowrap; border: 1.5px solid; text-decoration: none; }
    .btn-map.expand { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
    .btn-map.expand:hover { background: #dbeafe; border-color: #60a5fa; }
    .btn-map.shrink { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
    .btn-map.shrink:hover { background: #fecaca; }
    .btn-map.tambah { background: #059669; color: #fff; border-color: #059669; }
    .btn-map.tambah:hover { background: #047857; border-color: #047857; }
    @media (max-width: 640px) { .btn-map { padding: 6px 10px; font-size: 10px; } }
</style>
@endpush

@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-3 sm:mb-4" id="stats-cards">
    <div class="stat-card bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
        <p class="stat-label text-xs text-slate-500 mb-0.5">Total Blok</p>
        <p class="stat-value text-xl sm:text-2xl font-bold text-slate-900" id="stat-total-blok">{{ $stats['total_blok'] }}</p>
        <p class="stat-sub text-xs text-slate-400">blok terdaftar</p>
    </div>
    <div class="stat-card bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
        <p class="stat-label text-xs text-slate-500 mb-0.5">Total Luas</p>
        <p class="stat-value text-xl sm:text-2xl font-bold text-emerald-600" id="stat-total-luas">{{ number_format($stats['total_luas'], 2) }}</p>
        <p class="stat-sub text-xs text-slate-400">hektar</p>
    </div>
    <div class="stat-card bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm">
        <p class="stat-label text-xs text-slate-500 mb-0.5">Dianalisis</p>
        <p class="stat-value text-xl sm:text-2xl font-bold text-blue-600" id="stat-sudah-analisis">{{ $stats['sudah_analisis'] }}</p>
        <p class="stat-sub text-xs text-slate-400">dari {{ $stats['total_blok'] }} blok</p>
    </div>
    <div class="stat-card bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm border-l-4 border-l-red-500">
        <p class="stat-label text-xs text-slate-500 mb-0.5">Defisiensi Berat</p>
        <p class="stat-value text-xl sm:text-2xl font-bold text-red-600" id="stat-darurat">{{ $stats['darurat'] }}</p>
        @php $deltaDarurat = $stats['darurat'] - ($statsBulanLalu['darurat'] ?? 0); @endphp
        @if($deltaDarurat > 0)
        <p class="stat-sub text-xs text-red-500">↑ {{ $deltaDarurat }} dari bulan lalu</p>
        @elseif($deltaDarurat < 0)
        <p class="stat-sub text-xs text-green-600">↓ {{ abs($deltaDarurat) }} dari bulan lalu</p>
        @else
        <p class="stat-sub text-xs text-slate-400">= sama dengan bulan lalu</p>
        @endif
    </div>
    <div class="stat-card bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm border-l-4 border-l-orange-400">
        <p class="stat-label text-xs text-slate-500 mb-0.5">Perlu Pupuk</p>
        <p class="stat-value text-xl sm:text-2xl font-bold text-orange-500" id="stat-segera">{{ $stats['segera'] }}</p>
        @php $deltaSegera = $stats['segera'] - ($statsBulanLalu['segera'] ?? 0); @endphp
        @if($deltaSegera > 0)
        <p class="stat-sub text-xs text-red-500">↑ {{ $deltaSegera }} dari bulan lalu</p>
        @elseif($deltaSegera < 0)
        <p class="stat-sub text-xs text-green-600">↓ {{ abs($deltaSegera) }} dari bulan lalu</p>
        @else
        <p class="stat-sub text-xs text-slate-400">= sama dengan bulan lalu</p>
        @endif
    </div>
</div>

{{-- Blok Perlu Perhatian (E1) --}}
@if($blokPerluPerhatian->isNotEmpty())
<div class="mb-3 sm:mb-4 bg-amber-50 border border-amber-200 rounded-xl p-3 sm:p-4">
    <p class="text-xs font-bold text-amber-800 mb-2 flex items-center gap-1.5">
        <span>⚠️</span> Perlu Perhatian — {{ $blokPerluPerhatian->count() }} Blok
    </p>
    <div class="flex flex-wrap gap-2">
        @foreach($blokPerluPerhatian->take(6) as $bp)
        @php
            $keterangan = $bp->rekomendasiRbsTerbaru
                ? 'Terakhir ' . $bp->rekomendasiRbsTerbaru->tanggal_analisis->diffInDays(now()) . ' hari lalu'
                : 'Belum dianalisis';
        @endphp
        <a href="{{ route('rbs.detail', $bp) }}" class="flex items-center gap-2 px-3 py-2 bg-white border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
            <div>
                <p class="text-xs font-semibold text-slate-800">{{ $bp->nama_blok }}</p>
                <p class="text-[10px] text-amber-700">{{ $bp->nama_pemilik }} · {{ $keterangan }}</p>
            </div>
        </a>
        @endforeach
        @if($blokPerluPerhatian->count() > 6)
        <a href="{{ route('rbs.index') }}" class="flex items-center px-3 py-2 text-[10px] text-amber-700 font-semibold hover:underline">
            +{{ $blokPerluPerhatian->count() - 6 }} lainnya →
        </a>
        @endif
    </div>
</div>
@endif

{{-- Luas per Status --}}
<div class="mb-3 sm:mb-4">
    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Luas Lahan per Status</p>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
        <div class="luas-status-item"><div class="luas-dot" style="background:#dc2626;"></div><span class="luas-label">Def. Berat</span><span class="luas-value" id="luas-darurat">0 Ha</span></div>
        <div class="luas-status-item"><div class="luas-dot" style="background:#f97316;"></div><span class="luas-label">Perlu Pupuk</span><span class="luas-value" id="luas-segera">0 Ha</span></div>
        <div class="luas-status-item"><div class="luas-dot" style="background:#22c55e;"></div><span class="luas-label">Sehat</span><span class="luas-value" id="luas-normal">0 Ha</span></div>
        <div class="luas-status-item"><div class="luas-dot" style="background:#94a3b8;"></div><span class="luas-label">Tunda</span><span class="luas-value" id="luas-tunda">0 Ha</span></div>
        <div class="luas-status-item"><div class="luas-dot" style="background:#475569;"></div><span class="luas-label">Belum Dicek</span><span class="luas-value" id="luas-belum">0 Ha</span></div>
    </div>
</div>

{{-- Map Container --}}
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden relative shadow-sm" id="map-container">

    {{-- HEADER BAR --}}
    <div class="px-3 sm:px-4 py-2 sm:py-2.5 border-b border-slate-100" id="map-header">

        {{-- Desktop layout --}}
        <div class="hidden sm:flex items-center gap-2 flex-wrap">
            {{-- Filter status --}}
            <div class="flex items-center gap-1.5" id="status-filter-buttons-desktop">
                <button type="button" class="status-filter-btn active" data-status="Darurat" onclick="toggleStatusFilter(this)">Def. Berat</button>
                <button type="button" class="status-filter-btn active" data-status="Segera" onclick="toggleStatusFilter(this)">Perlu Pupuk</button>
                <button type="button" class="status-filter-btn active" data-status="Normal" onclick="toggleStatusFilter(this)">Sehat</button>
                <button type="button" class="status-filter-btn active" data-status="Tunda" onclick="toggleStatusFilter(this)">Tunda Pupuk</button>
                <button type="button" class="status-filter-btn active" data-status="Belum Dianalisis" onclick="toggleStatusFilter(this)">Belum Dicek</button>
            </div>
            <div class="flex-1"></div>
            {{-- Filters --}}
            <select id="filter-pemilik" class="min-w-[140px] pl-2.5 pr-7 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer appearance-none">
                <option value="">Semua Pemilik</option>
            </select>
            <select id="filter-blok" disabled class="min-w-[130px] pl-2.5 pr-7 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer appearance-none">
                <option value="">Semua Blok</option>
            </select>
            {{-- Perluas / Kecilkan --}}
            <button type="button" onclick="toggleFullscreen()" class="btn-map expand" id="btn-fs-desktop">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span id="btn-fs-desktop-text">Perluas Peta</span>
            </button>
            {{-- Tambah Blok --}}
            <a href="{{ route('blok-lahan.create') }}" class="btn-map tambah">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Blok
            </a>
        </div>

        {{-- Mobile layout --}}
        <div class="sm:hidden space-y-2">
            {{-- Baris 1: Filter status --}}
            <div class="flex flex-wrap items-center gap-1" id="status-filter-buttons-mobile">
                <button type="button" class="status-filter-btn active" data-status="Darurat" onclick="toggleStatusFilter(this)">Def. Berat</button>
                <button type="button" class="status-filter-btn active" data-status="Segera" onclick="toggleStatusFilter(this)">Perlu Pupuk</button>
                <button type="button" class="status-filter-btn active" data-status="Normal" onclick="toggleStatusFilter(this)">Sehat</button>
                <button type="button" class="status-filter-btn active" data-status="Tunda" onclick="toggleStatusFilter(this)">Tunda Pupuk</button>
                <button type="button" class="status-filter-btn active" data-status="Belum Dianalisis" onclick="toggleStatusFilter(this)">Belum Dicek</button>
            </div>
            {{-- Baris 2: Filter pemilik + blok --}}
            <div class="flex items-center gap-2">
                <select id="filter-pemilik-mobile" class="flex-1 pl-2.5 pr-6 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer appearance-none">
                    <option value="">Semua Pemilik</option>
                </select>
                <select id="filter-blok-mobile" disabled class="flex-1 pl-2.5 pr-6 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer appearance-none">
                    <option value="">Semua Blok</option>
                </select>
            </div>
            {{-- Baris 3: Tambah + Perluas/Kecilkan (sama panjang) --}}
            <div class="flex items-center gap-2" id="mobile-btn-row">
                <a href="{{ route('blok-lahan.create') }}" class="btn-map tambah flex-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Blok
                </a>
                <button type="button" onclick="toggleFullscreen()" class="btn-map expand flex-1" id="btn-fs-mobile">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    <span id="btn-fs-mobile-text">Perluas Peta</span>
                </button>
            </div>
        </div>
    </div>

    {{-- PETA --}}
    <div class="p-1.5 sm:p-3 relative">
        <div id="map"></div>
        {{-- Legend --}}
        <div class="map-legend">
            <p class="text-[9px] sm:text-[10px] font-semibold text-slate-600 mb-1">Status Lahan</p>
            <div class="legend-items">
                <div class="legend-item"><div class="legend-dot" style="background:#dc2626;"></div>Def. Berat</div>
                <div class="legend-item"><div class="legend-dot" style="background:#f97316;"></div>Perlu Pupuk</div>
                <div class="legend-item"><div class="legend-dot" style="background:#22c55e;"></div>Sehat</div>
                <div class="legend-item"><div class="legend-dot" style="background:#94a3b8;"></div>Tunda Pupuk</div>
                <div class="legend-item"><div class="legend-dot" style="background:#475569;"></div>Belum Dicek</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

var mapData = @json($mapData);
var activeStatuses = ['Darurat', 'Segera', 'Normal', 'Tunda', 'Belum Dianalisis'];

var map = L.map('map', { center: [-2.5489, 118.0149], zoom: 5, zoomControl: true });
var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' });
osm.addTo(map);
var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri', maxZoom: 19 });
L.control.layers({'Peta': osm, 'Satelit': satellite}).addTo(map);

function getColorRbs(s){return{'Darurat':'#dc2626','Segera':'#f97316','Normal':'#22c55e','Tunda':'#94a3b8','Belum Dianalisis':'#475569'}[s]||'#475569';}
function getBadgeStyleRbs(s){return{'Darurat':'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;','Segera':'background:#ffedd5;color:#9a3412;border:1px solid #fdba74;','Normal':'background:#dcfce7;color:#166534;border:1px solid #86efac;','Tunda':'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;','Belum Dianalisis':'background:#eff6ff;color:#1e40af;border:1px solid #93c5fd;'}[s]||'background:#eff6ff;color:#1e40af;border:1px solid #93c5fd;';}

// Mapping status DB ke label tampilan
function getStatusLabel(s){return{'Darurat':'Defisiensi Berat','Segera':'Perlu Pupuk','Normal':'Sehat','Tunda':'Tunda Pupuk','Belum Dianalisis':'Belum Dicek'}[s]||'Belum Dicek';}

// Populate filters
var selectEl = document.getElementById('filter-pemilik');
var selectElMobile = document.getElementById('filter-pemilik-mobile');
var filterBlokEl = document.getElementById('filter-blok');
var filterBlokElMobile = document.getElementById('filter-blok-mobile');

var pemilikSet={},pemilikList=[];
mapData.forEach(function(b){if(b.nama_pemilik&&!pemilikSet[b.nama_pemilik]){pemilikSet[b.nama_pemilik]=true;pemilikList.push(b.nama_pemilik);}});
pemilikList.sort();
pemilikList.forEach(function(p){
    var o1=document.createElement('option');o1.value=p;o1.textContent=p;selectEl.appendChild(o1);
    var o2=document.createElement('option');o2.value=p;o2.textContent=p;selectElMobile.appendChild(o2);
});

var mapLayers=[];

function updateStats(data){
    var t=0,l=0,a=0,d=0,s=0;
    data.forEach(function(b){t++;l+=(b.luas_ha||0);if(b.status_rbs&&b.status_rbs!=='Belum Dianalisis')a++;if(b.status_rbs==='Darurat')d++;if(b.status_rbs==='Segera')s++;});
    document.getElementById('stat-total-blok').textContent=t;
    document.getElementById('stat-total-luas').textContent=l.toFixed(2);
    document.getElementById('stat-sudah-analisis').textContent=a;
    document.getElementById('stat-darurat').textContent=d;
    document.getElementById('stat-segera').textContent=s;
}

function updateLuasPerStatus(data){
    var r={Darurat:0,Segera:0,Normal:0,Tunda:0,Belum:0};
    data.forEach(function(b){var s=b.status_rbs||'Belum Dianalisis';var h=b.luas_ha||0;if(s==='Darurat')r.Darurat+=h;else if(s==='Segera')r.Segera+=h;else if(s==='Normal')r.Normal+=h;else if(s==='Tunda')r.Tunda+=h;else r.Belum+=h;});
    document.getElementById('luas-darurat').textContent=r.Darurat.toFixed(2)+' Ha';
    document.getElementById('luas-segera').textContent=r.Segera.toFixed(2)+' Ha';
    document.getElementById('luas-normal').textContent=r.Normal.toFixed(2)+' Ha';
    document.getElementById('luas-tunda').textContent=r.Tunda.toFixed(2)+' Ha';
    document.getElementById('luas-belum').textContent=r.Belum.toFixed(2)+' Ha';
}

function buildPopupContent(blok){
    var statusRbs=blok.status_rbs||'Belum Dianalisis',masalahRbs=blok.masalah_rbs||[],pupukRbs=blok.pupuk_rbs||[],saranRbs=blok.saran_rbs||'',tglRbs=blok.tgl_analisis_rbs||'-',jumlahRule=blok.jumlah_rule||0;
    var statusLabel=getStatusLabel(statusRbs);
    var bs=getBadgeStyleRbs(statusRbs);
    var mh=masalahRbs.length?masalahRbs.slice(0,3).map(function(m){return'<span style="font-size:10px;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1px 5px;display:inline-block;margin:1px 2px 1px 0;">'+m+'</span>';}).join('')+(masalahRbs.length>3?'<span style="font-size:9px;color:#9ca3af;"> +'+(masalahRbs.length-3)+'</span>':''):'<span style="font-size:10px;color:#9ca3af;">Tidak ada masalah</span>';
    var ph=pupukRbs.length?pupukRbs.slice(0,2).map(function(p){return'<div style="font-size:10px;color:#15803d;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;padding:2px 5px;margin-top:2px;">'+p.jenis_utama+(p.dosis?' — '+p.dosis:'')+'</div>';}).join(''):'';
    var sh=saranRbs?'<div style="font-size:9px;color:#78350f;background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:2px 5px;margin-top:3px;line-height:1.3;">'+saranRbs.substring(0,70)+(saranRbs.length>70?'...':'')+'</div>':'';
    return'<div style="min-width:170px;max-width:220px;font-family:system-ui,sans-serif;"><div style="font-weight:700;font-size:12px;color:#0f172a;padding-bottom:4px;border-bottom:1px solid #f1f5f9;margin-bottom:4px;">'+blok.nama_blok+'</div><div style="font-size:10px;color:#64748b;margin-bottom:3px;">'+(blok.nama_pemilik||'-')+' \u00B7 '+blok.luas_ha+' Ha'+(blok.umur_tanaman!==null?' \u00B7 '+blok.umur_tanaman+' thn':'')+'</div><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;"><span style="font-size:9px;font-weight:700;color:#6b7280;text-transform:uppercase;">Status</span><span style="'+bs+'font-size:10px;font-weight:700;padding:1px 6px;border-radius:9999px;">'+statusLabel+'</span></div><div style="margin-bottom:3px;">'+mh+'</div>'+ph+sh+'<div style="display:flex;justify-content:space-between;align-items:center;padding-top:4px;margin-top:3px;border-top:1px solid #f1f5f9;"><span style="font-size:9px;color:#9ca3af;">'+jumlahRule+' rule \u00B7 '+tglRbs+'</span><a href="/rbs/detail/'+blok.id+'" style="font-size:10px;color:#059669;font-weight:700;text-decoration:none;">Detail \u2192</a></div></div>';
}

function getSelectedPemilik(){return window.innerWidth<640?selectElMobile.value:selectEl.value;}
function getSelectedBlok(){return window.innerWidth<640?filterBlokElMobile.value:filterBlokEl.value;}

function getFilteredData(){
    var pemilik=getSelectedPemilik(),blokId=getSelectedBlok(),data=mapData;
    if(pemilik)data=data.filter(function(b){return b.nama_pemilik===pemilik;});
    if(blokId)data=data.filter(function(b){return b.id==blokId;});
    data=data.filter(function(b){return activeStatuses.indexOf(b.status_rbs||'Belum Dianalisis')!==-1;});
    return data;
}

function renderMapLayers(){
    mapLayers.forEach(function(item){map.removeLayer(item.layer);});
    mapLayers=[];
    var filteredData=getFilteredData();
    var pemilik=getSelectedPemilik(),blokId=getSelectedBlok();
    var statsData=mapData;
    if(pemilik)statsData=statsData.filter(function(b){return b.nama_pemilik===pemilik;});
    if(blokId)statsData=statsData.filter(function(b){return b.id==blokId;});
    updateStats(statsData);
    updateLuasPerStatus(statsData);
    var activeLayers=[];
    filteredData.forEach(function(blok){
        if(!blok.geojson)return;
        var color=getColorRbs(blok.status_rbs||'Belum Dianalisis');
        var layer=L.geoJSON(blok.geojson,{style:{fillColor:color,fillOpacity:0.45,color:color,weight:2,opacity:0.9}});
        layer.bindPopup(buildPopupContent(blok),{maxWidth:230,autoPanPaddingTopLeft:[10,10],autoPanPaddingBottomRight:[10,50]});
        layer.bindTooltip(blok.nama_blok,{permanent:true,direction:'center',className:'leaflet-tooltip-label'});
        layer.on('mouseover',function(e){e.target.setStyle({fillOpacity:0.7,weight:3});});
        layer.on('mouseout',function(e){e.target.setStyle({fillOpacity:0.45,weight:2});});
        layer.addTo(map);
        mapLayers.push({id:blok.id,layer:layer});
        activeLayers.push(layer);
    });
    if(activeLayers.length>0)map.fitBounds(L.featureGroup(activeLayers).getBounds().pad(0.1));
}

renderMapLayers();

// ─── FILTER STATUS (sync desktop + mobile buttons) ───────────────
function toggleStatusFilter(btn){
    var status=btn.getAttribute('data-status');
    var idx=activeStatuses.indexOf(status);
    if(idx!==-1){if(activeStatuses.length<=1)return;activeStatuses.splice(idx,1);}
    else{activeStatuses.push(status);}
    // Sync all buttons with same data-status
    document.querySelectorAll('.status-filter-btn[data-status="'+status+'"]').forEach(function(b){
        if(activeStatuses.indexOf(status)!==-1){b.classList.remove('inactive');b.classList.add('active');}
        else{b.classList.remove('active');b.classList.add('inactive');}
    });
    renderMapLayers();
}

// ─── FILTER PEMILIK + BLOK (sync desktop & mobile) ───────────────
function handlePemilikChange(pemilik,blokSelect){
    blokSelect.innerHTML='<option value="">Semua Blok</option>';
    if(pemilik){
        blokSelect.disabled=false;
        mapData.filter(function(b){return b.nama_pemilik===pemilik;}).forEach(function(b){var o=document.createElement('option');o.value=b.id;o.textContent=b.nama_blok;blokSelect.appendChild(o);});
    }else{blokSelect.disabled=true;}
    renderMapLayers();
}

selectEl.addEventListener('change',function(){
    selectElMobile.value=selectEl.value;
    handlePemilikChange(selectEl.value,filterBlokEl);
    filterBlokElMobile.innerHTML=filterBlokEl.innerHTML;
    filterBlokElMobile.disabled=filterBlokEl.disabled;
});
filterBlokEl.addEventListener('change',function(){filterBlokElMobile.value=filterBlokEl.value;renderMapLayers();});
selectElMobile.addEventListener('change',function(){
    selectEl.value=selectElMobile.value;
    handlePemilikChange(selectElMobile.value,filterBlokElMobile);
    filterBlokEl.innerHTML=filterBlokElMobile.innerHTML;
    filterBlokEl.disabled=filterBlokElMobile.disabled;
});
filterBlokElMobile.addEventListener('change',function(){filterBlokEl.value=filterBlokElMobile.value;renderMapLayers();});

// ─── FULLSCREEN ──────────────────────────────────────────────────
var isFullscreen=false;
var btnFsDesktop=document.getElementById('btn-fs-desktop');
var btnFsDesktopText=document.getElementById('btn-fs-desktop-text');
var btnFsMobile=document.getElementById('btn-fs-mobile');
var btnFsMobileText=document.getElementById('btn-fs-mobile-text');

function toggleFullscreen(){
    var container=document.getElementById('map-container');
    var mapEl=document.getElementById('map');
    var sidebar=document.getElementById('sidebar');
    var header=document.getElementById('map-header');
    isFullscreen=!isFullscreen;

    if(isFullscreen){
        container.style.position='fixed';
        container.style.inset='0';
        container.style.zIndex='8000';
        container.style.borderRadius='0';
        container.style.margin='0';
        var hH=header.offsetHeight;
        mapEl.style.height='calc(100vh - '+(hH+8)+'px)';
        mapEl.style.minHeight='unset';
        mapEl.style.borderRadius='0';
        document.body.style.overflow='hidden';
        if(sidebar)sidebar.style.display='none';
        // Switch button to "Kecilkan"
        btnFsDesktop.classList.remove('expand');
        btnFsDesktop.classList.add('shrink');
        btnFsDesktopText.textContent='Kecilkan';
        btnFsMobile.classList.remove('expand');
        btnFsMobile.classList.add('shrink');
        btnFsMobileText.textContent='Kecilkan';
    }else{
        container.style.position='';
        container.style.inset='';
        container.style.zIndex='';
        container.style.borderRadius='';
        container.style.margin='';
        mapEl.style.height='';
        mapEl.style.minHeight='';
        mapEl.style.borderRadius='';
        document.body.style.overflow='';
        if(sidebar)sidebar.style.display='';
        // Switch button back to "Perluas Peta"
        btnFsDesktop.classList.remove('shrink');
        btnFsDesktop.classList.add('expand');
        btnFsDesktopText.textContent='Perluas Peta';
        btnFsMobile.classList.remove('shrink');
        btnFsMobile.classList.add('expand');
        btnFsMobileText.textContent='Perluas Peta';
    }
    setTimeout(function(){map.invalidateSize();},200);
}

document.addEventListener('keydown',function(e){if(e.key==='Escape'&&isFullscreen)toggleFullscreen();});
window.addEventListener('resize',function(){map.invalidateSize();});
window.addEventListener('orientationchange',function(){setTimeout(function(){map.invalidateSize();},400);});
document.addEventListener('sidebarToggled',function(){map.invalidateSize();});
</script>
@endpush
