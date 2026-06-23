# PROJECT INDEX — SPK Sawit
## Sistem Pendukung Keputusan Pemupukan Kelapa Sawit Berbasis WebGIS dengan Metode Rule-Based System

**Tanggal Index:** 19 Juni 2026  
**Judul Skripsi:** "Rancang Bangun WebGIS Sistem Pemupukan Kelapa Sawit Menggunakan Metode Rule-Based System"  
**Framework:** Laravel 11 | PHP 8.2  
**Frontend:** Blade + Tailwind CSS v4 + Vite 5  
**Database:** MySQL (DB: `sawit_spk`)  
**Peta:** Leaflet.js 1.9.4 + OpenStreetMap + ESRI Satellite  
**PDF:** barryvdh/laravel-dompdf  
**Auth:** Custom guard `admin` (single-tenant, single-role)

---

## 1. Ringkasan Aplikasi

Aplikasi web single-tenant untuk kelompok tani kelapa sawit yang menentukan dosis pupuk (Urea & KCl) per blok lahan menggunakan **Rule-Based System (RBS)** berbasis Forward Chaining. Sistem mendiagnosis kondisi tanaman berdasarkan gejala visual dan kondisi lingkungan, lalu memberikan rekomendasi pemupukan lengkap dengan:

- **Perhitungan dosis otomatis** (base × multiplier tanah × topografi × koreksi waktu)
- **Confidence score** (0–100) untuk tingkat keyakinan rekomendasi
- **Validitas rekomendasi** (Cukup Kuat / Estimasi Visual)
- **Jadwal pemupukan 2 tahap** berdasarkan status kebutuhan
- **Histori analisis** per blok lahan
- **Peta WebGIS interaktif** dengan polygon GeoJSON berwarna sesuai status

**Pengguna:** 1 admin (ketua/sekretaris kelompok tani) — akses penuh ke semua modul.

---

## 2. Struktur Direktori Lengkap

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AnggotaController.php         — CRUD anggota kelompok tani
│   │   ├── AuthController.php            — Login/Logout (guard: admin)
│   │   ├── BlokLahanController.php       — CRUD blok lahan + kriteria agronomis
│   │   ├── Controller.php                — Base controller
│   │   ├── DashboardController.php       — WebGIS peta interaktif + statistik
│   │   ├── KondisiLahanController.php    — CRUD observasi kondisi lahan
│   │   ├── LaporanController.php         — Laporan, PDF, ringkasan teks
│   │   ├── RbsController.php             — Analisis RBS (single/batch)
│   │   ├── RealisasiPemupukanController.php — Catat realisasi pemupukan
│   │   └── RuleBaseController.php        — CRUD rule RBS
│   └── Middleware/
│       └── AdminAuthenticated.php        — Cek sesi guard admin
├── Models/
│   ├── Admin.php                         — Authenticatable user
│   ├── Anggota.php                       — Anggota kelompok tani
│   ├── BlokLahan.php                     — Blok lahan + kriteria terintegrasi
│   ├── KondisiLahan.php                  — Observasi kondisi periodik
│   ├── RealisasiPemupukan.php            — Data realisasi pemupukan
│   ├── RekomendasiRbs.php                — Hasil analisis RBS (histori)
│   └── RuleBaseLanjutan.php              — Aturan rule-based system
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── RbsService.php                    — Engine RBS + perhitungan dosis + confidence

bootstrap/
├── app.php                               — Middleware alias + trustProxies (ngrok)
└── providers.php

config/
├── app.php, auth.php, cache.php, database.php, filesystems.php
├── logging.php, mail.php, queue.php, services.php, session.php

database/
├── migrations/                           — 26 file migrasi
├── seeders/
│   ├── AdminSeeder.php                   — 1 admin default (admin/admin123)
│   ├── DatabaseSeeder.php                — Calls Admin + RuleBaseLanjutan
│   └── RuleBaseLanjutanSeeder.php        — 22 rules RBS bawaan
└── database.sqlite

resources/views/
├── anggota/              — index, create, edit
├── auth/                 — login
├── blok_lahan/           — index, create, edit, show
├── components/           — filter-searchable, searchable-select, status-badge
├── dashboard/            — index (WebGIS Leaflet.js)
├── kondisi_lahan/        — index, create, edit
├── laporan/              — index, show, pdf
├── layouts/              — app.blade.php (layout utama + sidebar)
├── rbs/                  — index, detail, partials/
├── rule_base/            — index, create, edit, info
└── vendor/pagination/

routes/
└── web.php               — Semua route (auth + protected)
```

---

## 3. Models & Relasi Database

### 3.1 Admin
**Tabel:** `admins`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| username | varchar(50) | Unique |
| password | varchar | Hashed (bcrypt) |
| nama_lengkap | varchar(100) | |

**Relasi:** —  
**Catatan:** Extends `Authenticatable`. Cast password → hashed.

---

### 3.2 Anggota
**Tabel:** `anggotas`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| nama | varchar(100) | Unique |
| no_hp | varchar(20) | Nullable |
| alamat | text | Nullable |

**Relasi:** `hasMany` → BlokLahan  
**Accessor:** `jumlah_blok` — count blok lahan

---

### 3.3 BlokLahan
**Tabel:** `blok_lahans`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| anggota_id | FK → anggotas | Nullable, nullOnDelete |
| nama_blok | varchar(100) | |
| luas_ha | double | Hektar |
| sph | integer | Standar Pohon per Hektar |
| koordinat_geojson | longtext | GeoJSON Polygon |
| tahun_tanam | integer | Nullable |
| jenis_tanah | varchar(255) | 10 pilihan |
| topografi | varchar(50) | 3 pilihan |

**Relasi:**
- `belongsTo` → Anggota
- `hasMany` → KondisiLahan
- `hasOne (latestOfMany tanggal_observasi)` → kondisiTerbaru
- `hasMany` → RekomendasiRbs
- `hasOne (is_latest + latestOfMany tanggal_analisis)` → rekomendasiRbsTerbaru

**Accessor:**
- `nama_pemilik` — dari relasi anggota
- `umur_tanaman` — now().year - tahun_tanam
- `kategori_umur` — <3: Belum Menghasilkan, 3-8: Remaja, 9-14: Menghasilkan Muda, 15-25: Menghasilkan Tua, >25: Tua Renta

---

### 3.4 KondisiLahan
**Tabel:** `kondisi_lahans`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE |
| tanggal_observasi | date | |
| tanggal_pemupukan_terakhir | date | Nullable |
| ph_tanah | decimal(4,2) | Nullable, 3.0–8.0 |
| kelembaban_tanah | enum | Sangat Kering/Kering/Normal/Lembab/Sangat Lembab |
| curah_hujan_kategori | enum | Sangat Rendah/Rendah/Normal/Tinggi/Sangat Tinggi |
| musim_saat_ini | enum | Musim Hujan/Musim Kemarau/Peralihan |
| warna_daun | enum | 8 pilihan (Hijau Normal s/d Bercak Nekrotik) |
| kondisi_pelepah | enum | Normal/Patah/Kering Prematur/Pertumbuhan Terhambat |
| gejala_defisiensi | JSON | Array: ['N','P','K','Mg','B','Fe','Zn'] |
| kondisi_tandan | enum | Normal/Kecil/Rontok Prematur/Busuk Pangkal/Tidak Ada |
| kondisi_drainase | enum | Baik/Cukup/Buruk — Tergenang |
| ada_gulma_dominan | boolean | Default false |
| ada_serangan_hama | boolean | Default false |
| catatan_observasi | text | Nullable |

**Relasi:** `belongsTo` → BlokLahan, `hasMany` → RekomendasiRbs  
**Accessor:** `label_ph` — Sangat Masam / Masam / Agak Masam (Optimal) / Netral / Basa

---

### 3.5 RuleBaseLanjutan
**Tabel:** `rule_bases_lanjutan`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| **KONDISI (IF)** | | NULL = diabaikan (wildcard) |
| kondisi_warna_daun | varchar(100) | Nullable |
| kondisi_ph_min | decimal(4,2) | Nullable |
| kondisi_ph_max | decimal(4,2) | Nullable |
| kondisi_kelembaban | varchar(50) | Nullable |
| kondisi_curah_hujan_kategori | varchar(50) | Nullable |
| kondisi_musim | varchar(50) | Nullable |
| kondisi_drainase | varchar(50) | Nullable |
| kondisi_defisiensi | varchar(50) | Nullable, 1 unsur target |
| kondisi_kategori_umur | varchar(50) | Nullable |
| kondisi_pelepah | varchar(100) | Nullable |
| kondisi_tandan | varchar(100) | Nullable |
| ada_serangan_hama | boolean | Nullable, NULL=tidak cek |
| ada_gulma_dominan | boolean | Nullable, NULL=tidak cek |
| kondisi_intermediate | JSON | Nullable, flag output intermediate |
| prasyarat_intermediate | JSON | Nullable, flag input intermediate |
| **OUTPUT (THEN)** | | |
| indikasi_masalah | varchar(255) | Required |
| jenis_pupuk_utama | varchar(100) | Required |
| jenis_pupuk_pendukung | varchar(100) | Nullable |
| dosis_anjuran | varchar(150) | Required |
| metode_aplikasi | varchar(255) | Nullable |
| waktu_aplikasi | varchar(150) | Nullable |
| saran_tindakan | text | Required |
| status_kebutuhan | enum | Darurat/Segera/Normal/Tunda |
| prioritas | tinyint | 1 (tertinggi) – 10 (terendah) |
| aktif | boolean | Default true |
| keterangan_rule | text | Nullable |

**Scope:** `aktif` — hanya rule aktif  
**Catatan:** Rule chaining via `kondisi_intermediate` dan `prasyarat_intermediate`

---

### 3.6 RekomendasiRbs
**Tabel:** `rekomendasi_rbs`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE |
| kondisi_lahan_id | FK → kondisi_lahans | CASCADE |
| admin_id | FK → admins | CASCADE |
| tanggal_analisis | date | |
| is_latest | boolean | True = rekomendasi aktif |
| nomor_analisis | integer | Counter per blok |
| rules_terpicu | JSON | [{rule_id, indikasi, pupuk, status, prioritas}] |
| masalah_teridentifikasi | JSON | Array string |
| rekomendasi_pupuk | JSON | [{jenis_utama, jenis_pendukung, dosis, metode, waktu}] |
| saran_tindakan_utama | text | |
| status_kebutuhan_dominan | enum | Darurat/Segera/Normal/Tunda |
| jumlah_rule_terpicu | tinyint | |
| dosis_urea | double | kg/pokok |
| dosis_kcl | double | kg/pokok |
| total_urea | double | kg total untuk blok |
| total_kcl | double | kg total untuk blok |
| catatan_dosis | text | Catatan kontekstual |
| jadwal_pemupukan | JSON | Array [{tahap, urea_kg, kcl_kg, ...}] |
| validitas_rekomendasi | varchar | Cukup Kuat / Estimasi Visual |
| catatan_validitas | text | |
| confidence_score | integer | 0–100 |
| confidence_label | varchar | Tinggi/Sedang/Rendah |
| catatan_confidence | text | |
| data_cukup | boolean | |
| data_kurang | JSON | Array field yang kosong |
| notifikasi_data | text | Pesan kecukupan data |

**Relasi:**
- `belongsTo` → BlokLahan, KondisiLahan, Admin
- `hasOne` → RealisasiPemupukan

**Accessor:**
- `warna_badge` — red/orange/green/gray/blue sesuai status
- `label_status` — Defisiensi Berat / Perlu Pupuk / Sehat / Tunda Pupuk
- `karung_urea` — ceil(total_urea / 50)
- `karung_kcl` — ceil(total_kcl / 50)
- `warna_confidence` — green/blue/amber
- `warna_validitas` — green/blue/amber

---

### 3.7 RealisasiPemupukan
**Tabel:** `realisasi_pemupukans`  
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| rekomendasi_rbs_id | FK → rekomendasi_rbs | CASCADE |
| admin_id | FK → admins | CASCADE |
| tanggal_realisasi | date | |
| jumlah_urea_realisasi | decimal(8,2) | Default 0 |
| jumlah_kcl_realisasi | decimal(8,2) | Default 0 |
| catatan_pelaksana | text | Nullable |

**Relasi:** `belongsTo` → RekomendasiRbs, Admin

---

## 4. Routes (web.php)

### Authentication (Guest Only)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | /login | login | AuthController@showLoginForm |
| POST | /login | login.submit | AuthController@login |
| POST | /logout | logout | AuthController@logout |

### Protected (AdminAuthenticated Middleware)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | / | — | Redirect → dashboard |
| GET | /dashboard | dashboard | DashboardController@index |

**Anggota (Resource tanpa show):**
| Method | URI | Name |
|--------|-----|------|
| GET | /anggota | anggota.index |
| GET | /anggota/create | anggota.create |
| POST | /anggota | anggota.store |
| GET | /anggota/{anggotum}/edit | anggota.edit |
| PUT | /anggota/{anggotum} | anggota.update |
| DELETE | /anggota/{anggotum} | anggota.destroy |

**Blok Lahan (Full Resource):**
| Method | URI | Name |
|--------|-----|------|
| GET | /blok-lahan | blok-lahan.index |
| GET | /blok-lahan/create | blok-lahan.create |
| POST | /blok-lahan | blok-lahan.store |
| GET | /blok-lahan/{blokLahan} | blok-lahan.show |
| GET | /blok-lahan/{blokLahan}/edit | blok-lahan.edit |
| PUT | /blok-lahan/{blokLahan} | blok-lahan.update |
| DELETE | /blok-lahan/{blokLahan} | blok-lahan.destroy |

**Kondisi Lahan (Resource tanpa show):**
| Method | URI | Name |
|--------|-----|------|
| GET | /kondisi-lahan | kondisi-lahan.index |
| GET | /kondisi-lahan/create | kondisi-lahan.create |
| POST | /kondisi-lahan | kondisi-lahan.store |
| GET | /kondisi-lahan/{kondisiLahan}/edit | kondisi-lahan.edit |
| PUT | /kondisi-lahan/{kondisiLahan} | kondisi-lahan.update |
| DELETE | /kondisi-lahan/{kondisiLahan} | kondisi-lahan.destroy |

**Rule Base (Resource tanpa show + info):**
| Method | URI | Name |
|--------|-----|------|
| GET | /rule-base/info | rule-base.info |
| GET | /rule-base | rule-base.index |
| GET | /rule-base/create | rule-base.create |
| POST | /rule-base | rule-base.store |
| GET | /rule-base/{ruleBase}/edit | rule-base.edit |
| PUT | /rule-base/{ruleBase} | rule-base.update |
| DELETE | /rule-base/{ruleBase} | rule-base.destroy |

**Analisis RBS:**
| Method | URI | Name |
|--------|-----|------|
| GET | /rbs | rbs.index |
| GET | /rbs/daftar-blok-belum-analisis | rbs.daftarBlokBelumAnalisis |
| POST | /rbs/analisis/{blokLahan} | rbs.analisis |
| POST | /rbs/analisis-semua | rbs.analisisSemua |
| GET | /rbs/detail/{blokLahan} | rbs.detail |

**Laporan:**
| Method | URI | Name |
|--------|-----|------|
| GET | /laporan | laporan.index |
| GET | /laporan/{rekomendasiRbs} | laporan.show |
| GET | /laporan/{rekomendasiRbs}/pdf | laporan.pdf |
| GET | /laporan/{rekomendasiRbs}/ringkasan | laporan.ringkasan |

**Realisasi Pemupukan:**
| Method | URI | Name |
|--------|-----|------|
| POST | /realisasi-pemupukan | realisasi.store |
| DELETE | /realisasi-pemupukan/{realisasiPemupukan} | realisasi.destroy |

**API (WebGIS Popup):**
| Method | URI | Name |
|--------|-----|------|
| GET | /api/rbs-popup/{blokLahan} | api.rbs.popup |

---

## 5. Controllers — Detail Method

### 5.1 AuthController
| Method | Fungsi |
|--------|--------|
| showLoginForm() | Tampilkan form login (redirect jika sudah login) |
| login() | Validasi credentials, Auth::guard('admin')->attempt() |
| logout() | Logout, invalidate session, regenerate token |

### 5.2 DashboardController
| Method | Fungsi |
|--------|--------|
| index() | Load semua blok + relasi, build mapData JSON (GeoJSON, status, dosis), stats cards, delta bulan lalu, blok perlu perhatian (>90 hari tanpa analisis) |

### 5.3 AnggotaController
| Method | Fungsi |
|--------|--------|
| index() | List paginated (10/page) + withCount blokLahans |
| create() | Form tambah |
| store() | Validasi (nama unique), simpan |
| edit() | Form edit |
| update() | Validasi, update |
| destroy() | Cek relasi blokLahans, tolak jika masih ada |

### 5.4 BlokLahanController
| Method | Fungsi |
|--------|--------|
| index() | List grouped by anggota, filter by anggota/status RBS |
| create() | Form + load existing bloks untuk overlay peta |
| store() | Validasi (GeoJSON check), simpan, warning SPH di luar 100-160 |
| show() | Detail blok + kondisi + rekomendasi |
| edit() | Form edit + peta existing |
| update() | Validasi, update, warning SPH |
| destroy() | Hapus (cascade) |

### 5.5 KondisiLahanController
| Method | Fungsi |
|--------|--------|
| index() | List grouped by anggota, filter by blok/anggota |
| create() | Form wizard, cascading filter blok per anggota (JSON) |
| store() | Validasi, konsistensi logis (warning), simpan |
| edit() | Form edit |
| update() | Validasi, konsistensi logis, update |
| destroy() | Hapus |

### 5.6 RuleBaseController
| Method | Fungsi |
|--------|--------|
| index() | List semua rule (ordered by prioritas, status) |
| info() | Halaman penjelasan cara kerja RBS |
| create() | Form tambah rule |
| store() | Validasi multi-field, handle boolean/null, simpan |
| edit() | Form edit rule |
| update() | Validasi, update |
| destroy() | Hapus |

### 5.7 RbsController
| Method | Fungsi |
|--------|--------|
| index() | Daftar blok + status analisis, grouped by anggota, stats, filter |
| analisis(BlokLahan) | Jalankan RbsService->analisis() untuk 1 blok |
| analisisSemua() | Batch: RbsService->analisisSemua() |
| detail(BlokLahan) | Detail hasil analisis + histori rekomendasi sebelumnya |
| apiPopup(BlokLahan) | JSON endpoint untuk popup peta WebGIS |
| daftarBlokBelumAnalisis() | JSON: daftar blok yang punya kondisi (untuk AJAX) |

### 5.8 LaporanController
| Method | Fungsi |
|--------|--------|
| index() | Rekap semua rekomendasi, grouped by anggota, grand total (hanya Normal+Segera), filter |
| show(RekomendasiRbs) | Detail 1 rekomendasi lengkap |
| exportPdf(RekomendasiRbs) | Generate PDF via DomPDF |
| exportRingkasan(RekomendasiRbs) | Teks ringkasan WhatsApp-friendly (plain text / JSON) |

### 5.9 RealisasiPemupukanController
| Method | Fungsi |
|--------|--------|
| store() | Validasi, updateOrCreate (1 realisasi per rekomendasi) |
| destroy() | Hapus realisasi |

---

## 6. Service Layer — RbsService (Engine Utama)

### 6.1 Alur Analisis RBS (Forward Chaining)
```
analisis(BlokLahan)
├── 1. Ambil kondisi terbaru (latestOfMany)
├── 2. Cek kecukupan data (minimal 5/7 field terisi)
├── 3. Cek apakah minimal 1 field kondisi terisi
├── 4. Ambil kategori umur dari accessor blok
├── 5. Load semua rule aktif (ordered by prioritas)
├── 6. Evaluasi setiap rule (Forward Chaining + Rule Chaining):
│   ├── Cek prasyarat intermediate (rule chaining A2)
│   ├── Evaluasi kondisi (AND logic, NULL di rule = skip):
│   │   ├── warna_daun (exact match)
│   │   ├── pH (range: min ≤ pH ≤ max)
│   │   ├── kelembaban (exact match)
│   │   ├── curah_hujan_kategori (exact match)
│   │   ├── musim (exact match)
│   │   ├── drainase (exact match)
│   │   ├── defisiensi (array contains)
│   │   ├── pelepah (exact match)
│   │   ├── serangan hama (boolean check)
│   │   ├── gulma dominan (boolean check)
│   │   ├── tandan (exact match)
│   │   └── kategori umur (exact match)
│   └── Jika terpicu → tambah intermediate flags
├── 7. Jika 0 rule terpicu → status Normal
└── 8. Susun hasil:
    ├── Status dominan (Darurat>Segera>Normal>Tunda)
    ├── Masalah unik dari semua rule terpicu
    ├── Pupuk deduplicate by jenis_utama
    ├── Saran top 3 prioritas
    ├── Hitung dosis standar
    ├── Tentukan catatan dosis kontekstual
    ├── Generate jadwal pemupukan 2 tahap
    ├── Tentukan validitas rekomendasi
    ├── Hitung confidence score
    └── Simpan dengan histori (create, is_latest)
```

### 6.2 Formula Dosis Standar
```
dosis_final = base_dosis × multiplier_tanah × multiplier_topografi × multiplier_waktu
total = dosis_final × SPH × luas_ha
(dibulatkan ke 0.25 terdekat)
```

**Base Dosis per Kategori Umur:**
| Kategori | Urea (kg/pokok) | KCl (kg/pokok) |
|----------|:---------------:|:--------------:|
| Belum Menghasilkan (<3 th) | 0.50 | 0.50 |
| Remaja (3-8 th) | 1.50 | 1.00 |
| Menghasilkan Muda (9-14 th) | 2.25 | 1.75 |
| Menghasilkan Tua (15-25 th) | 2.75 | 2.25 |
| Tua Renta (>25 th) | 1.50 | 1.50 |

**Multiplier Jenis Tanah:**
| Jenis Tanah | Urea | KCl |
|-------------|:----:|:---:|
| Tanah Lempung | 1.0 | 1.0 |
| Tanah Lempung Berpasir | 1.1 | 1.1 |
| Tanah Berpasir | 1.3 | 1.4 |
| Tanah Liat | 0.9 | 0.9 |
| Tanah Gambut | 0.6 | 1.5 |
| Tanah Aluvial | 1.0 | 1.0 |
| Tanah PMK | 1.1 | 1.2 |
| Tanah Laterit | 1.1 | 1.2 |
| Tanah Berbatu | 1.2 | 1.2 |
| Lainnya | 1.0 | 1.0 |

**Multiplier Topografi:**
| Topografi | Urea | KCl |
|-----------|:----:|:---:|
| Datar 0-15° | 1.0 | 1.0 |
| Bergelombang 15-30° | 1.1 | 1.1 |
| Curam >30° | 1.2 | 1.2 |

**Multiplier Koreksi Waktu Pemupukan Terakhir:**
| Jarak (hari) | Multiplier | Keterangan |
|:------------:|:----------:|------------|
| < 60 | 0.75 | Masih baru, kurangi dosis |
| 60–120 | 1.0 | Jadwal normal |
| > 120 | 1.25 | Terlambat, dosis ditingkatkan |

### 6.3 Confidence Score (0–100)

| Komponen | Bobot Maks | Perhitungan |
|----------|:----------:|-------------|
| A. Kelengkapan Data | 40 poin | (field_terisi / 8) × 40 |
| B. Jumlah Rule Terpicu | 25 poin | ≥3→25, 2→18, 1→12, 0→5 |
| C. Kesesuaian Visual-Unsur | 20 poin | Cocok mapping→20, Tidak cocok→10, Sebagian→5 |
| D. Penalti Kontradiksi | -20 poin | -10 per kontradiksi (maks -20) |

**Label:** ≥75 = Tinggi, ≥50 = Sedang, <50 = Rendah

### 6.4 Validitas Rekomendasi

| Level | Syarat |
|-------|--------|
| Cukup Kuat | warna_daun + pH + (kelembaban ATAU curah_hujan) + drainase |
| Estimasi Visual | Data di atas tidak lengkap |

### 6.5 Jadwal Pemupukan 2 Tahap

| Status | Pembagian Tahap 1 / Tahap 2 |
|--------|:----------------------------:|
| Darurat | 70% / 30% |
| Segera | 60% / 40% |
| Normal | 50% / 50% |
| Tunda | Tidak dijadwalkan |

### 6.6 Histori Analisis
- Setiap analisis baru membuat record baru (`create`, bukan `updateOrCreate`)
- Record lama di-set `is_latest = false`
- Field `nomor_analisis` auto-increment per blok
- Histori bisa dilihat di halaman detail RBS

---

## 7. Autentikasi & Middleware

- **Guard:** `admin` (session-based, Eloquent provider → App\Models\Admin)
- **Middleware:** `AdminAuthenticated` — redirect ke login jika belum login
- **Alias:** `auth.admin` (didefinisikan di bootstrap/app.php)
- **Trust Proxies:** `*` (untuk ngrok)
- **Credentials:** username + password
- **Seeder Default:** admin / admin123

---

## 8. Seeder Data Bawaan (22 Rules RBS)

| # | Grup | Kondisi Utama | Status | Prioritas |
|---|------|---------------|--------|:---------:|
| 1 | Defisiensi N | Kuning Merata + N | Segera | 2 |
| 2 | Defisiensi N Ringan | Hijau Pucat + N | Normal | 4 |
| 3 | Defisiensi K (Berat) | Oranye/Kemerahan + K | Darurat | 1 |
| 4 | Defisiensi K (Sedang) | Kuning Tepi + K | Segera | 2 |
| 5 | Defisiensi Mg | Kuning Antar Tulang + Mg | Segera | 3 |
| 6 | Defisiensi B | Pertumbuhan Terhambat + B | Segera | 2 |
| 7 | pH Sangat Masam | pH 3.0–4.5 | Darurat | 1 |
| 8 | pH Masam | pH 4.5–5.5 | Normal | 4 |
| 9 | Drainase Buruk | Tergenang + Sangat Lembab | Tunda | 1 |
| 10 | Kemarau Parah | Kemarau + Sangat Kering | Tunda | 2 |
| 11 | Kemarau Sedang | Kemarau + Kering | Normal | 5 |
| 12 | Tanaman Muda Defisiensi | Belum Menghasilkan + Kuning | Segera | 2 |
| 13 | Tanaman Tua Renta | Tua Renta | Tunda | 8 |
| 14 | Kondisi Normal | Hijau + pH 5.5-6.5 + Baik | Normal | 9 |
| 15 | Defisiensi P | Coklat Ujung + P | Segera | 3 |
| 16 | Hama + Bercak | Bercak Nekrotik | Segera | 2 |
| 17 | Defisiensi Fe | Kuning Antar Tulang + Fe + pH>6.5 | Segera | 3 |
| 18 | Pelepah Kering | Kering Prematur | Segera | 3 |
| 19 | Tandan Rontok | Rontok Prematur | Segera | 2 |
| 20 | Busuk Pangkal | Busuk Pangkal | Darurat | 1 |
| 21 | Musim Hujan Optimal | Hujan + Normal | Normal | 6 |
| 22 | Defisiensi Zn | Kuning Merata + Zn | Segera | 3 |

---

## 9. Views & Komponen UI

### Layout Utama (`layouts/app.blade.php`)
- Sidebar fixed dengan collapse/expand (desktop & mobile)
- Topbar sticky: hamburger + page title + tanggal
- Flash messages: success/error/warning
- Global confirm modal: `confirmDelete()`, `confirmLogout()`, `showConfirm()`
- Leaflet z-index fix, responsive utilities

### Halaman per Modul
| Modul | Views | Deskripsi |
|-------|-------|-----------|
| Auth | login | Form login (username + password) |
| Dashboard | index | Peta WebGIS, stats cards, delta bulan lalu, blok perlu perhatian |
| Anggota | index, create, edit | CRUD paginated (10/page) |
| Blok Lahan | index, create, edit, show | CRUD + draw polygon GeoJSON + detail |
| Kondisi Lahan | index, create, edit | Form wizard 5 seksi, cascading filter |
| Rule Base | index, create, edit, info | CRUD rules + halaman penjelasan RBS |
| RBS | index, detail | Daftar analisis + detail + histori |
| Laporan | index, show, pdf | Rekap, detail, export PDF |

### Blade Components
- `filter-searchable` — Dropdown filter dengan live search
- `searchable-select` — Select input dengan live search (dipakai di form)
- `status-badge` — Badge warna otomatis sesuai status

---

## 10. Fitur-Fitur Kunci

| # | Fitur | Deskripsi |
|---|-------|-----------|
| 1 | **Histori Analisis** | Setiap analisis tersimpan sebagai record baru, histori bisa dilihat per blok |
| 2 | **Jadwal Pemupukan 2 Tahap** | Pembagian dosis berdasarkan status (Darurat 70/30, Segera 60/40, Normal 50/50) |
| 3 | **Validitas Rekomendasi** | Cukup Kuat vs Estimasi Visual berdasarkan kelengkapan data |
| 4 | **Curah Hujan & Gulma** | Parameter tambahan yang dievaluasi oleh rules RBS |
| 5 | **WebGIS Dashboard** | Peta interaktif Leaflet.js, polygon warna status, popup detail, layer switching |
| 6 | **Confidence Score** | Skor 0–100 dengan label Tinggi/Sedang/Rendah |
| 7 | **Kecukupan Data** | Notifikasi jika data observasi belum lengkap |
| 8 | **Batch Analisis** | Analisis semua blok sekaligus |
| 9 | **Realisasi Pemupukan** | Catat pelaksanaan pupuk vs rekomendasi |
| 10 | **Export PDF** | Laporan per rekomendasi via DomPDF |
| 11 | **Export Ringkasan Teks** | Format WhatsApp-friendly (plain text) |
| 12 | **Validasi Konsistensi Logis** | Warning jika data kontradiktif (musim vs kelembaban, daun vs defisiensi) |
| 13 | **Grouped by Anggota** | Semua list di-group per anggota, sorted by activity terbaru |
| 14 | **Delta Stats** | Perbandingan status bulan ini vs bulan lalu |
| 15 | **Blok Perlu Perhatian** | Alert blok yang >90 hari tanpa analisis |
| 16 | **Rule Chaining** | Intermediate flags untuk evaluasi bertingkat antar rule |

---

## 11. Dependencies

### PHP (composer.json)
| Package | Versi | Fungsi |
|---------|-------|--------|
| php | ^8.2 | Runtime |
| laravel/framework | ^11.0 | Framework utama |
| barryvdh/laravel-dompdf | ^3.1 | PDF generation |
| laravel/tinker | ^2.9 | REPL development |

### JavaScript (package.json)
| Package | Fungsi |
|---------|--------|
| tailwindcss ^4.3.0 | CSS framework |
| @tailwindcss/forms | Form styling |
| @tailwindcss/vite | Vite plugin |
| laravel-vite-plugin ^1.0 | Laravel + Vite bridge |
| vite ^5.0 | Build tool |
| axios ^1.6.4 | HTTP client |

### CDN (dimuat langsung di layout)
| Library | Fungsi |
|---------|--------|
| Leaflet.js 1.9.4 | Peta interaktif |
| Leaflet Draw 1.0.4 | Draw polygon |
| OpenStreetMap | Base map layer |
| ESRI Satellite | Satellite layer |

---

## 12. Cara Menjalankan Project

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup database (pastikan MySQL aktif, buat DB 'sawit_spk')
php artisan migrate
php artisan db:seed

# 4. Build assets
npm run build          # untuk production/ngrok
# ATAU
npm run dev            # untuk development (live reload)

# 5. Jalankan server
php artisan serve      # http://localhost:8000

# 6. (Opsional) Online via ngrok
ngrok http 8000
```

**Login default:** username: `admin`, password: `admin123`

---

## 13. Mapping Status & Label

| Status DB | Label Tampilan | Warna Badge | Keterangan |
|-----------|----------------|:-----------:|------------|
| Darurat | Defisiensi Berat | 🔴 Red | Masalah kritis, perlu penanganan |
| Segera | Perlu Pupuk | 🟠 Orange | Segera aplikasikan pupuk |
| Normal | Sehat | 🟢 Green | Kondisi baik, pupuk standar |
| Tunda | Tunda Pupuk | ⚪ Gray | Tunda pemupukan (masalah lain) |
| null | Belum Dicek | 🔵 Blue | Belum pernah dianalisis |

---

## 14. Diagram Relasi Antar Entitas

```
┌─────────┐     ┌──────────────┐     ┌──────────────────┐
│ admins  │     │   anggotas   │     │ rule_bases_      │
│         │     │              │     │ lanjutan         │
└────┬────┘     └──────┬───────┘     │ (22 rules)      │
     │                 │              └────────┬─────────┘
     │                 │ 1:N                   │
     │                 ▼                       │ evaluasi
     │         ┌──────────────┐                │
     │         │ blok_lahans  │                │
     │         │              │                │
     │         └──┬───────┬───┘                │
     │            │       │                    │
     │         1:N│       │1:N                 │
     │            ▼       ▼                    │
     │   ┌────────────┐  ┌────────────────┐   │
     │   │ kondisi_   │  │ rekomendasi_   │◀──┘
     │   │ lahans     │  │ rbs (histori)  │
     │   └────────────┘  └───────┬────────┘
     │                           │
     │         admin_id FK       │ 1:1
     └───────────────────────────┤
                                 ▼
                        ┌────────────────┐
                        │ realisasi_     │
                        │ pemupukans     │
                        └────────────────┘
```

---

## 15. Catatan Implementasi

- **Single-tenant:** 1 kelompok tani, 1 admin
- **Histori:** Setiap analisis membuat record baru (bukan updateOrCreate), `is_latest` flag
- **Kriteria terintegrasi:** tahun_tanam, jenis_tanah, topografi sudah merged ke tabel `blok_lahans`
- **Status label mapping:** Darurat→Defisiensi Berat, Segera→Perlu Pupuk, Normal→Sehat, Tunda→Tunda Pupuk
- **Karung:** 1 karung = 50 kg, dihitung dengan ceiling
- **Laporan total:** Hanya menghitung dari status Normal + Segera (Darurat/Tunda memiliki catatan khusus)
- **Konsistensi logis:** Validasi tidak menggagalkan simpan, hanya menampilkan warning
- **Rule NULL:** Kondisi NULL di rule berarti "tidak dicek" (wildcard), bukan "harus null"
- **Confidence penalti:** Kontradiksi data (misalnya musim kemarau + kelembaban tinggi) mengurangi skor
- **Time correction:** Jarak pemupukan terakhir mempengaruhi dosis (×0.75 jika <60 hari, ×1.25 jika >120 hari)
