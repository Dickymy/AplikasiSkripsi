@extends('layouts.app')

@section('title', 'Panduan Penggunaan')
@section('page-title', 'Panduan Penggunaan')
@section('page-subtitle', 'Cara menggunakan sistem rekomendasi pemupukan')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">


    {{-- Langkah-langkah --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">📋 Langkah-Langkah Penggunaan</h3>
        </div>
        <div class="divide-y divide-slate-100">

            {{-- Langkah 1 --}}
            <div class="px-5 py-4 flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">1</div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Daftarkan Anggota Kelompok Tani</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">Masukkan nama anggota pemilik lahan melalui menu <strong>Anggota</strong>. Setiap anggota bisa memiliki beberapa blok lahan.</p>
                    <a href="{{ route('anggota.index') }}" class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-2 hover:underline">Buka Halaman Anggota →</a>
                </div>
            </div>

            {{-- Langkah 2 --}}
            <div class="px-5 py-4 flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">2</div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Tambah Blok Lahan</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">Untuk setiap anggota, tambahkan blok lahan melalui menu <strong>Blok Lahan</strong>. Isi data:</p>
                    <ul class="text-xs text-slate-600 mt-1 list-disc list-inside space-y-0.5">
                        <li>Nama blok, luas (hektar), SPH (pohon per hektar)</li>
                        <li>Gambar area di peta atau paste koordinat GeoJSON</li>
                        <li>Tahun tanam, jenis tanah, dan topografi</li>
                    </ul>
                    <a href="{{ route('blok-lahan.create') }}" class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-2 hover:underline">Tambah Blok Lahan →</a>
                </div>
            </div>

            {{-- Langkah 3 --}}
            <div class="px-5 py-4 flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">3</div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Input Kondisi Lahan</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">Lakukan observasi di lapangan, lalu masukkan datanya melalui menu <strong>Kondisi Lahan</strong>. Data yang perlu diisi:</p>
                    <ul class="text-xs text-slate-600 mt-1 list-disc list-inside space-y-0.5">
                        <li>Warna daun (hijau normal, kuning, oranye, dll)</li>
                        <li>pH tanah (3.0 – 8.0)</li>
                        <li>Kelembaban tanah dan kondisi drainase</li>
                        <li>Musim saat ini dan curah hujan</li>
                        <li>Dugaan unsur hara yang kurang (N, P, K, Mg, B, Fe, Zn)</li>
                        <li>Kondisi pelepah, tandan, hama, dan gulma</li>
                    </ul>
                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-2.5 text-xs text-blue-700">
                        💡 <strong>Tips:</strong> Semakin lengkap data yang diisi, semakin akurat rekomendasi yang dihasilkan. Minimal isi warna daun + pH tanah + drainase.
                    </div>
                    <a href="{{ route('kondisi-lahan.create') }}" class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-2 hover:underline">Input Kondisi →</a>
                </div>
            </div>

            {{-- Langkah 4 --}}
            <div class="px-5 py-4 flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">4</div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Jalankan Analisis</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">Buka menu <strong>Analisis RBS</strong>, lalu klik tombol <strong>"Analisis"</strong> pada blok yang ingin dicek, atau klik <strong>"Analisis Semua"</strong> untuk menganalisis seluruh blok sekaligus.</p>
                    <p class="text-xs text-slate-600 mt-1">Sistem akan mengevaluasi kondisi berdasarkan 22 aturan diagnostik dan memberikan:</p>
                    <ul class="text-xs text-slate-600 mt-1 list-disc list-inside space-y-0.5">
                        <li>Status: Sehat / Perlu Pupuk / Defisiensi Berat / Tunda Pupuk</li>
                        <li>Dosis pupuk yang tepat (Urea & KCl dalam kg)</li>
                        <li>Jadwal pemupukan per tahap</li>
                        <li>Saran tindakan spesifik</li>
                    </ul>
                    <a href="{{ route('rbs.index') }}" class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-2 hover:underline">Buka Analisis RBS →</a>
                </div>
            </div>

            {{-- Langkah 5 --}}
            <div class="px-5 py-4 flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">5</div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Lihat Hasil & Cetak Laporan</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">Hasil analisis bisa dilihat di halaman <strong>Laporan</strong>. Anda bisa:</p>
                    <ul class="text-xs text-slate-600 mt-1 list-disc list-inside space-y-0.5">
                        <li>Melihat rekap total kebutuhan pupuk per anggota</li>
                        <li>Download PDF per blok untuk dokumentasi</li>
                        <li>Melihat detail rekomendasi setiap blok</li>
                    </ul>
                    <a href="{{ route('laporan.index') }}" class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-2 hover:underline">Buka Laporan →</a>
                </div>
            </div>

        </div>
    </div>

    {{-- Penjelasan Status --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">🚦 Arti Status Rekomendasi</h3>
        </div>
        <div class="divide-y divide-slate-100">
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Defisiensi Berat</span>
                <p class="text-xs text-slate-600">Tanaman mengalami masalah serius. Perlu penanganan segera sebelum memupuk.</p>
            </div>
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Perlu Pupuk</span>
                <p class="text-xs text-slate-600">Ada tanda kekurangan hara. Segera aplikasikan pupuk sesuai rekomendasi.</p>
            </div>
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Sehat</span>
                <p class="text-xs text-slate-600">Kondisi normal. Lanjutkan program pemupukan rutin sesuai dosis standar.</p>
            </div>
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Tunda Pupuk</span>
                <p class="text-xs text-slate-600">Kondisi lahan tidak memungkinkan (tergenang, kering ekstrem, dll). Perbaiki dulu, baru pupuk.</p>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">❓ Pertanyaan Umum</h3>
        </div>
        <div class="divide-y divide-slate-100">
            <details class="group">
                <summary class="px-5 py-3 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-800">Seberapa sering harus input kondisi lahan?</p>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-5 pb-3 text-xs text-slate-600 leading-relaxed">
                    Idealnya setiap 3 bulan sekali (per semester pemupukan). Atau kapan saja Anda melihat gejala abnormal di lapangan seperti daun menguning, tandan rontok, atau tanah tergenang.
                </div>
            </details>
            <details class="group">
                <summary class="px-5 py-3 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-800">Bagaimana kalau data kondisi tidak lengkap?</p>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-5 pb-3 text-xs text-slate-600 leading-relaxed">
                    Sistem tetap bisa dijalankan, tapi tingkat keyakinan rekomendasi akan lebih rendah. Sistem akan memberi tahu data apa saja yang masih kurang agar bisa dilengkapi.
                </div>
            </details>
            <details class="group">
                <summary class="px-5 py-3 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-800">Apa itu "1 karung" pupuk?</p>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-5 pb-3 text-xs text-slate-600 leading-relaxed">
                    1 karung = 50 kg. Angka karung dibulatkan ke atas untuk memudahkan perencanaan pembelian.
                </div>
            </details>
            <details class="group">
                <summary class="px-5 py-3 cursor-pointer hover:bg-slate-50 transition-colors flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-800">Apakah bisa menjalankan analisis ulang?</p>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-5 pb-3 text-xs text-slate-600 leading-relaxed">
                    Ya, Anda bisa menjalankan analisis berulang kali. Jika kondisi lahan berubah (misalnya setelah hujan atau setelah perbaikan drainase), update data kondisi lalu jalankan ulang analisis untuk mendapat rekomendasi terbaru.
                </div>
            </details>
        </div>
    </div>

    {{-- Alur Diagram --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-800 mb-4">🔄 Alur Kerja Sistem</h3>
        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 text-center">
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex-1 w-full sm:w-auto">
                <p class="text-xs font-bold text-blue-800">1. Input Data</p>
                <p class="text-[10px] text-blue-600 mt-0.5">Anggota + Blok + Kondisi</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 rotate-90 sm:rotate-0 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex-1 w-full sm:w-auto">
                <p class="text-xs font-bold text-emerald-800">2. Analisis RBS</p>
                <p class="text-[10px] text-emerald-600 mt-0.5">Evaluasi 22 aturan</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 rotate-90 sm:rotate-0 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex-1 w-full sm:w-auto">
                <p class="text-xs font-bold text-amber-800">3. Rekomendasi</p>
                <p class="text-[10px] text-amber-600 mt-0.5">Dosis + Jadwal + Saran</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 rotate-90 sm:rotate-0 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <div class="bg-purple-50 border border-purple-200 rounded-xl px-4 py-3 flex-1 w-full sm:w-auto">
                <p class="text-xs font-bold text-purple-800">4. Laporan</p>
                <p class="text-[10px] text-purple-600 mt-0.5">PDF + Rekap</p>
            </div>
        </div>
    </div>

</div>
@endsection
