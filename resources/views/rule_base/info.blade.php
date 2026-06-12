@extends('layouts.app')

@section('title', 'Tentang Rule Base')
@section('page-title', 'Tentang Rule Base')
@section('page-subtitle', 'Penjelasan dua mekanisme aturan yang digunakan sistem')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('rule-base.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Rule Base
    </a>

    {{-- Rule Base Legacy (Forward Chaining) --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Rule Base — Forward Chaining (Legacy)</h2>
                <p class="text-xs text-slate-400 font-medium">Tabel: <code class="bg-slate-100 px-1.5 py-0.5 rounded">rule_bases</code></p>
            </div>
        </div>
        <div class="prose prose-sm prose-slate max-w-none">
            <p class="text-sm text-slate-600 leading-relaxed">
                Tabel ini berisi <strong>150 kombinasi aturan</strong> Forward Chaining berbasis parameter agronomis tetap:
                <strong>5 Kategori Umur × 10 Jenis Tanah × 3 Topografi</strong>.
            </p>
            <p class="text-sm text-slate-600 leading-relaxed">
                Setiap rule memiliki format key <code>Kategori_Umur|Jenis_Tanah|Topografi</code> dengan output berupa takaran Urea dan KCl (kg/pokok) serta status pemupukan.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-3">
                <p class="text-xs font-semibold text-amber-800 mb-1">⚠️ Status: Referensi Historis</p>
                <p class="text-xs text-amber-700">Tabel ini <strong>tidak digunakan langsung dalam analisis RBS aktif</strong>. Formula dosis sudah terintegrasi ke dalam RbsService.php sebagai multiplier perhitungan. Data di tabel ini bisa dikelola untuk referensi dan dokumentasi.</p>
            </div>
        </div>
    </div>

    {{-- Rule Base Lanjutan (RBS Aktif) --}}
    <div class="bg-white border border-emerald-100 rounded-2xl shadow-sm p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Rule Base Lanjutan — RBS Aktif</h2>
                <p class="text-xs text-emerald-600 font-medium">Tabel: <code class="bg-emerald-50 px-1.5 py-0.5 rounded">rule_bases_lanjutan</code></p>
            </div>
        </div>
        <div class="prose prose-sm prose-slate max-w-none">
            <p class="text-sm text-slate-600 leading-relaxed">
                Tabel ini berisi <strong>25 rule aktif</strong> yang digunakan oleh <code>RbsService</code> untuk menganalisis kondisi lahan secara real-time berbasis gejala visual, pH tanah, kondisi iklim, dan parameter lingkungan.
            </p>
            <p class="text-sm text-slate-600 leading-relaxed">
                Setiap rule memiliki <strong>multi-kondisi IF</strong> (AND logic) dan output berupa indikasi masalah, jenis pupuk, dosis, metode aplikasi, dan saran tindakan.
            </p>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mt-3">
                <p class="text-xs font-semibold text-emerald-800 mb-1">✅ Status: Aktif Digunakan</p>
                <p class="text-xs text-emerald-700">Rule di tabel ini <strong>langsung digunakan dalam analisis RBS</strong>. Penambahan, pengeditan, atau penghapusan rule akan langsung mempengaruhi hasil analisis selanjutnya. Gunakan field "aktif" untuk menonaktifkan rule tanpa menghapusnya.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-red-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-red-700">Darurat</p>
                <p class="text-[10px] text-red-600">Prioritas 1–2</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-orange-700">Segera</p>
                <p class="text-[10px] text-orange-600">Prioritas 2–3</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-emerald-700">Normal</p>
                <p class="text-[10px] text-emerald-600">Prioritas 4–9</p>
            </div>
            <div class="bg-slate-100 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-slate-600">Tunda</p>
                <p class="text-[10px] text-slate-500">Prioritas 1–8</p>
            </div>
        </div>
    </div>
</div>
@endsection
