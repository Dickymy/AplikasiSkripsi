# Requirements Document

## Introduction

Sistem Pendukung Keputusan Pemupukan Kelapa Sawit (SPK Sawit) adalah aplikasi web berbasis Laravel 11 yang membantu kelompok tani menentukan dosis pupuk (Urea & KCl) yang tepat per blok lahan. Aplikasi ini menggunakan dua mekanisme inferensi: **Forward Chaining** untuk penentuan dosis berdasarkan parameter agronomis tetap (umur, jenis tanah, topografi), dan **Rule-Based System (RBS)** untuk analisis kondisi lahan real-time berdasarkan gejala visual dan parameter lingkungan. Dilengkapi peta interaktif WebGIS berbasis Leaflet.js untuk visualisasi spasial status pemupukan seluruh blok lahan.

Aplikasi ini bersifat single-tenant untuk satu kelompok tani dengan satu role admin yang mengelola seluruh sistem.

## Glossary

- **SPK**: Sistem Pendukung Keputusan — modul inferensi Forward Chaining yang menentukan dosis pupuk berdasarkan rule base parameter agronomis tetap
- **RBS**: Rule-Based System — modul inferensi lanjutan yang mengevaluasi kondisi lahan real-time (gejala visual, pH, iklim) untuk menghasilkan rekomendasi pemupukan kontekstual
- **Forward_Chaining**: Metode inferensi yang dimulai dari fakta (data lahan) lalu mencocokkan ke aturan (rule) untuk menghasilkan konklusi (rekomendasi dosis)
- **Blok_Lahan**: Unit area kebun kelapa sawit yang dikelola sebagai satu kesatuan manajemen pemupukan
- **Kriteria_Lahan**: Data parameter agronomis tetap suatu blok lahan: tahun tanam, jenis tanah, dan topografi
- **Kondisi_Lahan**: Data observasi lapangan periodik meliputi pH tanah, kelembaban, gejala visual daun, kondisi drainase, dan parameter lingkungan lainnya
- **Rule_Base_SPK**: Tabel aturan Forward Chaining berisi 150 kombinasi (5 kategori umur × 10 jenis tanah × 3 topografi) dengan output dosis Urea dan KCl per pokok
- **Rule_Base_RBS**: Tabel aturan lanjutan berisi ~20 rules berdasarkan gejala visual dan kondisi lingkungan dengan output indikasi masalah, rekomendasi pupuk, dan saran tindakan
- **Rekomendasi_SPK**: Hasil konklusi Forward Chaining berupa dosis dan total kebutuhan pupuk per blok lahan
- **Rekomendasi_RBS**: Hasil evaluasi RBS berupa masalah teridentifikasi, rekomendasi pupuk kontekstual, dan saran tindakan
- **Admin**: Pengguna tunggal sistem yang memiliki akses penuh ke seluruh modul
- **WebGIS**: Web Geographic Information System — peta interaktif untuk visualisasi spasial blok lahan
- **SPH**: Standar Pohon per Hektar — jumlah pohon sawit per hektar pada suatu blok
- **Kategori_Umur**: Klasifikasi umur tanaman sawit: Belum Menghasilkan (<3 thn), Remaja (3–8 thn), Menghasilkan Muda (9–14 thn), Menghasilkan Tua (15–25 thn), Tua Renta (>25 thn)
- **Parameter_Kondisi**: String key format "Kategori_Umur|Jenis_Tanah|Topografi" untuk pattern matching ke Rule_Base_SPK
- **Status_Pemupukan_SPK**: Klasifikasi status dari Forward Chaining: Segera Pupuk, Pemupukan Normal, Tunda Pemupukan
- **Status_Kebutuhan_RBS**: Klasifikasi status dari RBS berdasarkan hierarki prioritas: Darurat > Segera > Normal > Tunda
- **GeoJSON**: Format data geospasial untuk koordinat poligon blok lahan
- **Karung**: Satuan logistik pupuk dimana 1 karung = 50 kg

## Requirements

### Requirement 1: Autentikasi Admin

**User Story:** Sebagai admin kelompok tani, saya ingin login ke sistem dengan credential yang aman, agar hanya pihak berwenang yang dapat mengakses dan mengelola data pemupukan.

#### Acceptance Criteria

1. WHEN Admin memasukkan username dan password yang valid pada halaman login, THE Sistem_Autentikasi SHALL mengautentikasi Admin menggunakan guard 'admin' dan mengarahkan ke halaman dashboard
2. WHEN Admin memasukkan credential yang tidak valid, THE Sistem_Autentikasi SHALL menampilkan pesan error dan mengembalikan ke halaman login tanpa menyimpan sesi
3. WHERE Admin memilih opsi "Remember Me" saat login, THE Sistem_Autentikasi SHALL mempertahankan sesi autentikasi melampaui durasi sesi standar
4. WHEN Admin yang belum terautentikasi mengakses route yang dilindungi, THE Middleware_AdminAuthenticated SHALL mengarahkan ke halaman login
5. WHEN Admin menekan tombol logout, THE Sistem_Autentikasi SHALL menghapus sesi aktif dan mengarahkan ke halaman login
6. WHILE Admin sudah terautentikasi, THE Sistem_Autentikasi SHALL mengizinkan akses ke seluruh route yang dilindungi middleware AdminAuthenticated

### Requirement 2: Dashboard WebGIS (Peta Interaktif)

**User Story:** Sebagai admin, saya ingin melihat visualisasi spasial seluruh blok lahan dalam peta interaktif, agar saya dapat memantau status pemupukan secara cepat dan menyeluruh.

#### Acceptance Criteria

1. WHEN Admin mengakses halaman dashboard, THE Dashboard_WebGIS SHALL menampilkan peta interaktif Leaflet.js dengan seluruh blok lahan yang memiliki data koordinat GeoJSON sebagai poligon
2. THE Dashboard_WebGIS SHALL mewarnai setiap poligon blok lahan berdasarkan status RBS: merah untuk Darurat, oranye untuk Segera, hijau untuk Normal, abu-abu untuk Tunda, dan putih untuk Belum Dianalisis
3. WHEN Admin mengklik poligon blok lahan pada peta, THE Dashboard_WebGIS SHALL menampilkan popup berisi: nama blok, nama pemilik, masalah teridentifikasi dari RBS, rekomendasi pupuk, saran tindakan, dan link ke halaman detail RBS
4. THE Dashboard_WebGIS SHALL menampilkan stats cards berisi: total blok lahan, total luas (Ha), jumlah blok sudah dianalisis SPK, jumlah blok Segera Pupuk (SPK), jumlah blok status RBS Darurat, dan jumlah blok status RBS Segera
5. WHERE Admin memilih filter pemilik lahan, THE Dashboard_WebGIS SHALL menampilkan hanya poligon blok lahan yang dimiliki oleh pemilik yang dipilih
6. WHERE Admin memilih layer peta tertentu, THE Dashboard_WebGIS SHALL mengubah tampilan base map antara OpenStreetMap (OSM) dan ESRI Satellite imagery

### Requirement 3: Manajemen Blok Lahan (CRUD)

**User Story:** Sebagai admin, saya ingin mengelola data blok lahan (tambah, lihat, edit, hapus), agar seluruh area kebun terdokumentasi untuk analisis pemupukan.

#### Acceptance Criteria

1. WHEN Admin mengisi form tambah blok lahan dengan data lengkap (nama_blok, nama_pemilik, luas_ha, sph, koordinat_geojson), THE Modul_Blok_Lahan SHALL menyimpan data ke database dan menampilkan konfirmasi keberhasilan
2. WHEN Admin mengisi field total_tonase_panen pada form blok lahan, THE Modul_Blok_Lahan SHALL menghitung yield_per_hektar secara otomatis dengan rumus: total_tonase_panen / luas_ha
3. WHEN Admin mengakses halaman detail blok lahan, THE Modul_Blok_Lahan SHALL menampilkan: informasi lahan, kriteria agronomis, rekomendasi SPK terbaru, riwayat analisis SPK, dan hasil RBS terbaru
4. WHEN Admin mengedit data blok lahan dan menyimpan, THE Modul_Blok_Lahan SHALL memperbarui record yang sesuai di database
5. WHEN Admin menghapus blok lahan, THE Modul_Blok_Lahan SHALL menghapus record blok lahan beserta seluruh data terkait (kriteria, kondisi, rekomendasi)
6. THE Modul_Blok_Lahan SHALL menyimpan koordinat blok lahan dalam format GeoJSON yang valid untuk ditampilkan sebagai poligon pada peta Leaflet
7. IF Admin menyimpan blok lahan tanpa mengisi field wajib (nama_blok, luas_ha, sph), THEN THE Modul_Blok_Lahan SHALL menampilkan pesan validasi error untuk setiap field yang belum diisi

### Requirement 4: Manajemen Kriteria Lahan (CRUD)

**User Story:** Sebagai admin, saya ingin menginput data kriteria agronomis (tahun tanam, jenis tanah, topografi) untuk setiap blok lahan, agar sistem dapat melakukan analisis Forward Chaining.

#### Acceptance Criteria

1. WHEN Admin mengisi form kriteria lahan dengan tahun_tanam, jenis_tanah, dan topografi untuk suatu blok lahan, THE Modul_Kriteria_Lahan SHALL menyimpan satu record kriteria yang terhubung ke blok lahan tersebut
2. THE Modul_Kriteria_Lahan SHALL menyediakan 10 pilihan jenis tanah: Tanah Lempung, Tanah Lempung Berpasir, Tanah Berpasir, Tanah Liat, Tanah Gambut, Tanah Aluvial, Tanah Podsolik Merah Kuning (PMK), Tanah Laterit, Tanah Berbatu, Lainnya
3. THE Modul_Kriteria_Lahan SHALL menyediakan 3 pilihan topografi: Datar 0-15°, Bergelombang 15-30°, Curam >30°
4. WHEN data kriteria lahan diakses, THE Modul_Kriteria_Lahan SHALL menghitung umur_tanaman secara dinamis (tahun_sekarang - tahun_tanam) dan menentukan kategori_umur berdasarkan klasifikasi: Belum Menghasilkan (<3 thn), Remaja (3–8 thn), Menghasilkan Muda (9–14 thn), Menghasilkan Tua (15–25 thn), Tua Renta (>25 thn)
5. THE Modul_Kriteria_Lahan SHALL membatasi setiap blok lahan hanya memiliki maksimal satu record kriteria lahan (relasi one-to-one)

### Requirement 5: Manajemen Kondisi Lahan (CRUD) — Input RBS

**User Story:** Sebagai admin, saya ingin menginput data observasi kondisi lahan secara periodik (pH, kelembaban, gejala visual, kondisi lingkungan), agar sistem RBS dapat mengevaluasi masalah dan memberikan rekomendasi kontekstual.

#### Acceptance Criteria

1. WHEN Admin mengisi form kondisi lahan melalui wizard 5 seksi, THE Modul_Kondisi_Lahan SHALL menyimpan seluruh parameter observasi ke database terkait blok lahan yang dipilih
2. THE Modul_Kondisi_Lahan SHALL menyediakan input parameter lingkungan: pH tanah (numerik desimal), kelembaban tanah (pilihan: Sangat Kering, Kering, Normal, Lembab, Sangat Lembab), curah hujan kategori, dan musim saat ini (Musim Hujan, Musim Kemarau, Pancaroba)
3. THE Modul_Kondisi_Lahan SHALL menyediakan input gejala visual: warna daun (8 pilihan: Hijau Normal, Hijau Pucat, Kuning Merata, Kuning Tepi, Kuning Antar Tulang, Oranye/Kemerahan, Coklat Ujung, Bercak Nekrotik), kondisi pelepah (4 pilihan: Normal, Kering Prematur, Patah, Pertumbuhan Terhambat), gejala defisiensi (multi-select: N, P, K, Mg, B, Fe, Zn), dan kondisi tandan (5 pilihan: Normal, Kecil, Rontok Prematur, Busuk Pangkal, Tidak Ada Tandan)
4. THE Modul_Kondisi_Lahan SHALL menyediakan input kondisi fisik: kondisi drainase (pilihan: Baik, Sedang, Buruk — Tergenang), ada gulma dominan (boolean), dan ada serangan hama (boolean)
5. THE Modul_Kondisi_Lahan SHALL menyimpan field gejala_defisiensi sebagai array JSON untuk mendukung seleksi multi-nutrient
6. THE Modul_Kondisi_Lahan SHALL menyimpan tanggal_observasi dan catatan_observasi sebagai metadata setiap record observasi
7. WHEN beberapa observasi kondisi lahan sudah tercatat untuk satu blok, THE Modul_Kondisi_Lahan SHALL menyediakan relasi ke kondisi terbaru (latestOfMany berdasarkan tanggal_observasi) untuk analisis RBS

### Requirement 6: Manajemen Rule Base SPK (CRUD)

**User Story:** Sebagai admin, saya ingin mengelola tabel aturan Forward Chaining (tambah, lihat, edit, hapus), agar saya dapat menyesuaikan dosis pupuk sesuai pedoman PPKS terbaru.

#### Acceptance Criteria

1. THE Modul_Rule_Base_SPK SHALL menyimpan setiap aturan dengan format parameter_kondisi berupa string "Kategori_Umur|Jenis_Tanah|Topografi" dan output berupa takaran_urea (kg/pokok), takaran_kcl (kg/pokok), dan status_pemupukan
2. THE Modul_Rule_Base_SPK SHALL mendukung 150 kombinasi aturan yang dihasilkan dari: 5 kategori umur × 10 jenis tanah × 3 topografi
3. WHEN Admin mengedit takaran_urea atau takaran_kcl pada suatu rule, THE Modul_Rule_Base_SPK SHALL memperbarui dosis yang tersimpan dan dosis tersebut digunakan pada analisis SPK berikutnya
4. THE Modul_Rule_Base_SPK SHALL menyediakan 3 status pemupukan sebagai output rule: Segera Pupuk, Pemupukan Normal, Tunda Pemupukan
5. WHEN Admin menambah rule baru, THE Modul_Rule_Base_SPK SHALL memvalidasi bahwa parameter_kondisi belum ada di database untuk mencegah duplikasi

### Requirement 7: Analisis SPK (Forward Chaining)

**User Story:** Sebagai admin, saya ingin menjalankan analisis Forward Chaining per blok atau untuk semua blok sekaligus, agar sistem menghasilkan rekomendasi dosis pupuk Urea dan KCl yang tepat berdasarkan parameter agronomis.

#### Acceptance Criteria

1. WHEN Admin menjalankan analisis SPK untuk satu blok lahan, THE Mesin_Inferensi_SPK SHALL menjalankan alur Forward Chaining: (a) ambil kriteria lahan, (b) hitung umur tanaman, (c) tentukan kategori umur, (d) buat pattern "kategori_umur|jenis_tanah|topografi", (e) cocokkan ke Rule_Base_SPK
2. WHEN pattern matching berhasil menemukan rule, THE Mesin_Inferensi_SPK SHALL menghitung total kebutuhan pupuk: total_urea = dosis_urea_per_pokok × SPH × luas_ha, total_kcl = dosis_kcl_per_pokok × SPH × luas_ha
3. WHEN analisis SPK selesai, THE Mesin_Inferensi_SPK SHALL menyimpan hasil ke tabel rekomendasi_spks menggunakan pola updateOrCreate berdasarkan blok_lahan_id sehingga setiap blok hanya memiliki satu rekomendasi aktif
4. THE Mesin_Inferensi_SPK SHALL menghitung kebutuhan karung pupuk dengan rumus: jumlah_karung = ceiling(total_kg / 50)
5. WHEN Admin menjalankan "Analisis Semua", THE Mesin_Inferensi_SPK SHALL memproses seluruh blok lahan yang memiliki data kriteria dan melaporkan jumlah blok berhasil dianalisis dan jumlah error
6. IF blok lahan belum memiliki data kriteria lahan, THEN THE Mesin_Inferensi_SPK SHALL menampilkan pesan error "Blok lahan belum memiliki data kriteria lahan" tanpa menyimpan rekomendasi
7. IF pattern matching tidak menemukan rule yang cocok di tabel rule_bases, THEN THE Mesin_Inferensi_SPK SHALL menampilkan pesan error yang menyertakan parameter_kondisi yang gagal dicocokkan

### Requirement 8: Manajemen Rule Base Lanjutan (RBS)

**User Story:** Sebagai admin, saya ingin mengelola aturan Rule-Based System untuk analisis kondisi lahan, agar rekomendasi pemupukan kontekstual dapat disesuaikan dengan pengetahuan agronomi terbaru.

#### Acceptance Criteria

1. THE Modul_Rule_Base_RBS SHALL menyimpan setiap aturan dengan multi-kondisi input: kondisi_warna_daun, kondisi_ph_min, kondisi_ph_max, kondisi_kelembaban, kondisi_musim, kondisi_drainase, kondisi_defisiensi, kondisi_kategori_umur, kondisi_pelepah, kondisi_tandan, ada_serangan_hama
2. THE Modul_Rule_Base_RBS SHALL menyimpan setiap aturan dengan output: indikasi_masalah, jenis_pupuk_utama, jenis_pupuk_pendukung, dosis_anjuran, metode_aplikasi, waktu_aplikasi, saran_tindakan, status_kebutuhan, dan prioritas
3. THE Modul_Rule_Base_RBS SHALL menyediakan 4 level status_kebutuhan: Darurat, Segera, Normal, Tunda
4. THE Modul_Rule_Base_RBS SHALL menyediakan skala prioritas dari 1 (tertinggi/paling mendesak) sampai 10 (terendah)
5. THE Modul_Rule_Base_RBS SHALL mendukung field 'aktif' (boolean) untuk mengaktifkan atau menonaktifkan rule tanpa menghapusnya
6. WHEN kondisi field pada rule bernilai NULL, THE Modul_Rule_Base_RBS SHALL mengabaikan field tersebut saat evaluasi (field NULL berarti kondisi tidak relevan)

### Requirement 9: Analisis RBS (Rule-Based System)

**User Story:** Sebagai admin, saya ingin menjalankan analisis RBS per blok atau untuk semua blok sekaligus, agar sistem mengevaluasi kondisi lahan terkini dan memberikan rekomendasi pemupukan berbasis gejala.

#### Acceptance Criteria

1. WHEN Admin menjalankan analisis RBS untuk satu blok lahan, THE Mesin_Inferensi_RBS SHALL mengambil data kondisi lahan terbaru (latestOfMany) dan mengevaluasi setiap rule aktif yang diurutkan berdasarkan prioritas
2. THE Mesin_Inferensi_RBS SHALL menerapkan logika AND: semua kondisi yang terisi (non-NULL) pada suatu rule harus terpenuhi oleh data kondisi lahan agar rule terpicu
3. WHEN beberapa rules terpicu dengan status berbeda, THE Mesin_Inferensi_RBS SHALL menentukan status dominan berdasarkan hierarki: Darurat > Segera > Normal > Tunda
4. WHEN analisis RBS selesai, THE Mesin_Inferensi_RBS SHALL menyimpan hasil ke tabel rekomendasi_rbs menggunakan pola updateOrCreate berdasarkan blok_lahan_id sehingga setiap blok hanya memiliki satu rekomendasi RBS aktif
5. THE Mesin_Inferensi_RBS SHALL menghasilkan output berisi: daftar masalah teridentifikasi (unik), rekomendasi pupuk (deduplikasi berdasarkan jenis_pupuk_utama), saran tindakan (gabungan 3 rule prioritas tertinggi), dan jumlah rule terpicu
6. WHEN Admin menjalankan "Analisis Semua" RBS, THE Mesin_Inferensi_RBS SHALL memproses seluruh blok lahan yang memiliki data kondisi lahan dan melaporkan jumlah berhasil dan error
7. IF blok lahan belum memiliki data kondisi lahan, THEN THE Mesin_Inferensi_RBS SHALL menampilkan pesan error "Data kondisi lahan belum tersedia untuk blok tersebut"
8. IF tidak ada rule yang terpicu dari kondisi lahan yang dievaluasi, THEN THE Mesin_Inferensi_RBS SHALL menyimpan rekomendasi dengan status "Normal" dan saran "Lanjutkan program pemupukan standar"

### Requirement 10: Independensi SPK dan RBS

**User Story:** Sebagai admin, saya ingin hasil analisis SPK dan RBS berjalan independen tanpa saling menimpa, agar saya mendapat dua perspektif rekomendasi: dosis berbasis parameter agronomis tetap (SPK) dan rekomendasi berbasis kondisi real-time (RBS).

#### Acceptance Criteria

1. THE Sistem SHALL menjalankan analisis SPK Forward Chaining dan analisis RBS secara paralel tanpa hasil satu analisis menimpa atau mengubah hasil analisis lainnya
2. THE Sistem SHALL menyimpan rekomendasi SPK dan rekomendasi RBS pada tabel terpisah (rekomendasi_spks dan rekomendasi_rbs) masing-masing dengan pola updateOrCreate per blok_lahan_id
3. THE Sistem SHALL menampilkan hasil kedua analisis secara bersamaan pada halaman detail blok lahan tanpa konflik informasi

### Requirement 11: Laporan dan Rekap

**User Story:** Sebagai admin, saya ingin melihat laporan rekap seluruh hasil analisis SPK dengan ringkasan kebutuhan pupuk total, agar saya dapat merencanakan pengadaan pupuk secara efisien.

#### Acceptance Criteria

1. WHEN Admin mengakses halaman laporan, THE Modul_Laporan SHALL menampilkan daftar seluruh rekomendasi SPK yang tersimpan dengan detail per blok lahan
2. THE Modul_Laporan SHALL menampilkan summary total: total kebutuhan Urea (kg dan jumlah karung), total kebutuhan KCl (kg dan jumlah karung) dari seluruh rekomendasi yang ditampilkan
3. WHERE Admin memilih filter berdasarkan status pemupukan SPK, THE Modul_Laporan SHALL menampilkan hanya rekomendasi yang sesuai status yang dipilih
4. WHERE Admin memilih filter berdasarkan nama pemilik lahan, THE Modul_Laporan SHALL menampilkan hanya rekomendasi untuk blok lahan milik pemilik yang dipilih
5. WHEN Admin mengklik detail suatu rekomendasi, THE Modul_Laporan SHALL menampilkan informasi lengkap: blok lahan, kriteria yang digunakan, parameter rule yang cocok, dosis per pokok, total kebutuhan, dan status

### Requirement 12: Struktur Database

**User Story:** Sebagai developer, saya ingin struktur database yang terdefinisi dengan jelas dan relasi antar tabel yang benar, agar data tersimpan secara konsisten dan mendukung seluruh fitur analisis.

#### Acceptance Criteria

1. THE Database SHALL menyimpan data admin pada tabel admins dengan kolom: id, username, password (hashed), nama_lengkap, timestamps
2. THE Database SHALL menyimpan data blok lahan pada tabel blok_lahans dengan kolom: id, nama_blok, nama_pemilik, luas_ha (double), sph (integer), koordinat_geojson (text/json), total_tonase_panen (double nullable), yield_per_hektar (double nullable), timestamps
3. THE Database SHALL menyimpan data kriteria pada tabel kriteria_lahans dengan kolom: id, blok_lahan_id (foreign key), tahun_tanam (integer), jenis_tanah (string), topografi (string), timestamps
4. THE Database SHALL menyimpan rule SPK pada tabel rule_bases dengan kolom: id, parameter_kondisi (string unique), takaran_urea (decimal), takaran_kcl (decimal), status_pemupukan (string), timestamps
5. THE Database SHALL menyimpan rekomendasi SPK pada tabel rekomendasi_spks dengan kolom: id, blok_lahan_id (FK), admin_id (FK), tanggal_analisis (date), dosis_urea, dosis_kcl, total_urea, total_kcl, status_akhir, timestamps
6. THE Database SHALL menyimpan kondisi lahan pada tabel kondisi_lahans dengan kolom: id, blok_lahan_id (FK), tanggal_observasi (date), ph_tanah (decimal nullable), kelembaban_tanah, curah_hujan_kategori, musim_saat_ini, warna_daun, kondisi_pelepah, gejala_defisiensi (JSON array), kondisi_tandan, kondisi_drainase, ada_gulma_dominan (boolean), ada_serangan_hama (boolean), catatan_observasi (text nullable), timestamps
7. THE Database SHALL menyimpan rule RBS pada tabel rule_bases_lanjutan dengan kolom: id, kondisi_warna_daun, kondisi_ph_min, kondisi_ph_max, kondisi_kelembaban, kondisi_musim, kondisi_drainase, kondisi_defisiensi, kondisi_kategori_umur, indikasi_masalah, jenis_pupuk_utama, jenis_pupuk_pendukung, dosis_anjuran, metode_aplikasi, waktu_aplikasi, saran_tindakan, status_kebutuhan, prioritas (integer), aktif (boolean default true), timestamps
8. THE Database SHALL menyimpan rekomendasi RBS pada tabel rekomendasi_rbs dengan kolom: id, blok_lahan_id (FK), kondisi_lahan_id (FK), admin_id (FK), tanggal_analisis (date), rules_terpicu (JSON), masalah_teridentifikasi (JSON), rekomendasi_pupuk (JSON), saran_tindakan_utama (text), status_kebutuhan_dominan (string), jumlah_rule_terpicu (integer), timestamps

### Requirement 13: Relasi Antar Entitas

**User Story:** Sebagai developer, saya ingin relasi antar model didefinisikan dengan benar, agar query dan eager loading berjalan efisien.

#### Acceptance Criteria

1. THE Sistem SHALL menetapkan relasi: Admin hasMany RekomendasiSpk dan Admin hasMany RekomendasiRbs
2. THE Sistem SHALL menetapkan relasi: BlokLahan hasOne KriteriaLahan, BlokLahan hasMany KondisiLahan, BlokLahan hasMany RekomendasiSpk, BlokLahan hasMany RekomendasiRbs
3. THE Sistem SHALL menyediakan relasi convenience: BlokLahan hasOne RekomendasiTerbaru (latestOfMany dari rekomendasi_spks), BlokLahan hasOne KondisiTerbaru (latestOfMany berdasarkan tanggal_observasi dari kondisi_lahans), BlokLahan hasOne RekomendasiRbsTerbaru (latestOfMany berdasarkan tanggal_analisis dari rekomendasi_rbs)
4. THE Sistem SHALL menetapkan relasi: KondisiLahan hasMany RekomendasiRbs (satu observasi dapat menghasilkan beberapa analisis)
5. THE Sistem SHALL menetapkan foreign key constraint pada seluruh relasi untuk menjaga integritas referensial data

### Requirement 14: Routing dan Navigasi

**User Story:** Sebagai admin, saya ingin navigasi yang terstruktur dan konsisten antar modul, agar saya dapat mengakses seluruh fitur dengan mudah.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan route GET /dashboard untuk halaman WebGIS sebagai halaman utama setelah login
2. THE Sistem SHALL menyediakan resource routes lengkap (index, create, store, show, edit, update, destroy) untuk modul blok-lahan
3. THE Sistem SHALL menyediakan resource routes (index, create, store, edit, update, destroy — tanpa show) untuk modul kriteria-lahan, rule-base, dan kondisi-lahan
4. THE Sistem SHALL menyediakan route group /spk dengan endpoint: GET index (daftar blok), POST analisis/{blokLahan} (analisis satu blok), POST analisis-semua (batch), GET detail/{blokLahan} (detail hasil)
5. THE Sistem SHALL menyediakan route group /rbs dengan endpoint: GET index, POST analisis/{blokLahan}, POST analisis-semua, GET detail/{blokLahan}
6. THE Sistem SHALL menyediakan route GET /laporan untuk rekap laporan dan GET /laporan/{rekomendasiSpk} untuk detail laporan
7. THE Sistem SHALL menyediakan API endpoint GET /api/rbs-popup/{blokLahan} untuk data popup peta WebGIS
8. THE Sistem SHALL mengarahkan root URL (/) ke halaman dashboard

### Requirement 15: Teknologi dan Arsitektur

**User Story:** Sebagai developer, saya ingin stack teknologi yang terdefinisi dan konsisten, agar pengembangan dan pemeliharaan aplikasi berjalan lancar.

#### Acceptance Criteria

1. THE Sistem SHALL menggunakan Laravel 11 dengan PHP 8.2 sebagai framework backend
2. THE Sistem SHALL menggunakan Blade Templates dengan Tailwind CSS v4 sebagai teknologi frontend dan Vite sebagai asset bundler
3. THE Sistem SHALL menggunakan MySQL sebagai database management system
4. THE Sistem SHALL menggunakan Leaflet.js versi 1.9.4 dengan tile provider OpenStreetMap dan ESRI Satellite untuk komponen peta WebGIS
5. THE Sistem SHALL menggunakan custom guard 'admin' pada konfigurasi Laravel Auth untuk autentikasi

### Requirement 16: Logika Penentuan Dosis Rule Base SPK

**User Story:** Sebagai admin, saya ingin dosis pupuk ditentukan berdasarkan formula ilmiah yang mempertimbangkan umur tanaman, jenis tanah, dan topografi, agar rekomendasi akurat sesuai pedoman PPKS.

#### Acceptance Criteria

1. THE Rule_Base_SPK SHALL menghitung dosis akhir dengan formula: dosis = base_dosis_umur × multiplier_jenis_tanah × multiplier_topografi, dimana base dosis ditentukan oleh kategori umur tanaman
2. THE Rule_Base_SPK SHALL menggunakan base dosis Urea per kategori: Belum Menghasilkan=0.5, Remaja=1.5, Menghasilkan Muda=2.25, Menghasilkan Tua=2.75, Tua Renta=1.5 (kg/pokok)
3. THE Rule_Base_SPK SHALL menggunakan base dosis KCl per kategori: Belum Menghasilkan=0.5, Remaja=1.0, Menghasilkan Muda=1.75, Menghasilkan Tua=2.25, Tua Renta=1.5 (kg/pokok)
4. THE Rule_Base_SPK SHALL membulatkan dosis akhir ke 0.25 terdekat untuk kemudahan takaran praktis di lapangan
5. THE Rule_Base_SPK SHALL menentukan status pemupukan berdasarkan rata-rata total multiplier: Segera Pupuk jika multiplier > 1.2, Tunda Pemupukan jika multiplier < 0.9, Pemupukan Normal untuk sisanya

### Requirement 17: Logika Evaluasi Rule RBS

**User Story:** Sebagai admin, saya ingin evaluasi RBS menggunakan logika AND yang ketat dengan dukungan NULL sebagai wildcard, agar rules hanya terpicu ketika semua kondisi yang didefinisikan benar-benar terpenuhi.

#### Acceptance Criteria

1. THE Mesin_Inferensi_RBS SHALL mengevaluasi kondisi_warna_daun dengan pencocokan exact match antara nilai rule dan data kondisi lahan
2. THE Mesin_Inferensi_RBS SHALL mengevaluasi pH tanah menggunakan range check: kondisi_ph_min <= ph_tanah <= kondisi_ph_max; evaluasi pH hanya dilakukan jika data ph_tanah pada kondisi lahan tersedia (non-null)
3. THE Mesin_Inferensi_RBS SHALL mengevaluasi kondisi_defisiensi dengan memeriksa apakah nilai defisiensi pada rule terdapat dalam array gejala_defisiensi dari data kondisi lahan (array contains check)
4. THE Mesin_Inferensi_RBS SHALL mengevaluasi kondisi kelembaban, musim, drainase, pelepah, dan tandan dengan pencocokan exact match
5. THE Mesin_Inferensi_RBS SHALL mengevaluasi ada_serangan_hama hanya jika rule menetapkan nilai true; jika kondisi lahan tidak melaporkan serangan hama, rule dengan kondisi ada_serangan_hama=true tidak terpicu
6. THE Mesin_Inferensi_RBS SHALL mengevaluasi kondisi_kategori_umur dengan mencocokkan ke accessor kategori_umur dari tabel kriteria lahan blok yang bersangkutan
