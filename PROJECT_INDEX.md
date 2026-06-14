# PROJECT INDEX — SPK Sawit (Sistem Pendukung Keputusan Pemupukan Kelapa Sawit)

**Tanggal Index:** 14 Juni 2026  
**Framework:** Laravel 11 | PHP 8.2  
**Frontend:** Blade + Tailwind CSS v4 + Vite 5  
**Database:** MySQL  
**Peta:** Leaflet.js + OpenStreetMap + ESRI Satellite  
**PDF:** barryvdh/laravel-dompdf

---

## 1. Ringkasan Aplikasi

Aplikasi web single-tenant untuk kelompok tani kelapa sawit yang menentukan dosis pupuk (Urea & KCl) per blok lahan menggunakan **Rule-Based System (RBS)** berbasis gejala visual dan kondisi lingkungan. Dilengkapi peta interaktif WebGIS dan perhitungan dosis otomatis berdasarkan parameter agronomis (umur, jenis tanah, topografi).

**Pengguna:** 1 admin (ketua/sekretaris kelompok tani) — akses penuh ke semua modul.

---

## 2. Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AnggotaController.php         — CRUD anggota kelompok tani
│   │   ├── AuthController.php            — Login/Logout (guard: admin)
│   │   ├── BlokLahanController.php       — CRUD blok lahan + kriteria
│   │   ├── Controller.php                — Base controller
│   │   ├── DashboardController.php       — WebGIS peta interaktif
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
│   ├── RekomendasiRbs.php                — Hasil analisis RBS
│   └── RuleBaseLanjutan.php              — Aturan rule-based system
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── RbsService.php                    — Engine RBS + perhitungan dosis

bootstrap/
├── app.php
└── providers.php

config/
├── app.php
├── auth.php              — Guard: admin (session + Eloquent Admin model)
├── cache.php
├── database.php
├── filesystems.php
├── logging.php
├── mail.php
├── queue.php
├── services.php
└── session.php

database/
├── migrations/           — 24 file migrasi
├── seeders/
│   ├── AdminSeeder.php           — 1 admin default (admin/admin123)
│   ├── DatabaseSeeder.php        — Calls Admin + RuleBaseLanjutan seeders
│   └── RuleBaseLanjutanSeeder.php — 22 rules RBS bawaan
└── database.sqlite

resources/views/
├── anggota/              — index, create, edit
├── auth/                 — login
├── blok_lahan/           — index, create, edit, show
├── components/           — filter-searchable, searchable-select, status-badge
├── dashboard/            — index (WebGIS)
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

**Relasi:**
- `hasMany` → BlokLahan

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
- `hasOne (latestOfMany tanggal_analisis)` → rekomendasiRbsTerbaru

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

**Relasi:**
- `belongsTo` → BlokLahan
- `hasMany` → RekomendasiRbs

**Accessor:** `label_ph` — kategori pH (Sangat Masam/Masam/Agak Masam/Netral/Basa)

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
| kondisi_musim | varchar(50) | Nullable |
| kondisi_drainase | varchar(50) | Nullable |
| kondisi_defisiensi | varchar(50) | Nullable, 1 unsur target |
| kondisi_kategori_umur | varchar(50) | Nullable |
| kondisi_pelepah | varchar(100) | Nullable |
| kondisi_tandan | varchar(100) | Nullable |
| ada_serangan_hama | boolean | Nullable, NULL=tidak cek |
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
| catatan_dosis | text | Nullable, catatan kontekstual |

**Relasi:**
- `belongsTo` → BlokLahan, KondisiLahan, Admin
- `hasOne` → RealisasiPemupukan

**Accessor:**
- `warna_badge` — red/orange/green/gray/blue sesuai status
- `label_status` — Defisiensi Berat/Perlu Pupuk/Sehat/Tunda Pupuk
- `karung_urea` — ceil(total_urea / 50)
- `karung_kcl` — ceil(total_kcl / 50)

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

**Relasi:**
- `belongsTo` → RekomendasiRbs, Admin

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

**Anggota (Resource minus show):**
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

**Kondisi Lahan (Resource minus show):**
| Method | URI | Name |
|--------|-----|------|
| GET | /kondisi-lahan | kondisi-lahan.index |
| GET | /kondisi-lahan/create | kondisi-lahan.create |
| POST | /kondisi-lahan | kondisi-lahan.store |
| GET | /kondisi-lahan/{kondisiLahan}/edit | kondisi-lahan.edit |
| PUT | /kondisi-lahan/{kondisiLahan} | kondisi-lahan.update |
| DELETE | /kondisi-lahan/{kondisiLahan} | kondisi-lahan.destroy |

**Rule Base (Resource minus show + info):**
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
| logout() | Logout, invalidate session |

### 5.2 DashboardController
| Method | Fungsi |
|--------|--------|
| index() | Load semua blok + relasi, build mapData (GeoJSON, status, dosis), stats cards, delta bulan lalu, blok perlu perhatian (>90 hari) |

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
| store() | Validasi, konsistensi logis (warning: musim vs kelembaban, daun vs defisiensi) |
| edit() | Form edit |
| update() | Validasi, konsistensi logis, update |
| destroy() | Hapus |

### 5.6 RuleBaseController
| Method | Fungsi |
|--------|--------|
| index() | List semua rule (ordered by prioritas, status) |
| info() | Halaman penjelasan RBS |
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
| detail(BlokLahan) | Detail hasil analisis 1 blok (rekomendasi + kondisi) |
| apiPopup(BlokLahan) | JSON endpoint untuk popup peta WebGIS |
| daftarBlokBelumAnalisis() | API: daftar blok yang punya kondisi (untuk AJAX) |

### 5.8 LaporanController
| Method | Fungsi |
|--------|--------|
| index() | Rekap semua rekomendasi, grouped by anggota, grand total (hanya Normal+Segera), filter |
| show(RekomendasiRbs) | Detail 1 rekomendasi |
| exportPdf(RekomendasiRbs) | Generate PDF via DomPDF |
| exportRingkasan(RekomendasiRbs) | Teks ringkasan WhatsApp-friendly |

### 5.9 RealisasiPemupukanController
| Method | Fungsi |
|--------|--------|
| store() | Validasi, updateOrCreate (1 realisasi per rekomendasi) |
| destroy() | Hapus realisasi |

---

## 6. Service Layer — RbsService

### Alur Analisis RBS
```
analisis(BlokLahan)
├── 1. Ambil kondisi terbaru (latestOfMany)
├── 2. Cek data cukup (minimal 1 field terisi)
├── 3. Ambil kategori umur dari blok
├── 4. Load semua rule aktif (ordered by prioritas)
├── 5. Evaluasi setiap rule:
│   ├── Cek prasyarat intermediate (rule chaining)
│   ├── Evaluasi kondisi (AND logic, NULL = skip)
│   │   ├── warna_daun (exact match)
│   │   ├── pH (range: min ≤ pH ≤ max)
│   │   ├── kelembaban (exact match)
│   │   ├── musim (exact match)
│   │   ├── drainase (exact match)
│   │   ├── defisiensi (array contains)
│   │   ├── pelepah (exact match)
│   │   ├── serangan hama (boolean check)
│   │   ├── tandan (exact match)
│   │   └── kategori umur (exact match)
│   └── Jika terpicu: tambah intermediate flags
├── 6. Jika 0 rule terpicu → status Normal
└── 7. Susun hasil:
    ├── Status dominan (Darurat>Segera>Normal>Tunda)
    ├── Masalah unik
    ├── Pupuk deduplicate by jenis_utama
    ├── Saran top 3 prioritas
    ├── Hitung dosis standar (base × tanah × topografi × waktu)
    ├── Tentukan catatan dosis kontekstual
    └── updateOrCreate ke rekomendasi_rbs
```

### Formula Dosis Standar
```
dosis_final = base_dosis × multiplier_tanah × multiplier_topografi × multiplier_waktu
total = dosis_final × SPH × luas_ha
```

**Base Dosis per Kategori Umur:**
| Kategori | Urea (kg/pokok) | KCl (kg/pokok) |
|----------|:---------------:|:--------------:|
| Belum Menghasilkan | 0.50 | 0.50 |
| Remaja | 1.50 | 1.00 |
| Menghasilkan Muda | 2.25 | 1.75 |
| Menghasilkan Tua | 2.75 | 2.25 |
| Tua Renta | 1.50 | 1.50 |

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
| < 60 | 0.75 | Masih baru |
| 60–120 | 1.0 | Normal |
| > 120 | 1.25 | Terlambat, dosis ditingkatkan |

---

## 7. Autentikasi & Middleware

- **Guard:** `admin` (session-based, Eloquent provider → App\Models\Admin)
- **Middleware:** `AdminAuthenticated` — redirect ke login jika belum login
- **Default Guard:** `admin` (bukan `web`)
- **Credentials:** username + password
- **Seeder Default:** admin / admin123

---

## 8. Seeder Data Bawaan

### AdminSeeder
- 1 admin: username=`admin`, password=`admin123`, nama=`Administrator`

### RuleBaseLanjutanSeeder (22 Rules)
| Grup | Kondisi Utama | Status | Prioritas |
|------|---------------|--------|:---------:|
| 1. Defisiensi N | Kuning Merata + N | Segera | 2 |
| 1. Defisiensi N Ringan | Hijau Pucat + N | Normal | 4 |
| 2. Defisiensi K (Berat) | Oranye/Kemerahan + K | Darurat | 1 |
| 2. Defisiensi K (Sedang) | Kuning Tepi + K | Segera | 2 |
| 3. Defisiensi Mg | Kuning Antar Tulang + Mg | Segera | 3 |
| 4. Defisiensi B | Pertumbuhan Terhambat + B | Segera | 2 |
| 5. pH Sangat Masam | pH 3.0–4.5 | Darurat | 1 |
| 5. pH Masam | pH 4.5–5.5 | Normal | 4 |
| 6. Drainase Buruk | Tergenang + Sangat Lembab | Tunda | 1 |
| 7. Kemarau Parah | Kemarau + Sangat Kering | Tunda | 2 |
| 7. Kemarau Sedang | Kemarau + Kering | Normal | 5 |
| 8. Tanaman Muda Defisiensi | Belum Menghasilkan + Kuning | Segera | 2 |
| 9. Tanaman Tua Renta | Tua Renta | Tunda | 8 |
| 10. Kondisi Normal | Hijau + pH 5.5-6.5 + Baik | Normal | 9 |
| 11. Defisiensi P | Coklat Ujung + P | Segera | 3 |
| 12. Hama + Bercak | Bercak Nekrotik | Segera | 2 |
| 13. Defisiensi Fe | Kuning Antar Tulang + Fe + pH>6.5 | Segera | 3 |
| 14. Pelepah Kering | Kering Prematur | Segera | 3 |
| 15. Tandan Rontok | Rontok Prematur | Segera | 2 |
| 15. Busuk Pangkal | Busuk Pangkal | Darurat | 1 |
| 16. Musim Hujan Optimal | Hujan + Normal | Normal | 6 |
| 17. Defisiensi Zn | Kuning Merata + Zn | Segera | 3 |
| 18. Serangan Hama | ada_serangan_hama=true | Segera | 3 |
| 19. Remaja Normal | Remaja + Hijau Normal | Normal | 6 |
| 20. TM Tua + Hijau Pucat | Menghasilkan Tua + Hijau Pucat | Normal | 5 |

---

## 9. Views & UI

### Layout
- `layouts/app.blade.php` — Layout utama dengan sidebar navigasi, Tailwind CSS, flash messages

### Halaman
| Modul | Views | Deskripsi |
|-------|-------|-----------|
| Auth | login | Form login (username + password) |
| Dashboard | index | Peta WebGIS Leaflet.js, stats cards, blok perlu perhatian |
| Anggota | index, create, edit | CRUD anggota (paginated) |
| Blok Lahan | index, create, edit, show | CRUD + peta draw GeoJSON + detail |
| Kondisi Lahan | index, create, edit | CRUD observasi, cascading filter |
| Rule Base | index, create, edit, info | CRUD rules + halaman penjelasan |
| RBS | index, detail | Daftar analisis + detail per blok |
| Laporan | index, show, pdf | Rekap, detail, export PDF |

### Components
- `filter-searchable` — Dropdown filter dengan search
- `searchable-select` — Select input dengan search
- `status-badge` — Badge warna status (Darurat/Segera/Normal/Tunda)

---

## 10. Fitur Kunci

1. **WebGIS Dashboard** — Peta interaktif Leaflet.js, poligon GeoJSON per blok, warna berdasarkan status, popup detail
2. **Rule-Based System (RBS)** — Forward chaining dengan rule chaining (intermediate flags), evaluasi multi-kondisi AND logic
3. **Perhitungan Dosis Otomatis** — Base × multiplier tanah × multiplier topografi × koreksi waktu pemupukan terakhir
4. **Batch Analisis** — Analisis semua blok sekaligus
5. **Realisasi Pemupukan** — Catat pelaksanaan pupuk vs rekomendasi
6. **Export PDF** — Laporan per rekomendasi via DomPDF
7. **Export Ringkasan Teks** — Format WhatsApp-friendly
8. **Validasi Konsistensi Logis** — Warning jika data kontradiktif (musim vs kelembaban, daun vs defisiensi)
9. **Grouped by Anggota** — Semua halaman list di-group per anggota, sorted by activity terbaru
10. **Delta Stats** — Perbandingan status bulan ini vs bulan lalu di dashboard

---

## 11. Dependencies

### PHP (composer.json)
| Package | Versi | Fungsi |
|---------|-------|--------|
| laravel/framework | ^11.0 | Framework |
| barryvdh/laravel-dompdf | ^3.1 | PDF generation |
| laravel/tinker | ^2.9 | REPL |

### JavaScript (package.json)
| Package | Versi | Fungsi |
|---------|-------|--------|
| tailwindcss | ^4.3.0 | CSS framework |
| @tailwindcss/forms | ^0.5.11 | Form styling |
| @tailwindcss/vite | ^4.3.0 | Vite plugin |
| laravel-vite-plugin | ^1.0 | Laravel + Vite bridge |
| vite | ^5.0 | Build tool |
| axios | ^1.6.4 | HTTP client |

---

## 12. Catatan Implementasi

- **Single-tenant:** 1 kelompok tani, 1 admin
- **updateOrCreate per blok:** Setiap analisis menimpa hasil sebelumnya (tidak ada histori)
- **Kriteria terintegrasi:** tahun_tanam, jenis_tanah, topografi sudah merged ke tabel blok_lahans (bukan tabel terpisah lagi)
- **SPK dihapus:** Modul Forward Chaining (SpkController, SpkService, KriteriaLahanController) sudah tidak ada di kode — logika dosisnya diintegrasikan ke RbsService
- **Status label mapping:** Darurat→Defisiensi Berat, Segera→Perlu Pupuk, Normal→Sehat, Tunda→Tunda Pupuk
- **Karung:** 1 karung = 50 kg, dihitung dengan ceiling
- **Laporan total:** Hanya menghitung dari status Normal + Segera (tidak Darurat/Tunda karena ada catatan khusus)
