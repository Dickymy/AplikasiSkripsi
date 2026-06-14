@extends('layouts.app')

@section('title', 'Edit Rule')
@section('page-title', 'Edit Rule')
@section('page-subtitle', 'Perbarui aturan: {{ \Illuminate\Support\Str::limit($rule->indikasi_masalah, 40) }}')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('rule-base.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <form method="POST" action="{{ route('rule-base.update', $rule) }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- SEKSI 1: Kondisi (IF) --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 sm:p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">IF</span>
                Kondisi — Parameter yang harus cocok
            </h2>
            <p class="text-xs text-slate-400 mb-4">Kosongkan field yang tidak relevan. Semua field yang diisi harus cocok (logika AND).</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Warna Daun --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Warna Daun</label>
                    <select name="kondisi_warna_daun" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Hijau Normal','Hijau Pucat','Kuning Merata','Kuning Tepi','Kuning Antar Tulang','Oranye/Kemerahan','Coklat Ujung','Bercak Nekrotik'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_warna_daun', $rule->kondisi_warna_daun) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">pH Minimum</label>
                    <input type="number" name="kondisi_ph_min" value="{{ old('kondisi_ph_min', $rule->kondisi_ph_min) }}" step="0.1" min="3" max="8" placeholder="cth: 3.0"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">pH Maksimum</label>
                    <input type="number" name="kondisi_ph_max" value="{{ old('kondisi_ph_max', $rule->kondisi_ph_max) }}" step="0.1" min="3" max="8" placeholder="cth: 4.5"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kelembaban Tanah</label>
                    <select name="kondisi_kelembaban" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Sangat Kering','Kering','Normal','Lembab','Sangat Lembab'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_kelembaban', $rule->kondisi_kelembaban) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Musim</label>
                    <select name="kondisi_musim" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Musim Hujan','Musim Kemarau','Peralihan'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_musim', $rule->kondisi_musim) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kondisi Drainase</label>
                    <select name="kondisi_drainase" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Baik','Cukup','Buruk — Tergenang'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_drainase', $rule->kondisi_drainase) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Gejala Defisiensi</label>
                    <select name="kondisi_defisiensi" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['N','P','K','Mg','B','Fe','Zn'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_defisiensi', $rule->kondisi_defisiensi) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kategori Umur</label>
                    <select name="kondisi_kategori_umur" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Belum Menghasilkan','Remaja','Menghasilkan Muda','Menghasilkan Tua','Tua Renta'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_kategori_umur', $rule->kondisi_kategori_umur) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kondisi Pelepah</label>
                    <select name="kondisi_pelepah" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Normal','Patah/Menggantung','Kering Prematur','Pertumbuhan Terhambat'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_pelepah', $rule->kondisi_pelepah) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kondisi Tandan</label>
                    <select name="kondisi_tandan" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak dicek —</option>
                        @foreach(['Normal','Kecil','Rontok Prematur','Busuk Pangkal','Tidak Ada Tandan'] as $opt)
                            <option value="{{ $opt }}" {{ old('kondisi_tandan', $rule->kondisi_tandan) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Serangan Hama</label>
                    <select name="ada_serangan_hama" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="null" {{ old('ada_serangan_hama', $rule->ada_serangan_hama === null ? 'null' : '') == 'null' ? 'selected' : '' }}>— Tidak dicek —</option>
                        <option value="1" {{ old('ada_serangan_hama', $rule->ada_serangan_hama) == '1' ? 'selected' : '' }}>Harus ada hama</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEKSI 2: Output (THEN) --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 sm:p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">→</span>
                Output — Rekomendasi jika rule terpicu
            </h2>
            <p class="text-xs text-slate-400 mb-4">Field bertanda * wajib diisi.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Indikasi Masalah <span class="text-red-400">*</span></label>
                    <input type="text" name="indikasi_masalah" value="{{ old('indikasi_masalah', $rule->indikasi_masalah) }}" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 @error('indikasi_masalah') border-red-400 @enderror">
                    @error('indikasi_masalah') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Jenis Pupuk Utama <span class="text-red-400">*</span></label>
                    <input type="text" name="jenis_pupuk_utama" value="{{ old('jenis_pupuk_utama', $rule->jenis_pupuk_utama) }}" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 @error('jenis_pupuk_utama') border-red-400 @enderror">
                    @error('jenis_pupuk_utama') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Jenis Pupuk Pendukung</label>
                    <input type="text" name="jenis_pupuk_pendukung" value="{{ old('jenis_pupuk_pendukung', $rule->jenis_pupuk_pendukung) }}"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Dosis Anjuran <span class="text-red-400">*</span></label>
                    <input type="text" name="dosis_anjuran" value="{{ old('dosis_anjuran', $rule->dosis_anjuran) }}" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 @error('dosis_anjuran') border-red-400 @enderror">
                    @error('dosis_anjuran') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Metode Aplikasi</label>
                    <input type="text" name="metode_aplikasi" value="{{ old('metode_aplikasi', $rule->metode_aplikasi) }}"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Waktu Aplikasi</label>
                    <input type="text" name="waktu_aplikasi" value="{{ old('waktu_aplikasi', $rule->waktu_aplikasi) }}"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Saran Tindakan <span class="text-red-400">*</span></label>
                    <textarea name="saran_tindakan" rows="3" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 resize-none @error('saran_tindakan') border-red-400 @enderror">{{ old('saran_tindakan', $rule->saran_tindakan) }}</textarea>
                    @error('saran_tindakan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- SEKSI 3: Konfigurasi --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 sm:p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">⚙</span>
                Konfigurasi Rule
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Status Kebutuhan <span class="text-red-400">*</span></label>
                    <select name="status_kebutuhan" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach(['Darurat' => 'Darurat (Defisiensi Berat)', 'Segera' => 'Segera (Perlu Pupuk)', 'Normal' => 'Normal (Sehat)', 'Tunda' => 'Tunda (Tunda Pupuk)'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status_kebutuhan', $rule->status_kebutuhan) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Prioritas (1–10) <span class="text-red-400">*</span></label>
                    <input type="number" name="prioritas" value="{{ old('prioritas', $rule->prioritas) }}" min="1" max="10" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-0.5">1 = paling penting, 10 = paling rendah</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50 transition-colors w-full">
                        <input type="checkbox" name="aktif" value="1" {{ old('aktif', $rule->aktif) ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-medium text-slate-800">Rule Aktif</span>
                            <p class="text-[10px] text-slate-400">Rule akan digunakan dalam analisis</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-medium text-slate-700 mb-1.5">Keterangan / Catatan</label>
                <textarea name="keterangan_rule" rows="2" placeholder="Catatan internal..."
                    class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 resize-none">{{ old('keterangan_rule', $rule->keterangan_rule) }}</textarea>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3">
            <a href="{{ route('rule-base.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50 transition-colors font-medium text-center">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">Perbarui Rule</button>
        </div>
    </form>
</div>
@endsection
