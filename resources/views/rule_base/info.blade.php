@extends('layouts.app')

@section('title', 'Tentang Rule Base')
@section('page-title', 'Tentang Rule Base')
@section('page-subtitle', 'Penjelasan aturan yang digunakan sistem analisis RBS')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('rule-base.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Rule Base
    </a>

    {{-- Penjelasan Utama --}}
    <div class="bg-white border border-emerald-100 rounded-2xl shadow-sm p-5 sm:p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Rule-Based System (RBS)</h2>
                <p class="text-xs text-emerald-600 font-medium">Metode Forward Chaining — 25 rule aktif</p>
            </div>
        </div>

        <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
                Rule Base yang ditampilkan di menu ini adalah aturan-aturan <strong>Rule-Based System (RBS)</strong> yang digunakan secara aktif oleh sistem untuk menganalisis kondisi lahan dan menghasilkan rekomendasi pemupukan.
            </p>
            <p>
                Setiap rule memiliki <strong>kondisi (IF)</strong> berupa kombinasi parameter seperti warna daun, pH tanah, kelembaban, musim, gejala defisiensi, dll. Jika semua kondisi yang diisi cocok dengan data observasi lapangan (logika AND), maka rule <strong>terpicu</strong> dan menghasilkan <strong>output (THEN)</strong> berupa indikasi masalah, jenis pupuk, dosis, dan saran tindakan.
            </p>
            <p>
                Sistem juga menghitung <strong>dosis Urea & KCl</strong> secara numerik berdasarkan formula agronomis (umur tanaman × jenis tanah × topografi × koreksi waktu pemupukan terakhir).
            </p>
        </div>
    </div>

    {{-- Alur Analisis --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Alur Analisis RBS</h3>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
                <div class="text-lg mb-1">📋</div>
                <p class="text-xs font-bold text-blue-800">1. Input Kondisi</p>
                <p class="text-[10px] text-blue-600 mt-0.5">Admin input data observasi lapangan</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                <div class="text-lg mb-1">⚡</div>
                <p class="text-xs font-bold text-amber-800">2. Evaluasi Rule</p>
                <p class="text-[10px] text-amber-600 mt-0.5">Semua rule aktif dievaluasi (AND logic)</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                <div class="text-lg mb-1">🧮</div>
                <p class="text-xs font-bold text-emerald-800">3. Hitung Dosis</p>
                <p class="text-[10px] text-emerald-600 mt-0.5">Formula: base × tanah × topografi × waktu</p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-center">
                <div class="text-lg mb-1">📊</div>
                <p class="text-xs font-bold text-purple-800">4. Rekomendasi</p>
                <p class="text-[10px] text-purple-600 mt-0.5">Status + masalah + pupuk + saran</p>
            </div>
        </div>
    </div>

    {{-- Status Kebutuhan --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4">4 Level Status Kebutuhan</h3>
        <p class="text-xs text-slate-500 mb-4">Status ditentukan oleh rule dengan prioritas tertinggi yang terpicu. Hierarki: Defisiensi Berat > Perlu Pupuk > Sehat > Tunda Pupuk.</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                <p class="text-sm font-bold text-red-700">Defisiensi Berat</p>
                <p class="text-[10px] text-red-600 mt-1">Masalah berat (pH sangat rendah, penyakit, dll). Atasi masalah dulu sebelum pupuk.</p>
                <p class="text-[9px] text-red-500 mt-1 font-medium">Prioritas: 1–2</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 text-center">
                <p class="text-sm font-bold text-orange-700">Perlu Pupuk</p>
                <p class="text-[10px] text-orange-600 mt-1">Defisiensi hara terdeteksi. Segera aplikasikan pupuk sesuai rekomendasi.</p>
                <p class="text-[9px] text-orange-500 mt-1 font-medium">Prioritas: 2–3</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                <p class="text-sm font-bold text-emerald-700">Sehat</p>
                <p class="text-[10px] text-emerald-600 mt-1">Kondisi normal. Lanjutkan pemupukan standar sesuai jadwal rutin.</p>
                <p class="text-[9px] text-emerald-500 mt-1 font-medium">Prioritas: 4–9</p>
            </div>
            <div class="bg-slate-100 border border-slate-200 rounded-xl p-3 text-center">
                <p class="text-sm font-bold text-slate-600">Tunda Pupuk</p>
                <p class="text-[10px] text-slate-500 mt-1">Kondisi tidak mendukung (tergenang, terlalu kering, tanaman tua renta).</p>
                <p class="text-[9px] text-slate-500 mt-1 font-medium">Prioritas: 1–8</p>
            </div>
        </div>
    </div>

    {{-- Cara Kerja Kondisi IF --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-3">Cara Kerja Kondisi (IF)</h3>
        <div class="space-y-2 text-xs text-slate-600">
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p>Field yang <strong>dikosongkan</strong> (tidak dicek) berarti rule tidak mempertimbangkan parameter tersebut — bersifat wildcard.</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p>Field yang <strong>diisi</strong> harus cocok dengan data observasi agar rule terpicu (logika <strong>AND</strong> — semua harus cocok).</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p><strong>pH Tanah</strong> menggunakan range check: nilai pH observasi harus berada di antara min dan max yang ditentukan.</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p><strong>Defisiensi</strong> menggunakan array-contains check: jika rule menetapkan "N", maka gejala defisiensi observasi harus mengandung "N".</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p><strong>Prioritas 1–10:</strong> angka semakin kecil = semakin penting. Rule dengan prioritas 1 akan muncul paling atas di hasil analisis.</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-600 font-bold flex-shrink-0">•</span>
                <p>Rule bisa di-<strong>nonaktifkan</strong> tanpa dihapus. Rule nonaktif tidak dievaluasi saat analisis.</p>
            </div>
        </div>
    </div>
</div>
@endsection
