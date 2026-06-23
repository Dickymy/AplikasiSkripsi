@extends('layouts.app')

@section('title', 'Edit Blok Lahan')
@section('page-title', 'Edit Blok Lahan')
@section('page-subtitle', 'Perbarui data: {{ $blokLahan->nama_blok }}')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<style>
    /* ─── MAP WRAPPER — Mode Normal ─── */
    .map-wrapper {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    #draw-map {
        height: 450px;
        width: 100%;
        z-index: 1;
    }
    @media (max-width: 640px) { #draw-map { height: 300px; } }

    /* Tombol Perluas - centered, prominent */
    #btn-expand {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.92);
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        backdrop-filter: blur(6px);
        transition: opacity 0.3s ease, transform 0.3s ease, background 0.15s;
    }
    #btn-expand:hover { background: #fff; border-color: #059669; color: #059669; }
    #btn-expand.is-hidden { opacity: 0; pointer-events: none; transform: translate(-50%, -50%) scale(0.9); }
    @media (max-width: 640px) { #btn-expand { padding: 6px 12px; font-size: 11px; gap: 4px; } }

    /* Bar atas fullscreen */
    .map-top-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(6px);
        padding: 6px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .map-top-bar.hidden { display: none; }
    .map-info-luas {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #374151;
    }
    .map-info-luas .icon-ha { width: 18px; height: 18px; color: #16a34a; }
    .map-info-luas strong { font-size: 18px; font-weight: 700; color: #16a34a; }
    #btn-kecilkan {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 500;
        color: #dc2626;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s;
        white-space: nowrap;
    }
    #btn-kecilkan:hover { background: #fecaca; }

    /* ─── MAP WRAPPER — Mode Fullscreen ─── */
    .map-wrapper.is-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9100 !important;
        border-radius: 0 !important;
        border: none !important;
        overflow: hidden !important;
    }
    .map-wrapper.is-fullscreen #draw-map {
        height: 100vh !important;
        margin-top: 0 !important;
    }
    .map-wrapper.is-fullscreen .leaflet-top {
        top: 54px !important;
    }
    .map-wrapper.is-fullscreen #btn-expand {
        display: none !important;
    }
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .map-wrapper.is-fullscreen #draw-map {
            padding-bottom: env(safe-area-inset-bottom);
        }
    }
    @media (max-width: 640px) {
        .map-top-bar { padding: 6px 10px; }
        .map-info-luas strong { font-size: 16px; }
        #btn-kecilkan { font-size: 12px; padding: 5px 9px; }
    }

    /* Hide default Leaflet zoom control */
    .leaflet-control-zoom { display: none !important; }

    /* Custom Zoom Slider */
    .zoom-slider-container {
        position: absolute;
        bottom: 16px;
        right: 16px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        background: rgba(255,255,255,0.96);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.10);
        backdrop-filter: blur(6px);
    }
    .zoom-slider-container button {
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        color: #374151;
        font-size: 16px;
        font-weight: 700;
        transition: background 0.15s, color 0.15s;
        user-select: none;
        line-height: 1;
    }
    .zoom-slider-container button:hover { background: #f0fdf4; color: #059669; }
    .zoom-slider-container button:active { background: #dcfce7; }
    .zoom-slider-container input[type="range"] {
        -webkit-appearance: none;
        appearance: none;
        width: 4px;
        height: 90px;
        background: #e2e8f0;
        border-radius: 4px;
        outline: none;
        writing-mode: vertical-lr;
        direction: rtl;
        margin: 4px 0;
        cursor: pointer;
    }
    .zoom-slider-container input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: #059669;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        cursor: pointer;
        transition: transform 0.1s;
    }
    .zoom-slider-container input[type="range"]::-webkit-slider-thumb:hover { transform: scale(1.2); }
    .zoom-slider-container input[type="range"]::-moz-range-thumb {
        width: 14px;
        height: 14px;
        background: #059669;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        cursor: pointer;
    }
    .zoom-slider-container input[type="range"]::-moz-range-track {
        width: 4px;
        background: #e2e8f0;
        border-radius: 4px;
    }
    @media (max-width: 640px) {
        .zoom-slider-container { bottom: 10px; right: 10px; padding: 4px 4px; }
        .zoom-slider-container button { width: 24px; height: 24px; font-size: 14px; }
        .zoom-slider-container input[type="range"] { height: 60px; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('blok-lahan.update', $blokLahan) }}" class="space-y-5" id="form-blok-lahan">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    @include('components.searchable-select', [
                        'name' => 'anggota_id',
                        'label' => 'Pemilik Lahan',
                        'placeholder' => 'Cari nama anggota...',
                        'options' => $anggotas,
                        'displayField' => 'nama',
                        'selected' => old('anggota_id', $blokLahan->anggota_id),
                        'required' => true,
                        'error' => $errors->first('anggota_id'),
                    ])
                </div>
                <div>
                    <label for="nama_blok" class="block text-sm font-medium text-slate-700 mb-2">Nama Blok <span class="text-red-400">*</span></label>
                    <input type="text" id="nama_blok" name="nama_blok" value="{{ old('nama_blok', $blokLahan->nama_blok) }}" required
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('nama_blok') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('nama_blok') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sph" class="block text-sm font-medium text-slate-700 mb-2">SPH <span class="text-red-400">*</span></label>
                    <input type="number" id="sph" name="sph" value="{{ old('sph', $blokLahan->sph) }}" min="1" required
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('sph') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('sph') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div></div>
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
                        <input type="number" id="tahun_tanam" name="tahun_tanam" value="{{ old('tahun_tanam', $blokLahan->tahun_tanam) }}" min="1990" max="{{ now()->year }}" required
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('tahun_tanam') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        @error('tahun_tanam') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jenis_tanah" class="block text-sm font-medium text-slate-700 mb-2">Jenis Tanah <span class="text-red-400">*</span></label>
                        <select id="jenis_tanah" name="jenis_tanah" required
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('jenis_tanah') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            @foreach(['Tanah Lempung','Tanah Lempung Berpasir','Tanah Berpasir','Tanah Liat','Tanah Gambut','Tanah Aluvial','Tanah Podsolik Merah Kuning (PMK)','Tanah Laterit','Tanah Berbatu','Lainnya'] as $jt)
                                <option value="{{ $jt }}" {{ old('jenis_tanah', $blokLahan->jenis_tanah) == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                            @endforeach
                        </select>
                        @error('jenis_tanah') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="topografi" class="block text-sm font-medium text-slate-700 mb-2">Topografi <span class="text-red-400">*</span></label>
                        <select id="topografi" name="topografi" required
                            class="w-full px-4 py-3 bg-white border {{ $errors->has('topografi') ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            @foreach(['Datar 0-15°','Bergelombang 15-30°','Curam >30°'] as $tp)
                                <option value="{{ $tp }}" {{ old('topografi', $blokLahan->topografi) == $tp ? 'selected' : '' }}>{{ $tp }}</option>
                            @endforeach
                        </select>
                        @error('topografi') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Koordinat GeoJSON --}}
            <div id="koordinat">
                <label class="block text-sm font-medium text-slate-700 mb-2">Koordinat Blok Lahan <span class="text-red-400">*</span></label>

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

                <div id="panel-draw" class="space-y-2">
                    <div class="map-wrapper" id="draw-map-wrapper">
                        {{-- Top bar fullscreen (hidden by default) --}}
                        <div id="map-top-bar" class="map-top-bar hidden">
                            <div class="map-info-luas">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-ha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                                </svg>
                                <span>Luas: </span>
                                <strong id="luas-fullscreen">0.00</strong>
                                <span> ha</span>
                            </div>
                            <button type="button" id="btn-kecilkan" onclick="kecilkanPeta()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="4 14 10 14 10 20"/><polyline points="20 10 14 10 14 4"/>
                                    <line x1="10" y1="14" x2="3" y2="21"/><line x1="21" y1="3" x2="14" y2="10"/>
                                </svg>
                                Kecilkan Peta
                            </button>
                        </div>
                        {{-- Peta Leaflet --}}
                        <div id="draw-map"></div>
                        {{-- Zoom Slider --}}
                        <div class="zoom-slider-container" id="zoom-slider-container">
                            <button type="button" id="zoom-in-btn" title="Zoom In">+</button>
                            <input type="range" id="zoom-slider" min="1" max="19" step="0.1" value="10" orient="vertical" title="Zoom Level">
                            <button type="button" id="zoom-out-btn" title="Zoom Out">−</button>
                        </div>
                        {{-- Tombol perluas (mode normal) - centered --}}
                        <button type="button" id="btn-expand" onclick="perluasPeta()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            Perluas Peta
                        </button>
                    </div>

                    {{-- Panduan Interaktif Alat Peta --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mt-2">
                        <p class="text-xs font-semibold text-slate-700 mb-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Panduan Alat Peta
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <div class="flex items-start gap-2 p-2 bg-white rounded-lg border border-slate-100">
                                <div class="w-6 h-6 bg-emerald-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-700">Gambar Polygon</p>
                                    <p class="text-[10px] text-slate-400 leading-tight">Klik titik-titik batas lahan</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 p-2 bg-white rounded-lg border border-slate-100">
                                <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-700">Kotak (Rectangle)</p>
                                    <p class="text-[10px] text-slate-400 leading-tight">Drag untuk area persegi</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 p-2 bg-white rounded-lg border border-slate-100">
                                <div class="w-6 h-6 bg-amber-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-700">Edit Titik</p>
                                    <p class="text-[10px] text-slate-400 leading-tight">Geser titik polygon</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 p-2 bg-white rounded-lg border border-slate-100">
                                <div class="w-6 h-6 bg-red-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-700">Hapus Polygon</p>
                                    <p class="text-[10px] text-slate-400 leading-tight">Klik polygon lalu hapus</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2">💡 Area <span class="text-amber-600 font-semibold">kuning</span> = lahan milik anggota lain. Gunakan edit (pensil) untuk geser titik.</p>
                    </div>
                </div>

                <div id="panel-json" class="hidden">
                    <textarea id="textarea_geojson" rows="8"
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-mono text-xs leading-relaxed resize-y">{{ old('koordinat_geojson', $blokLahan->koordinat_geojson) }}</textarea>
                </div>

                <input type="hidden" name="koordinat_geojson" id="koordinat_geojson" value="{{ old('koordinat_geojson', $blokLahan->koordinat_geojson) }}">
                @error('koordinat_geojson') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror

                {{-- Luas Lahan — dekat peta --}}
                <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Luas Lahan</p>
                        <p class="text-xs text-emerald-600" id="luas-info">✓ {{ $blokLahan->luas_ha }} Ha</p>
                    </div>
                    <div class="text-right">
                        <input type="number" id="luas_ha" name="luas_ha" value="{{ old('luas_ha', $blokLahan->luas_ha) }}" step="0.01" min="0.01" required readonly
                            class="w-24 px-3 py-2 bg-white border border-emerald-300 rounded-lg text-sm text-emerald-800 font-bold text-right cursor-not-allowed">
                        <p class="text-xs text-emerald-600 mt-0.5">Hektar</p>
                    </div>
                </div>
                @error('luas_ha') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Overlap Warning (Fitur 5) --}}
            <div id="overlap-warning" class="hidden bg-amber-50 border border-amber-300 rounded-xl p-4">
                <p class="text-sm text-amber-800 font-semibold mb-2" id="overlap-message"></p>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" id="overlap-confirm" class="mt-0.5 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                    <span class="text-xs text-amber-700">Saya memahami bahwa polygon ini bertumpuk dengan blok lain dan tetap ingin menyimpan.</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20">
                    Perbarui Data
                </button>
                <a href="{{ route('blok-lahan.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors">
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

var drawMap = L.map('draw-map', { center: [-1.5, 110.0], zoom: 10, zoomControl: false, zoomSnap: 0, zoomDelta: 0.25, wheelDebounceTime: 40, wheelPxPerZoomLevel: 120 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(drawMap);
var satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, maxNativeZoom: 17 });
L.control.layers({'Peta': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}), 'Satelit': satLayer}).addTo(drawMap);

// ─── ZOOM SLIDER (smooth continuous zoom on hold) ────────────────
(function(){
    var slider = document.getElementById('zoom-slider');
    var zoomInBtn = document.getElementById('zoom-in-btn');
    var zoomOutBtn = document.getElementById('zoom-out-btn');
    var isSliderDragging = false;
    var animFrameId = null;
    var zoomSpeed = 0.02;

    slider.min = drawMap.getMinZoom() || 1;
    slider.max = drawMap.getMaxZoom() || 19;
    slider.step = '0.1';
    slider.value = drawMap.getZoom();

    slider.addEventListener('input', function() {
        isSliderDragging = true;
        drawMap.setZoom(parseFloat(this.value));
    });
    slider.addEventListener('change', function() { isSliderDragging = false; });
    slider.addEventListener('pointerup', function() { isSliderDragging = false; });

    drawMap.on('zoomend zoom move', function() {
        if (!isSliderDragging) slider.value = drawMap.getZoom();
    });

    function startContinuousZoom(direction) {
        stopContinuousZoom();
        function frame() {
            var current = drawMap.getZoom();
            var next = current + (direction * zoomSpeed);
            var minZ = parseFloat(slider.min);
            var maxZ = parseFloat(slider.max);
            if (next < minZ) next = minZ;
            if (next > maxZ) next = maxZ;
            if ((direction > 0 && current < maxZ) || (direction < 0 && current > minZ)) {
                drawMap.setZoom(next);
                animFrameId = requestAnimationFrame(frame);
            }
        }
        animFrameId = requestAnimationFrame(frame);
    }

    function stopContinuousZoom() {
        if (animFrameId) { cancelAnimationFrame(animFrameId); animFrameId = null; }
    }

    zoomInBtn.addEventListener('mousedown', function(e) { e.preventDefault(); startContinuousZoom(1); });
    zoomOutBtn.addEventListener('mousedown', function(e) { e.preventDefault(); startContinuousZoom(-1); });
    document.addEventListener('mouseup', stopContinuousZoom);

    zoomInBtn.addEventListener('touchstart', function(e) { e.preventDefault(); startContinuousZoom(1); }, {passive:false});
    zoomOutBtn.addEventListener('touchstart', function(e) { e.preventDefault(); startContinuousZoom(-1); }, {passive:false});
    document.addEventListener('touchend', stopContinuousZoom);
    document.addEventListener('touchcancel', stopContinuousZoom);

    zoomInBtn.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    zoomOutBtn.addEventListener('contextmenu', function(e) { e.preventDefault(); });
})();

// Existing polygons
existingBloks.forEach(function(blok) {
    if (!blok.geojson) return;
    L.geoJSON(blok.geojson, {
        style: { color: '#d97706', fillColor: '#fbbf24', fillOpacity: 0.25, weight: 1.5, dashArray: '4 4' }
    }).bindTooltip(blok.nama, { sticky: true }).addTo(drawMap);
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

// Auto-sync saat layer digeser/diubah (tanpa perlu klik Save di toolbar)
drawMap.on('draw:editvertex', syncGeoJson);
drawMap.on('draw:editmove', syncGeoJson);

// Warning jika user submit saat mode edit masih aktif
var isEditingPolygon = false;
drawMap.on('draw:editstart', function() { isEditingPolygon = true; });
drawMap.on('draw:editstop', function() { isEditingPolygon = false; });

document.getElementById('form-blok-lahan').addEventListener('submit', function(e) {
    // Jika sedang mode edit, force sync dulu sebelum submit
    if (isEditingPolygon) {
        syncGeoJson();
    }
    if (currentTab === 'json') document.getElementById('koordinat_geojson').value = document.getElementById('textarea_geojson').value.trim();
});

function calculateAreaHa(geojson) {
    try {
        var coords;
        if (geojson.type === 'Polygon') coords = geojson.coordinates[0];
        else if (geojson.type === 'Feature' && geojson.geometry.type === 'Polygon') coords = geojson.geometry.coordinates[0];
        else return 0;
        var area = 0, n = coords.length;
        for (var i = 0; i < n - 1; i++) {
            var j = (i + 1) % n;
            area += (coords[j][0] * Math.PI / 180 - coords[i][0] * Math.PI / 180) * (2 + Math.sin(coords[i][1] * Math.PI / 180) + Math.sin(coords[j][1] * Math.PI / 180));
        }
        area = Math.abs(area * 6378137 * 6378137 / 2);
        return Math.round(area / 10000 * 100) / 100;
    } catch(e) { return 0; }
}

function updateLuas(geojson) {
    var ha = calculateAreaHa(geojson);
    var luasEl = document.getElementById('luas_ha');
    var infoEl = document.getElementById('luas-info');
    if (ha > 0) {
        luasEl.value = ha;
        infoEl.textContent = '✓ Luas: ' + ha + ' Ha';
        infoEl.className = 'mt-1 text-xs text-emerald-600 font-medium';
    } else {
        luasEl.value = '';
        infoEl.textContent = 'Gambar polygon untuk menghitung luas';
        infoEl.className = 'mt-1 text-xs text-slate-400';
    }
    syncLuasFullscreen();
}

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

// Load existing polygon
var existingGeojson = document.getElementById('koordinat_geojson').value;
if (existingGeojson) {
    try {
        var parsed = JSON.parse(existingGeojson);
        L.geoJSON(parsed, { style: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } })
            .eachLayer(function(l) { drawnItems.addLayer(l); });
        drawMap.fitBounds(drawnItems.getBounds().pad(0.2));
        updateLuas(parsed);
    } catch(e) {}
}

// Textarea blur → recalculate
document.getElementById('textarea_geojson').addEventListener('blur', function() {
    var val = this.value.trim();
    if (!val) { updateLuas({}); return; }
    try {
        var parsed = JSON.parse(val);
        document.getElementById('koordinat_geojson').value = val;
        updateLuas(parsed);
        drawnItems.clearLayers();
        L.geoJSON(parsed, { style: { color: '#059669', fillColor: '#059669', fillOpacity: 0.3, weight: 2 } })
            .eachLayer(function(l) { drawnItems.addLayer(l); });
        drawMap.fitBounds(drawnItems.getBounds().pad(0.2));
    } catch(e) { updateLuas({}); }
});

// ─── FULLSCREEN PETA DRAW ────────────────────────────────────────
var expandBtn = document.getElementById('btn-expand');
var drawDragTimer = null;

function perluasPeta() {
    var wrapper = document.getElementById('draw-map-wrapper');
    var topBar = document.getElementById('map-top-bar');
    wrapper.classList.add('is-fullscreen');
    topBar.classList.remove('hidden');
    syncLuasFullscreen();
    setTimeout(function() { drawMap.invalidateSize(); }, 150);
    document.body.style.overflow = 'hidden';
}

function kecilkanPeta() {
    var wrapper = document.getElementById('draw-map-wrapper');
    var topBar = document.getElementById('map-top-bar');
    wrapper.classList.remove('is-fullscreen');
    topBar.classList.add('hidden');
    setTimeout(function() { drawMap.invalidateSize(); }, 150);
    document.body.style.overflow = '';
}

// Auto-hide perluas button saat drag/zoom peta
function hideExpandBtn() {
    if (expandBtn && !document.getElementById('draw-map-wrapper').classList.contains('is-fullscreen')) {
        expandBtn.classList.add('is-hidden');
    }
}
function showExpandBtn() {
    if (expandBtn && !document.getElementById('draw-map-wrapper').classList.contains('is-fullscreen')) {
        expandBtn.classList.remove('is-hidden');
    }
}

drawMap.on('movestart', function() { hideExpandBtn(); clearTimeout(drawDragTimer); });
drawMap.on('zoomstart', function() { hideExpandBtn(); clearTimeout(drawDragTimer); });
drawMap.on('moveend', function() { clearTimeout(drawDragTimer); drawDragTimer = setTimeout(showExpandBtn, 1200); });
drawMap.on('zoomend', function() { clearTimeout(drawDragTimer); drawDragTimer = setTimeout(showExpandBtn, 1200); });

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var wrapper = document.getElementById('draw-map-wrapper');
        if (wrapper && wrapper.classList.contains('is-fullscreen')) {
            kecilkanPeta();
        }
    }
});

function syncLuasFullscreen() {
    var inputLuas = document.getElementById('luas_ha');
    var luasFullscreen = document.getElementById('luas-fullscreen');
    if (inputLuas && luasFullscreen) {
        var nilai = parseFloat(inputLuas.value) || 0;
        luasFullscreen.textContent = nilai.toFixed(2);
    }
}

// Listen for sidebar toggle
document.addEventListener('sidebarToggled', function() {
    if (typeof drawMap !== 'undefined') {
        drawMap.invalidateSize();
    }
});

// ─── FITUR 5: DETEKSI OVERLAP POLYGON (EDIT) ────────────────────────
(function() {
    var overlapWarning = document.getElementById('overlap-warning');
    var overlapCheckbox = document.getElementById('overlap-confirm');
    var submitBtn = document.querySelector('button[type="submit"]');

    function checkOverlapOnChange() {
        var geojsonInput = document.getElementById('koordinat_geojson');
        if (!geojsonInput || !geojsonInput.value) return;
        try {
            var newGeojson = JSON.parse(geojsonInput.value);
            detectOverlap(newGeojson);
        } catch(e) {}
    }

    function detectOverlap(newGeojson) {
        if (!overlapWarning) return;
        var overlaps = [];

        existingBloks.forEach(function(blok) {
            if (!blok.geojson) return;
            try {
                if (polygonsIntersect(newGeojson, blok.geojson)) {
                    overlaps.push(blok.nama);
                }
            } catch(e) {}
        });

        if (overlaps.length > 0) {
            var msg = 'Peringatan: Area blok yang digambar bertumpuk dengan ' + overlaps.join(', ') + '. Silakan sesuaikan polygon agar tidak menimpa blok lain.';
            document.getElementById('overlap-message').textContent = msg;
            overlapWarning.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = true;
        } else {
            overlapWarning.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    function polygonsIntersect(geojsonA, geojsonB) {
        var coordsA = getCoords(geojsonA);
        var coordsB = getCoords(geojsonB);
        if (!coordsA || !coordsB) return false;
        for (var i = 0; i < coordsA.length - 1; i++) {
            if (pointInPolygon(coordsA[i], coordsB)) return true;
        }
        for (var j = 0; j < coordsB.length - 1; j++) {
            if (pointInPolygon(coordsB[j], coordsA)) return true;
        }
        return false;
    }

    function getCoords(geojson) {
        if (geojson.type === 'Polygon') return geojson.coordinates[0];
        if (geojson.type === 'Feature' && geojson.geometry) return geojson.geometry.coordinates[0];
        if (geojson.type === 'FeatureCollection' && geojson.features && geojson.features.length > 0) {
            return geojson.features[0].geometry.coordinates[0];
        }
        return null;
    }

    function pointInPolygon(point, polygon) {
        var x = point[0], y = point[1];
        var inside = false;
        for (var i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            var xi = polygon[i][0], yi = polygon[i][1];
            var xj = polygon[j][0], yj = polygon[j][1];
            var intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    var geojsonInput = document.getElementById('koordinat_geojson');
    if (geojsonInput) {
        geojsonInput.addEventListener('change', checkOverlapOnChange);
        geojsonInput.addEventListener('input', checkOverlapOnChange);
    }

    if (overlapCheckbox) {
        overlapCheckbox.addEventListener('change', function() {
            if (submitBtn) submitBtn.disabled = !this.checked && !overlapWarning.classList.contains('hidden');
        });
    }

    if (typeof drawnItems !== 'undefined') {
        drawMap.on(L.Draw.Event.CREATED, function() { setTimeout(checkOverlapOnChange, 200); });
        drawMap.on(L.Draw.Event.EDITED, function() { setTimeout(checkOverlapOnChange, 200); });
    }
})();
</script>
@endpush
