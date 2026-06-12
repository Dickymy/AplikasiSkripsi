@extends('layouts.app')

@section('title', 'Input Kondisi Lahan')
@section('page-title', 'Input Kondisi Lahan')
@section('page-subtitle', 'Data observasi visual tanaman & lingkungan untuk analisis RBS')

@section('content')

<div class="w-full max-w-4xl mx-auto overflow-hidden">

    <form action="{{ route('kondisi-lahan.store') }}" method="POST" class="space-y-4 sm:space-y-6">
        @csrf

        {{-- SEKSI 1: Identifikasi Blok --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <h2 class="text-sm sm:text-base font-semibold text-slate-800 mb-4 flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">1</span>
                Identifikasi Blok Lahan
            </h2>
            <div class="grid grid-cols-1 gap-4">
                {{-- Row 1: Pemilik + Blok --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Pilih Pemilik (searchable) --}}
                    <div>
                        @include('components.searchable-select', [
                            'name' => '_anggota_filter',
                            'label' => 'Pemilik Lahan',
                            'placeholder' => 'Cari nama pemilik...',
                            'options' => $anggotas,
                            'displayField' => 'nama',
                            'selected' => old('_anggota_filter', $selectedBlokId ? ($bloks->firstWhere('id', $selectedBlokId)?->anggota_id) : ''),
                            'required' => false,
                            'error' => null,
                            'helpText' => 'Pilih pemilik untuk memfilter blok lahan di bawah',
                        ])
                    </div>

                    {{-- Tanggal Observasi --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Tanggal Observasi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_observasi"
                            value="{{ old('tanggal_observasi', now()->format('Y-m-d')) }}"
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('tanggal_observasi') border-red-400 @enderror"
                            required>
                        @error('tanggal_observasi')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: Tanggal Pemupukan Terakhir --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Tanggal Pemupukan Terakhir <span class="text-xs text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input type="date" name="tanggal_pemupukan_terakhir"
                            value="{{ old('tanggal_pemupukan_terakhir') }}"
                            max="{{ now()->format('Y-m-d') }}"
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <p class="mt-1 text-xs text-slate-400">Mempengaruhi koreksi dosis: &lt;60 hari → kurangi 25%, &gt;120 hari → tambah 25%</p>
                    </div>
                    <div></div>
                </div>

                {{-- Row 2: Pilih Blok (muncul setelah pilih pemilik) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Blok Lahan <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="blok_lahan_id" id="blok-lahan-id-value" value="{{ old('blok_lahan_id', $selectedBlokId) }}">

                    <div id="blok-list-container">
                        <p id="blok-hint" class="text-xs text-slate-400 py-2">Pilih pemilik lahan terlebih dahulu untuk menampilkan daftar blok.</p>
                        <div id="blok-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 hidden"></div>
                    </div>
                    @error('blok_lahan_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- SEKSI 2: Kondisi Tanah --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1 flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">2</span>
                Kondisi Tanah
            </h2>
            <p class="text-xs text-slate-400 mb-4 ml-0 sm:ml-8">Data keasaman dan kelembaban tanah untuk menentukan efektivitas penyerapan pupuk.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">pH Tanah</label>
                    <input type="number" name="ph_tanah" value="{{ old('ph_tanah') }}"
                        step="0.1" min="3" max="8" placeholder="Contoh: 5.2"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('ph_tanah') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Skala 3.0–8.0 · Optimal sawit: 5.5–6.5</p>
                    <p class="mt-0.5 text-xs text-amber-600">⚡ pH < 4.5 = pupuk tidak efektif, perlu kapur dulu</p>
                    @error('ph_tanah') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelembaban Tanah</label>
                    <select name="kelembaban_tanah"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Sangat Kering','Kering','Normal','Lembab','Sangat Lembab'] as $opt)
                            <option value="{{ $opt }}" {{ old('kelembaban_tanah') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400 break-words">Pengaruh: pupuk butuh kelembaban untuk terlarut dan diserap akar</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kondisi Drainase</label>
                    <select name="kondisi_drainase"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Baik','Cukup','Buruk — Tergenang'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_drainase') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Buruk = tergenang air, pupuk tanah akan terbuang sia-sia</p>
                </div>
            </div>
        </div>

        {{-- SEKSI 3: Kondisi Iklim --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1 flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs font-bold">3</span>
                Kondisi Iklim
            </h2>
            <p class="text-xs text-slate-400 mb-4 ml-0 sm:ml-8">Musim dan curah hujan mempengaruhi kapan waktu terbaik aplikasi pupuk.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Musim Saat Ini</label>
                    <select name="musim_saat_ini" id="select-musim"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Musim Hujan','Musim Kemarau','Peralihan'] as $opt)
                            <option value="{{ $opt }}" {{ old('musim_saat_ini') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Musim hujan = waktu optimal pemupukan; Kemarau = pupuk kurang efektif</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Intensitas Curah Hujan</label>
                    <select name="curah_hujan_kategori" id="select-curah-hujan"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-400" id="curah-hujan-info">Pilih musim terlebih dahulu</p>
                </div>
            </div>
        </div>

        {{-- SEKSI 4: Gejala Visual Tanaman --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1 flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold">4</span>
                Gejala Visual Tanaman
            </h2>
            <p class="text-xs text-slate-400 mb-4 ml-0 sm:ml-8">Pengamatan fisik daun, pelepah, dan tandan untuk mendeteksi kekurangan unsur hara.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Warna Daun</label>
                    <select name="warna_daun"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Hijau Normal','Hijau Pucat','Kuning Merata','Kuning Tepi','Kuning Antar Tulang','Oranye/Kemerahan','Coklat Ujung','Bercak Nekrotik'] as $opt)
                            <option value="{{ $opt }}" {{ old('warna_daun') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kondisi Pelepah</label>
                    <select name="kondisi_pelepah"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Normal','Patah/Menggantung','Kering Prematur','Pertumbuhan Terhambat'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_pelepah') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kondisi Tandan / Buah</label>
                    <select name="kondisi_tandan"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">— Pilih —</option>
                        @foreach(['Normal','Kecil','Rontok Prematur','Busuk Pangkal','Tidak Ada Tandan'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_tandan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Gejala Defisiensi Multi-select --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Gejala Defisiensi Terdeteksi
                    <span class="text-xs text-slate-400 font-normal">(boleh pilih lebih dari satu)</span>
                </label>
                <p class="text-xs text-slate-400 mb-3">Pilih unsur hara yang diduga kurang berdasarkan pengamatan visual. Sistem akan mencocokkan dengan rule base untuk menentukan jenis pupuk yang tepat.</p>

                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-1.5 sm:gap-2" id="defisiensi-grid">
                    @foreach(['N','P','K','Mg','B','Fe','Zn'] as $def)
                    @php
                        $defLabel = match($def) {
                            'N'  => 'Nitrogen',
                            'P'  => 'Fosfor',
                            'K'  => 'Kalium',
                            'Mg' => 'Magnesium',
                            'B'  => 'Boron',
                            'Fe' => 'Besi',
                            'Zn' => 'Seng',
                            default => $def,
                        };
                        $defHint = match($def) {
                            'N'  => 'Daun kuning merata',
                            'P'  => 'Ujung daun coklat',
                            'K'  => 'Daun oranye/tepi kuning',
                            'Mg' => 'Kuning antar tulang',
                            'B'  => 'Pucuk kerdil/bengkok',
                            'Fe' => 'Daun muda pucat',
                            'Zn' => 'Daun muda kecil',
                            default => '',
                        };
                        $checked = in_array($def, old('gejala_defisiensi', []));
                    @endphp
                    <label class="def-label flex flex-col items-center gap-1 p-2 sm:p-2.5 border rounded-xl cursor-pointer transition-all {{ $checked ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-500' : 'border-slate-200 hover:bg-emerald-50 hover:border-emerald-400' }}">
                        <input type="checkbox" name="gejala_defisiensi[]" value="{{ $def }}"
                            {{ $checked ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <span class="text-sm sm:text-base font-bold text-slate-800">{{ $def }}</span>
                        <span class="text-[10px] text-slate-500 text-center leading-tight">{{ $defLabel }}</span>
                        <span class="text-[9px] text-slate-400 text-center leading-tight italic hidden sm:block">{{ $defHint }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-3 p-3 rounded-xl bg-blue-50 border border-blue-100 text-xs text-blue-800">
                    <p class="font-semibold mb-1">💡 Cara menentukan gejala defisiensi:</p>
                    <ul class="space-y-0.5 text-blue-700">
                        <li>• <strong>N (Nitrogen)</strong> — daun menguning secara merata dari daun tua ke muda</li>
                        <li>• <strong>K (Kalium)</strong> — daun oranye kemerahan (Orange Frond) atau tepi menguning</li>
                        <li>• <strong>Mg (Magnesium)</strong> — kuning hanya di antara tulang daun, tulang tetap hijau</li>
                        <li>• <strong>B (Boron)</strong> — pucuk tidak berkembang, daun muda kerdil dan bengkok</li>
                        <li>• <strong>P (Fosfor)</strong> — ujung daun tua coklat/nekrosis, pertumbuhan lambat</li>
                    </ul>
                </div>
            </div>

            {{-- Toggle Kondisi Khusus --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="toggle-label flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition-all {{ old('ada_serangan_hama') ? 'bg-red-50 border-red-400' : 'border-slate-200 hover:bg-red-50 hover:border-red-300' }}">
                    <input type="checkbox" name="ada_serangan_hama" value="1"
                        {{ old('ada_serangan_hama') ? 'checked' : '' }}
                        class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                    <div>
                        <span class="text-sm font-medium text-slate-800">Ada Serangan Hama / Penyakit</span>
                        <p class="text-xs text-slate-400 mt-0.5">Terlihat gejala serangan fisik atau bercak penyakit</p>
                    </div>
                </label>
                <label class="toggle-label flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition-all {{ old('ada_gulma_dominan') ? 'bg-amber-50 border-amber-400' : 'border-slate-200 hover:bg-amber-50 hover:border-amber-300' }}">
                    <input type="checkbox" name="ada_gulma_dominan" value="1"
                        {{ old('ada_gulma_dominan') ? 'checked' : '' }}
                        class="w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500">
                    <div>
                        <span class="text-sm font-medium text-slate-800">Ada Gulma Dominan</span>
                        <p class="text-xs text-slate-400 mt-0.5">Gulma menutupi piringan atau gawangan secara masif</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- SEKSI 5: Catatan --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <h2 class="text-sm sm:text-base font-semibold text-slate-800 mb-3 flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">5</span>
                Catatan Observasi
            </h2>
            <textarea name="catatan_observasi" rows="3"
                placeholder="Catatan tambahan dari petugas lapangan (opsional)..."
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none">{{ old('catatan_observasi') }}</textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pb-2">
            <a href="{{ route('kondisi-lahan.index') }}"
               class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50 transition-colors font-medium text-center">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-emerald-600/20">
                Simpan Data Kondisi
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
// Toggle visual state untuk defisiensi checkboxes
document.querySelectorAll('#defisiensi-grid .def-label input[type="checkbox"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var label = this.closest('.def-label');
        if (this.checked) {
            label.classList.remove('border-slate-200', 'hover:bg-emerald-50', 'hover:border-emerald-400');
            label.classList.add('bg-emerald-50', 'border-emerald-500', 'ring-2', 'ring-emerald-500');
        } else {
            label.classList.remove('bg-emerald-50', 'border-emerald-500', 'ring-2', 'ring-emerald-500');
            label.classList.add('border-slate-200', 'hover:bg-emerald-50', 'hover:border-emerald-400');
        }
    });
});

// Toggle visual state untuk hama/gulma checkboxes
document.querySelectorAll('.toggle-label input[type="checkbox"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var label = this.closest('.toggle-label');
        var isHama = this.name === 'ada_serangan_hama';
        var activeClasses = isHama ? ['bg-red-50', 'border-red-400'] : ['bg-amber-50', 'border-amber-400'];
        var inactiveClasses = isHama
            ? ['border-slate-200', 'hover:bg-red-50', 'hover:border-red-300']
            : ['border-slate-200', 'hover:bg-amber-50', 'hover:border-amber-300'];

        if (this.checked) {
            inactiveClasses.forEach(function(c) { label.classList.remove(c); });
            activeClasses.forEach(function(c) { label.classList.add(c); });
        } else {
            activeClasses.forEach(function(c) { label.classList.remove(c); });
            inactiveClasses.forEach(function(c) { label.classList.add(c); });
        }
    });
});

// ─── MUSIM → CURAH HUJAN DEPENDENCY ──────────────────────────────
var musimEl = document.getElementById('select-musim');
var curahEl = document.getElementById('select-curah-hujan');
var curahInfo = document.getElementById('curah-hujan-info');
var oldCurah = '{{ old("curah_hujan_kategori") }}';

var curahOptions = {
    'Musim Hujan': ['Normal','Tinggi','Sangat Tinggi'],
    'Musim Kemarau': ['Sangat Rendah','Rendah','Normal'],
    'Peralihan': ['Sangat Rendah','Rendah','Normal','Tinggi','Sangat Tinggi'],
};

var curahHints = {
    'Musim Hujan': 'Saat musim hujan, curah hujan umumnya Normal hingga Sangat Tinggi',
    'Musim Kemarau': 'Saat kemarau, curah hujan umumnya Sangat Rendah hingga Normal',
    'Peralihan': 'Saat peralihan musim, curah hujan bisa bervariasi',
};

function updateCurahOptions() {
    var musim = musimEl.value;
    curahEl.innerHTML = '<option value="">— Pilih —</option>';

    if (musim && curahOptions[musim]) {
        curahOptions[musim].forEach(function(opt) {
            var selected = (opt === oldCurah) ? ' selected' : '';
            curahEl.innerHTML += '<option value="' + opt + '"' + selected + '>' + opt + '</option>';
        });
        curahInfo.textContent = curahHints[musim] || '';
        curahInfo.className = 'mt-1 text-xs text-sky-600';
    } else {
        curahInfo.textContent = 'Pilih musim terlebih dahulu';
        curahInfo.className = 'mt-1 text-xs text-slate-400';
    }
}

musimEl.addEventListener('change', updateCurahOptions);
// Init on load (kalau ada old value)
if (musimEl.value) updateCurahOptions();

// ─── CASCADING: PEMILIK → BLOK LAHAN ─────────────────────────────
var bloksData = @json($bloksJson);
var blokListEl = document.getElementById('blok-list');
var blokHintEl = document.getElementById('blok-hint');
var blokValueEl = document.getElementById('blok-lahan-id-value');
var selectedBlokId = '{{ old("blok_lahan_id", $selectedBlokId) }}';

// Listen for pemilik selection (from searchable-select component)
var anggotaHidden = document.querySelector('input[name="_anggota_filter"]');
if (anggotaHidden) {
    // Use MutationObserver to detect value changes on hidden input
    var observer = new MutationObserver(function() { renderBlokList(anggotaHidden.value); });
    observer.observe(anggotaHidden, { attributes: true, attributeFilter: ['value'] });

    // Also poll for changes (backup since hidden input value change doesn't trigger mutation)
    var lastAnggotaVal = anggotaHidden.value;
    setInterval(function() {
        if (anggotaHidden.value !== lastAnggotaVal) {
            lastAnggotaVal = anggotaHidden.value;
            renderBlokList(anggotaHidden.value);
        }
    }, 200);

    // Initial render if already selected
    if (anggotaHidden.value) renderBlokList(anggotaHidden.value);
}

function renderBlokList(anggotaId) {
    if (!anggotaId) {
        blokListEl.classList.add('hidden');
        blokHintEl.classList.remove('hidden');
        blokHintEl.textContent = 'Pilih pemilik lahan terlebih dahulu untuk menampilkan daftar blok.';
        return;
    }

    var filtered = bloksData.filter(function(b) { return b.anggota_id == anggotaId; });
    // Sort: terbaru dulu
    filtered.sort(function(a, b) { return b.updated_at - a.updated_at; });

    if (filtered.length === 0) {
        blokListEl.classList.add('hidden');
        blokHintEl.classList.remove('hidden');
        blokHintEl.textContent = 'Pemilik ini belum memiliki blok lahan.';
        return;
    }

    blokHintEl.classList.add('hidden');
    blokListEl.classList.remove('hidden');
    blokListEl.innerHTML = '';

    filtered.forEach(function(blok, idx) {
        var isSelected = blokValueEl.value == blok.id;
        var isNew = idx === 0; // terbaru
        var card = document.createElement('div');
        card.className = 'blok-card relative flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all '
            + (isSelected ? 'bg-emerald-50 border-emerald-500 ring-1 ring-emerald-500' : 'bg-white border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/30');
        card.dataset.blokId = blok.id;

        card.innerHTML = '<div class="flex-1 min-w-0">'
            + '<p class="font-semibold text-slate-800 text-sm">' + blok.nama_blok + '</p>'
            + '<p class="text-[10px] text-slate-500">' + blok.luas_ha + ' Ha · ' + blok.kategori + '</p>'
            + '</div>'
            + (isNew ? '<span class="text-[9px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0">Terbaru</span>' : '')
            + (isSelected ? '<svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' : '');

        card.addEventListener('click', function() {
            blokValueEl.value = blok.id;
            renderBlokList(anggotaId); // re-render to update selection
        });

        blokListEl.appendChild(card);
    });
}
</script>
@endpush
