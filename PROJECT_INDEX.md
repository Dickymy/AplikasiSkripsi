# PROJECT INDEX — SPK Sawit

**Nama:** SPK Sawit — Sistem Pendukung Keputusan Pemupukan Kelapa Sawit  
**Judul Skripsi:** "Rancang Bangun WebGIS Sistem Pemupukan Kelapa Sawit Menggunakan Metode Rule-Based System"  
**Stack:** Laravel 11 · PHP 8.2 · Tailwind CSS v4 · Vite 5 · Leaflet.js 1.9.4 · SQLite  
**Metode:** Rule-Based System (Forward Chaining) — 25 rules diagnostik  
**Auth:** Custom guard `admin` (single-tenant, single-role)

---

## DAFTAR ISI

1. [Struktur Folder](#1-struktur-folder)
2. [Konfigurasi & Environment](#2-konfigurasi--environment)
3. [Routing](#3-routing)
4. [Models & Relasi Database](#4-models--relasi-database)
5. [Controllers](#5-controllers)
6. [Services (Business Logic)](#6-services-business-logic)
7. [Middleware](#7-middleware)
8. [Database Migrations](#8-database-migrations)
9. [Database Seeders](#9-database-seeders)
10. [Views (Blade Templates)](#10-views-blade-templates)
11. [Frontend Assets & Build](#11-frontend-assets--build)
12. [Fitur Utama](#12-fitur-utama)
13. [Cara Menjalankan](#13-cara-menjalankan)

---

## 1. Struktur Folder

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AnggotaController.php       — CRUD anggota kelompok tani
│   │   ├── AuthController.php          — Login/Logout (guard admin)
│   │   ├── BlokLahanController.php     — CRUD blok lahan + peta polygon
│   │   ├── Controller.php              — Base controller (abstract)
│   │   ├── DashboardController.php     — WebGIS peta interaktif
│   │   ├── KondisiLahanController.php  — CRUD observasi kondisi lahan
│   │   ├── LaporanController.php       — Rekap laporan + export PDF
│   │   ├── RbsController.php           — Analisis RBS + API popup
│   │   └── RuleBaseController.php      — CRUD rule Forward Chaining
│   └── Middleware/
│       └── AdminAuthenticated.php      — Proteksi route admin
├── Models/
│   ├── Admin.php                       — Authenticatable admin
│   ├── Anggota.php                     — Anggota kelompok tani
│   ├── BlokLahan.php                   — Blok lahan + accessor umur
│   ├── KondisiLahan.php                — Observasi lapangan
│   ├── RekomendasiRbs.php              — Hasil analisis RBS
│   ├── RuleBase.php                    — Rule Forward Chaining (legacy)
│   ├── RuleBaseLanjutan.php            — 25 rules RBS aktif
│   └── User.php                        — Laravel default (tidak dipakai)
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── RbsService.php                  — Inti logic Rule-Based System

bootstrap/
├── app.php                             — Middleware alias + trustProxies
└── providers.php

config/
├── app.php, auth.php, database.php, session.php, ...

database/
├── migrations/ (21 files)
└── seeders/
    ├── DatabaseSeeder.php
    ├── AdminSeeder.php
    ├── RuleBaseSeeder.php              — 150 rules Forward Chaining
    └── RuleBaseLanjutanSeeder.php      — 25 rules RBS

resources/views/
├── layouts/app.blade.php              — Layout utama (sidebar + topbar)
├── auth/login.blade.php
├── dashboard/index.blade.php          — WebGIS Leaflet
├── anggota/{index,create,edit}
├── blok_lahan/{index,create,edit,show}
├── kondisi_lahan/{index,create,edit}
├── rule_base/{index,create,edit}
├── rbs/{index,detail,partials/_hasil_rbs}
├── laporan/{index,show,pdf}
└── components/{searchable-select,filter-searchable}

routes/
├── web.php                            — Semua route aplikasi
└── console.php
```

---

## 2. Konfigurasi & Environment

### .env.example
- `DB_CONNECTION=sqlite` (file: `database/database.sqlite`)
- `SESSION_DRIVER=database`
- `APP_TIMEZONE=UTC`

### config/auth.php
- Default guard: `admin`
- Guard `admin`: session driver, provider `admins` (model `App\Models\Admin`)

### config/database.php
- Default: `sqlite` → `database/database.sqlite`
- MySQL config tersedia (dikomentari di .env)

### bootstrap/app.php
- Middleware alias: `auth.admin` → `AdminAuthenticated`
- `trustProxies(at: '*')` — untuk ngrok
- Route: web.php + console.php + health `/up`

### composer.json (dependencies)
- `laravel/framework: ^11.0`
- `barryvdh/laravel-dompdf: ^3.1` — export PDF

### package.json (frontend)
- `tailwindcss: ^4.3.0` + `@tailwindcss/vite`
- `vite: ^5.0` + `laravel-vite-plugin`

### vite.config.js
```js
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
// input: resources/css/app.css + resources/js/app.js
```

---

## 3. Routing

**File:** `routes/web.php`

| Method | URI | Controller | Fungsi |
|--------|-----|-----------|--------|
| GET | `/` | — | Redirect → dashboard |
| GET | `/login` | AuthController@showLoginForm | Login page |
| POST | `/login` | AuthController@login | Proses login |
| POST | `/logout` | AuthController@logout | Logout |
| GET | `/dashboard` | DashboardController@index | WebGIS peta |
| RESOURCE | `/anggota` | AnggotaController (no show) | CRUD anggota |
| RESOURCE | `/blok-lahan` | BlokLahanController (full) | CRUD blok lahan |
| RESOURCE | `/kondisi-lahan` | KondisiLahanController (no show) | CRUD kondisi |
| RESOURCE | `/rule-base` | RuleBaseController (no show) | CRUD rule FC |
| GET | `/rbs` | RbsController@index | Daftar blok + status |
| POST | `/rbs/analisis/{blokLahan}` | RbsController@analisis | Analisis 1 blok |
| POST | `/rbs/analisis-semua` | RbsController@analisisSemua | Batch analisis |
| GET | `/rbs/detail/{blokLahan}` | RbsController@detail | Detail hasil |
| GET | `/laporan` | LaporanController@index | Rekap laporan |
| GET | `/laporan/{rekomendasiRbs}/pdf` | LaporanController@exportPdf | Download PDF |
| GET | `/laporan/{rekomendasiRbs}` | LaporanController@show | Detail laporan |
| GET | `/api/rbs-popup/{blokLahan}` | RbsController@apiPopup | JSON popup peta |

Semua route (kecuali login) dilindungi middleware `AdminAuthenticated`.

---

## 4. Models & Relasi Database

### Admin (`admins`)
- Fillable: username, password, nama_lengkap
- Auth: `Authenticatable` + `Notifiable`
- Cast: password → hashed

### Anggota (`anggotas`)
- Fillable: nama (unique), no_hp, alamat
- Relasi: `hasMany BlokLahan`
- Accessor: `jumlah_blok`

### BlokLahan (`blok_lahans`)
- Fillable: anggota_id, nama_blok, luas_ha, sph, koordinat_geojson, tahun_tanam, jenis_tanah, topografi
- Relasi:
  - `belongsTo Anggota`
  - `hasMany KondisiLahan`
  - `hasMany RekomendasiRbs`
  - `hasOne kondisiTerbaru` (latestOfMany tanggal_observasi)
  - `hasOne rekomendasiRbsTerbaru` (latestOfMany tanggal_analisis)
- Accessor: `nama_pemilik`, `umur_tanaman`, `kategori_umur`

### KondisiLahan (`kondisi_lahans`)
- Fillable: 14 fields (pH, kelembaban, musim, warna_daun, defisiensi JSON, dll)
- Relasi: `belongsTo BlokLahan`, `hasMany RekomendasiRbs`
- Accessor: `label_ph`
- Cast: gejala_defisiensi → array, tanggal_observasi → date

### RuleBaseLanjutan (`rule_bases_lanjutan`)
- 25 rules aktif (dari seeder)
- Fillable: 22 fields (kondisi IF + output THEN)
- Cast: aktif → boolean, kondisi_ph_min/max → decimal
- Scope: `aktif()` — hanya rule aktif=true

### RekomendasiRbs (`rekomendasi_rbs`)
- Fillable: 15 fields (blok_lahan_id, kondisi_lahan_id, admin_id, tanggal_analisis, rules_terpicu JSON, masalah JSON, rekomendasi_pupuk JSON, dosis, total, catatan_dosis, dll)
- Cast: rules_terpicu/masalah/rekomendasi_pupuk → array, dosis → double
- Accessor: `warna_badge`, `label_status`, `karung_urea`, `karung_kcl`
- Static: `labelStatus()` — konversi status DB ke label tampilan

### RuleBase (`rule_bases`) — Legacy
- Format key: "Kategori_Umur|Jenis_Tanah|Topografi"
- 150 kombinasi (seed)
- Masih dipakai untuk CRUD rule di UI

---

## 5. Controllers

### AuthController
- `showLoginForm()` — redirect jika sudah login
- `login()` — Auth::guard('admin')->attempt + session regenerate
- `logout()` — invalidate session

### DashboardController
- `index()` — load semua BlokLahan + build `$mapData` JSON untuk Leaflet

### AnggotaController
- CRUD lengkap (except show)
- Validation: nama unique, proteksi hapus jika punya blok lahan
- Note: Laravel route model binding → parameter `$anggotum` (plural Laravel convention)

### BlokLahanController
- `index()` — filter anggota + filter status RBS, grouped by anggota
- `create()` — pass anggotas + existingBloks (polygon kuning di peta)
- `store()` — validasi 8 field + JSON validation
- `show()` — detail blok + partial RBS
- `edit()` / `update()` / `destroy()`

### KondisiLahanController
- `index()` — grouped by anggota, filter anggota
- `create()` — wizard 5 seksi, cascading filter pemilik→blok (JS)
- `store()` — validasi 14+ fields, checkbox boolean handling
- `edit()` / `update()` / `destroy()`

### RuleBaseController
- CRUD untuk tabel `rule_bases` (Forward Chaining legacy)
- Simple parameter_kondisi + takaran_urea + takaran_kcl + status

### RbsController
- `index()` — daftar blok + stats + filter anggota/blok
- `analisis(BlokLahan)` — delegate ke RbsService
- `analisisSemua()` — batch semua blok yang punya kondisi
- `detail(BlokLahan)` — load kondisi + rekomendasi + rules terpicu
- `apiPopup(BlokLahan)` — JSON untuk popup WebGIS

### LaporanController
- `index()` — rekap per anggota, filter status/anggota/blok, grand total
- `show(RekomendasiRbs)` — detail satu rekomendasi
- `exportPdf(RekomendasiRbs)` — DomPDF download

---

## 6. Services (Business Logic)

### RbsService (`app/Services/RbsService.php`)

**Method utama:**
- `analisis(BlokLahan)` — entry point analisis satu blok
- `analisisSemua()` — batch semua blok

**Alur Forward Chaining:**
1. Ambil kondisi lahan terbaru (`kondisiTerbaru`)
2. Cek apakah data kondisi cukup (`kondisiCukup()`)
3. Ambil kategori umur dari blok
4. Ambil semua rule aktif, urut prioritas
5. Loop evaluasi setiap rule (`evaluasiRule()`) — AND logic
6. Jika tidak ada rule terpicu → status Normal
7. Susun output (`susunHasil()`)

**Evaluasi Rule (AND Logic):**
- Setiap field non-NULL di rule harus cocok dengan data input
- pH: range check (min ≤ pH ≤ max)
- Defisiensi: array contains check
- Jika data input NULL untuk field yang dicek → rule gagal
- Safety: minimal 1 kondisi di rule yang benar-benar cocok

**Kalkulasi Dosis (`hitungDosisStandar()`):**
- Base dosis per kategori umur (5 kategori)
- Multiplier jenis tanah (10 jenis)
- Multiplier topografi (3 jenis)
- Formula: `base × multiplier_tanah × multiplier_topo`, bulatkan ke 0.25
- Total: `dosis × SPH × luas_ha`

**Output Rekomendasi:**
- Status dominan: Darurat(4) > Segera(3) > Normal(2) > Tunda(1)
- Masalah unik dari rules terpicu
- Pupuk deduplicate by jenis_pupuk_utama
- Saran: gabungan 3 rule prioritas tertinggi
- Catatan dosis kontekstual (berdasarkan status + masalah)
- Simpan: `updateOrCreate` per blok_lahan_id

---

## 7. Middleware

### AdminAuthenticated
- Cek `Auth::guard('admin')->check()`
- Gagal → redirect `/login` + flash error

---

## 8. Database Migrations

| # | File | Fungsi |
|---|------|--------|
| 1 | `create_admins_table` | id, username (unique), password, nama_lengkap |
| 2 | `create_blok_lahans_table` | id, nama_blok, luas_ha, sph, koordinat_geojson |
| 3 | `create_kriteria_lahans_table` | id, blok_lahan_id FK, tahun_tanam, jenis_tanah, topografi |
| 4 | `create_rule_bases_table` | id, parameter_kondisi, takaran_urea/kcl, status_pemupukan |
| 5 | `create_rekomendasi_spks_table` | id, blok_lahan_id, admin_id, dosis, total, status |
| 6 | `add_nama_pemilik_to_blok_lahans` | +nama_pemilik |
| 7 | `add_panen_fields_to_blok_lahans` | +total_tonase_panen, +yield_per_hektar |
| 8 | `modify_jenis_tanah_column` | ENUM→VARCHAR(255) |
| 9 | `create_kondisi_lahans_table` | 14 fields observasi |
| 10 | `create_rule_bases_lanjutan_table` | 25+ fields rules RBS |
| 11 | `create_rekomendasi_rbs_table` | JSON fields + status + jumlah_rule |
| 12 | `add_missing_columns_to_rule_bases_lanjutan` | +kondisi_pelepah, +kondisi_tandan, +ada_serangan_hama |
| 13 | `add_dosis_columns_to_rekomendasi_rbs` | +dosis_urea/kcl, +total_urea/kcl |
| 14 | `drop_panen_fields_from_blok_lahans` | -total_tonase_panen, -yield_per_hektar |
| 15 | `create_anggotas_table` | id, nama (unique), no_hp, alamat |
| 16 | `add_anggota_id_to_blok_lahans` | +anggota_id FK, migrate nama_pemilik→anggota, -nama_pemilik |
| 17 | `merge_kriteria_into_blok_lahans` | +tahun_tanam/jenis_tanah/topografi ke blok_lahans, migrate data |
| 18 | `add_catatan_dosis_to_rekomendasi_rbs` | +catatan_dosis text |

---

## 9. Database Seeders

### AdminSeeder
- 1 admin: username=`admin`, password=`admin123`, nama=`Administrator`

### RuleBaseSeeder
- 150 rules Forward Chaining (5 umur × 10 tanah × 3 topografi)
- Formula: base_dosis × multiplier_tanah × multiplier_topo, bulatkan 0.25
- Status: Segera/Normal/Tunda berdasarkan total multiplier

### RuleBaseLanjutanSeeder
- 25 rules RBS mencakup:
  - Defisiensi N (2 rules), K (2), Mg (1), B (1), P (1), Fe (1), Zn (1)
  - pH masam (2 rules: sangat masam + masam)
  - Drainase buruk (1), Kemarau (2)
  - Tanaman muda/TBM (1), Tua renta (1)
  - Pelepah abnormal (1), Tandan abnormal (2)
  - Serangan hama (2), Bercak nekrotik (1)
  - Kondisi normal (1), Musim hujan optimal (1)
  - Tanaman remaja (1), Menghasilkan tua (1)

---

## 10. Views (Blade Templates)

### Layout (`layouts/app.blade.php`)
- Sidebar fixed (z-9000): navigasi 7 menu, collapsible desktop/mobile
- Topbar sticky: hamburger + page title + tanggal
- Flash messages: success/error/warning
- Global confirm modal (z-9999): `confirmDelete()`, `confirmLogout()`, `showConfirm()`
- Back to top button
- Leaflet CSS + JS loaded globally
- Print styles

### Dashboard (`dashboard/index.blade.php`)
- 5 stat cards: Total Blok, Total Luas, Dianalisis, Kritis, Perlu Pupuk
- Luas per status grid
- Peta Leaflet: polygon warna per status RBS, permanent label, popup detail
- Filter: pemilik + blok + status buttons (5 toggle)
- Layer switching: OSM + Satelit ESRI
- Fullscreen peta (toggle Perluas/Kecilkan)

### Blok Lahan Create/Edit
- Searchable dropdown pemilik
- Fields: nama blok, SPH, tahun tanam, jenis tanah (10 opsi), topografi (3 opsi)
- Peta Leaflet Draw: tab Gambar/GeoJSON Manual
- Existing polygons kuning putus-putus
- Auto-calc luas geodesic dari polygon
- Fullscreen peta
- Preview umur tanaman real-time

### Kondisi Lahan Create
- 5 seksi wizard: Identifikasi, Tanah, Iklim, Gejala Visual, Catatan
- Cascading filter: pilih pemilik → muncul blok (JS)
- Smart curah hujan dependent on musim
- Gejala defisiensi multi-select (N,P,K,Mg,B,Fe,Zn) dengan penjelasan
- Toggle hama/gulma dengan visual feedback

### RBS Detail
- 2-column layout: Info Blok + Kondisi (kiri) | Hasil RBS (kanan)
- Partial `_hasil_rbs.blade.php`: status badge, masalah, pupuk, saran, catatan dosis
- Detail rules terpicu: numbered list + status badge per rule
- Action: re-run analisis, link detail blok

### Laporan Index
- 4 stat cards: Total Anggota, Urea, KCl, Blok Layak Pupuk
- Catatan: hanya hitung dari status Sehat + Perlu Pupuk
- Filter: pemilik + blok + status
- Grouped by anggota: tabel desktop + card mobile
- Subtotal per anggota, Grand Total di bawah
- Tombol cetak (window.print) + PDF per item

### Laporan PDF (`laporan/pdf.blade.php`)
- Full HTML/CSS (DomPDF compatible)
- Status banner, info lahan, logistik pupuk, masalah, rekomendasi, rules, footer

### Components
- `searchable-select.blade.php` — dropdown dengan search input (dipakai di forms)
- `filter-searchable.blade.php` — versi filter (submit form on select)

---

## 11. Frontend Assets & Build

- **CSS:** Tailwind CSS v4 via `@tailwindcss/vite` plugin
- **JS:** Vanilla JavaScript (no framework)
- **Maps:** Leaflet.js 1.9.4 CDN + Leaflet Draw 1.0.4 CDN
- **Build:** `npm run build` (production) / `npm run dev` (dev server)
- **Fonts:** Inter (Google Fonts CDN)

---

## 12. Fitur Utama

| # | Fitur | Status |
|---|-------|--------|
| 1 | Login/Logout admin (custom guard) | ✅ |
| 2 | Dashboard WebGIS (Leaflet + polygon + popup + filter status) | ✅ |
| 3 | CRUD Anggota Kelompok Tani | ✅ |
| 4 | CRUD Blok Lahan + Draw Polygon + Auto Luas | ✅ |
| 5 | CRUD Kondisi Lahan (wizard 5 seksi) | ✅ |
| 6 | CRUD Rule Base (Forward Chaining legacy) | ✅ |
| 7 | Analisis RBS (1 blok / semua) | ✅ |
| 8 | Dosis Urea+KCl otomatis (formula PPKS) | ✅ |
| 9 | Catatan dosis kontekstual per status | ✅ |
| 10 | Laporan & Rekap (filter + grand total) | ✅ |
| 11 | Export PDF (DomPDF) | ✅ |
| 12 | Responsive (mobile + desktop) | ✅ |
| 13 | Fullscreen peta (dashboard + create/edit blok) | ✅ |
| 14 | Searchable dropdown components | ✅ |
| 15 | Cascading filter pemilik → blok | ✅ |

---

## 13. Cara Menjalankan

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database (SQLite default)
php artisan migrate
php artisan db:seed

# Build assets
npm run build        # production
# atau
npm run dev          # development (live reload)

# Jalankan server
php artisan serve    # http://localhost:8000
```

**Login:** username=`admin`, password=`admin123`

---

*Generated: 12 Juni 2026*
