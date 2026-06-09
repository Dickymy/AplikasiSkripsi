# PROJECT CONTEXT FOR CLAUDE

## 1. Ringkasan Project

**Nama:** SPK Sawit — Sistem Pendukung Keputusan Pemupukan Kelapa Sawit  
**Judul Skripsi:** "Rancang Bangun WebGIS Sistem Pemupukan Kelapa Sawit Menggunakan Metode Rule-Based System"  
**Jenis:** Aplikasi Web berbasis Laravel (single-tenant, single-role admin)

**Stack Teknologi:**
- Backend: Laravel 11, PHP 8.2
- Frontend: Blade Templates, Tailwind CSS v4, Vite 5
- Database: MySQL (DB: `sawit_spk`)
- Maps: Leaflet.js 1.9.4, Leaflet Draw 1.0.4, OpenStreetMap + ESRI Satellite
- Metode: Rule-Based System (Forward Chaining) dengan 25 rules diagnostik
- Auth: Custom guard `admin`

**Fitur Utama:**
1. Dashboard WebGIS — peta interaktif blok lahan dengan status pemupukan
2. CRUD Anggota Kelompok Tani — data pemilik lahan
3. CRUD Blok Lahan — termasuk draw polygon di peta + kriteria agronomis (tahun tanam, jenis tanah, topografi)
4. CRUD Kondisi Lahan — observasi lapangan (pH, warna daun, kelembaban, musim, gejala defisiensi, dll)
5. Rule Base — 25 aturan IF-THEN untuk diagnosis kondisi tanaman
6. Analisis Pemupukan (RBS) — evaluasi kondisi lahan → trigger rules → rekomendasi pupuk + dosis
7. Laporan & Rekap — ringkasan kebutuhan pupuk per anggota/blok dengan fitur cetak

---

## 2. Struktur Folder Project

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── AnggotaController.php
│   │   ├── BlokLahanController.php
│   │   ├── KondisiLahanController.php
│   │   ├── RuleBaseController.php
│   │   ├── RbsController.php
│   │   ├── DashboardController.php
│   │   └── LaporanController.php
│   └── Middleware/
│       └── AdminAuthenticated.php
├── Models/
│   ├── Admin.php
│   ├── Anggota.php
│   ├── BlokLahan.php
│   ├── KondisiLahan.php
│   ├── RuleBase.php              (legacy - tidak aktif dipakai)
│   ├── RuleBaseLanjutan.php
│   ├── RekomendasiRbs.php
│   ├── RekomendasiSpk.php        (legacy - tidak aktif dipakai)
│   ├── KriteriaLahan.php         (legacy - data sudah dimigrasikan)
│   └── User.php                  (Laravel default - tidak dipakai)
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── RbsService.php

database/
├── migrations/
│   ├── 2026_05_20_205515_create_admins_table.php
│   ├── 2026_05_20_205515_create_blok_lahans_table.php
│   ├── 2026_05_20_205516_create_kriteria_lahans_table.php
│   ├── 2026_05_20_205516_create_rule_bases_table.php
│   ├── 2026_05_20_205517_create_rekomendasi_spks_table.php
│   ├── 2026_05_20_231656_add_nama_pemilik_to_blok_lahans_table.php
│   ├── 2026_06_04_000000_add_panen_fields_to_blok_lahans_table.php
│   ├── 2026_06_04_000001_modify_jenis_tanah_column_in_kriteria_lahans_table.php
│   ├── 2026_06_04_100000_create_kondisi_lahans_table.php
│   ├── 2026_06_04_100001_create_rule_bases_lanjutan_table.php
│   ├── 2026_06_04_100002_create_rekomendasi_rbs_table.php
│   ├── 2026_06_04_100003_add_missing_columns_to_rule_bases_lanjutan_table.php
│   ├── 2026_06_04_200000_add_dosis_columns_to_rekomendasi_rbs_table.php
│   ├── 2026_06_07_000000_drop_panen_fields_from_blok_lahans_table.php
│   ├── 2026_06_07_100000_create_anggotas_table.php
│   ├── 2026_06_07_100001_add_anggota_id_to_blok_lahans_table.php
│   ├── 2026_06_07_200000_merge_kriteria_into_blok_lahans_table.php
│   └── 2026_06_07_200001_add_catatan_dosis_to_rekomendasi_rbs_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── AdminSeeder.php
    ├── RuleBaseSeeder.php
    └── RuleBaseLanjutanSeeder.php

resources/views/
├── layouts/
│   └── app.blade.php
├── auth/
│   └── login.blade.php
├── dashboard/
│   └── index.blade.php
├── anggota/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── blok_lahan/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── kondisi_lahan/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── rule_base/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── rbs/
│   ├── index.blade.php
│   ├── detail.blade.php
│   └── partials/_hasil_rbs.blade.php
├── laporan/
│   ├── index.blade.php
│   └── show.blade.php
└── components/
    └── searchable-select.blade.php

routes/
└── web.php

bootstrap/
└── app.php
```

---

## 3. Daftar File Penting

| File | Fungsi | Catatan |
|------|--------|---------|
| `routes/web.php` | Semua route aplikasi | 41 routes aktif |
| `app/Services/RbsService.php` | Logika inti Rule-Based System + kalkulasi dosis | File terpenting untuk metode skripsi |
| `app/Models/BlokLahan.php` | Model utama blok lahan + accessor umur/kategori | Termasuk kriteria agronomis |
| `app/Models/RekomendasiRbs.php` | Model hasil analisis | JSON fields: rules_terpicu, masalah, rekomendasi_pupuk |
| `app/Models/RuleBaseLanjutan.php` | Model 25 rules RBS | Scope `aktif()` |
| `app/Models/KondisiLahan.php` | Model observasi lapangan | JSON field: gejala_defisiensi |
| `app/Models/Anggota.php` | Model anggota kelompok tani | Relasi 1:N ke BlokLahan |
| `app/Http/Controllers/RbsController.php` | Controller analisis + filter | Filter anggota + blok |
| `app/Http/Controllers/BlokLahanController.php` | CRUD blok lahan + peta | Pass existing polygons ke view |
| `app/Http/Controllers/DashboardController.php` | WebGIS dashboard | Build mapData JSON untuk Leaflet |
| `app/Http/Controllers/LaporanController.php` | Rekap laporan + filter | Berbasis RekomendasiRbs |
| `resources/views/layouts/app.blade.php` | Layout utama | Sidebar, topbar, modal, global CSS/JS |
| `resources/views/dashboard/index.blade.php` | Peta WebGIS | Leaflet + polygon + popup + stats |
| `resources/views/blok_lahan/create.blade.php` | Form tambah blok + peta draw | Leaflet Draw + auto calc luas |
| `resources/views/rbs/partials/_hasil_rbs.blade.php` | Komponen hasil RBS (reusable) | Dipakai di detail + show blok |
| `resources/views/components/searchable-select.blade.php` | Dropdown searchable | Dipakai di form blok/kondisi |
| `database/seeders/RuleBaseLanjutanSeeder.php` | 25 rules RBS | Grup: defisiensi N/K/Mg/B/P/Fe/Zn, pH, drainase, musim, umur |
| `bootstrap/app.php` | Config middleware + trusted proxies | `trustProxies(at: '*')` untuk ngrok |

---

## 4. Route Aplikasi

| URL | Method | Controller | Fungsi |
|-----|--------|-----------|--------|
| `/` | GET | — | Redirect ke dashboard |
| `/login` | GET | AuthController@showLoginForm | Halaman login |
| `/login` | POST | AuthController@login | Proses login |
| `/logout` | POST | AuthController@logout | Proses logout |
| `/dashboard` | GET | DashboardController@index | Peta WebGIS + statistik |
| `/anggota` | RESOURCE | AnggotaController | CRUD anggota (tanpa show) |
| `/blok-lahan` | RESOURCE | BlokLahanController | CRUD blok lahan (full) |
| `/kondisi-lahan` | RESOURCE | KondisiLahanController | CRUD kondisi (tanpa show) |
| `/rule-base` | RESOURCE | RuleBaseController | CRUD rule base (tanpa show) |
| `/rbs` | GET | RbsController@index | Daftar blok + status analisis |
| `/rbs/analisis/{blokLahan}` | POST | RbsController@analisis | Analisis 1 blok |
| `/rbs/analisis-semua` | POST | RbsController@analisisSemua | Analisis batch |
| `/rbs/detail/{blokLahan}` | GET | RbsController@detail | Detail hasil analisis |
| `/laporan` | GET | LaporanController@index | Rekap laporan + filter |
| `/laporan/{rekomendasiRbs}` | GET | LaporanController@show | Detail satu rekomendasi |
| `/api/rbs-popup/{blokLahan}` | GET | RbsController@apiPopup | JSON untuk popup peta |

Semua route (kecuali login/logout) dilindungi middleware `AdminAuthenticated`.

---

## 5. Model dan Relasi Database

### Admin
- Tabel: `admins`
- Fillable: username, password, nama_lengkap
- Auth: Authenticatable (custom guard)

### Anggota
- Tabel: `anggotas`
- Fillable: nama (unique), no_hp, alamat
- Relasi: `hasMany BlokLahan`
- Accessor: `jumlah_blok`

### BlokLahan
- Tabel: `blok_lahans`
- Fillable: anggota_id, nama_blok, luas_ha, sph, koordinat_geojson, tahun_tanam, jenis_tanah, topografi
- Relasi: `belongsTo Anggota`, `hasMany KondisiLahan`, `hasMany RekomendasiRbs`, `hasOne kondisiTerbaru (latestOfMany)`, `hasOne rekomendasiRbsTerbaru (latestOfMany)`
- Accessor: `nama_pemilik` (dari anggota), `umur_tanaman`, `kategori_umur`

### KondisiLahan
- Tabel: `kondisi_lahans`
- Fillable: blok_lahan_id, tanggal_observasi, ph_tanah, kelembaban_tanah, curah_hujan_kategori, musim_saat_ini, warna_daun, kondisi_pelepah, gejala_defisiensi (JSON array), kondisi_tandan, kondisi_drainase, ada_gulma_dominan, ada_serangan_hama, catatan_observasi
- Relasi: `belongsTo BlokLahan`, `hasMany RekomendasiRbs`
- Accessor: `label_ph`

### RuleBaseLanjutan
- Tabel: `rule_bases_lanjutan`
- 25 rules aktif (seed)
- Fillable: kondisi_warna_daun, kondisi_ph_min, kondisi_ph_max, kondisi_kelembaban, kondisi_musim, kondisi_drainase, kondisi_defisiensi, kondisi_kategori_umur, kondisi_pelepah, kondisi_tandan, ada_serangan_hama, indikasi_masalah, jenis_pupuk_utama, jenis_pupuk_pendukung, dosis_anjuran, metode_aplikasi, waktu_aplikasi, saran_tindakan, status_kebutuhan (enum: Darurat/Segera/Normal/Tunda), prioritas (1-10), aktif, keterangan_rule
- Scope: `aktif()` — hanya rule yang aktif=true

### RekomendasiRbs
- Tabel: `rekomendasi_rbs`
- Fillable: blok_lahan_id, kondisi_lahan_id, admin_id, tanggal_analisis, rules_terpicu (JSON), masalah_teridentifikasi (JSON), rekomendasi_pupuk (JSON), saran_tindakan_utama, status_kebutuhan_dominan, jumlah_rule_terpicu, dosis_urea, dosis_kcl, total_urea, total_kcl, catatan_dosis
- Relasi: `belongsTo BlokLahan`, `belongsTo KondisiLahan`, `belongsTo Admin`
- Accessor: `warna_badge`, `karung_urea`, `karung_kcl`

---

## 6. Migration dan Struktur Tabel

### Tabel `admins`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| username | varchar(50) UNIQUE | |
| password | varchar | Bcrypt hashed |
| nama_lengkap | varchar(100) | |
| timestamps | | |

### Tabel `anggotas`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| nama | varchar(100) UNIQUE | Tidak boleh duplikat |
| no_hp | varchar(20) nullable | |
| alamat | text nullable | |
| timestamps | | |

### Tabel `blok_lahans`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| anggota_id | FK → anggotas | NULL on delete |
| nama_blok | varchar(100) | |
| luas_ha | double | Auto-calc dari polygon |
| sph | integer | Standar Pohon/Ha |
| koordinat_geojson | longtext | GeoJSON Polygon |
| tahun_tanam | integer nullable | Digabung dari kriteria_lahans |
| jenis_tanah | varchar(255) nullable | 10 pilihan |
| topografi | varchar(50) nullable | 3 pilihan |
| timestamps | | |

### Tabel `kondisi_lahans`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE DELETE |
| tanggal_observasi | date | |
| ph_tanah | decimal(4,2) nullable | 3.0–8.0 |
| kelembaban_tanah | enum nullable | 5 pilihan |
| curah_hujan_kategori | enum nullable | 5 pilihan |
| musim_saat_ini | enum nullable | Hujan/Kemarau/Peralihan |
| warna_daun | enum nullable | 8 pilihan |
| kondisi_pelepah | enum nullable | 4 pilihan |
| gejala_defisiensi | JSON nullable | Array: ['N','P','K','Mg','B','Fe','Zn'] |
| kondisi_tandan | enum nullable | 5 pilihan |
| kondisi_drainase | enum nullable | 3 pilihan |
| ada_gulma_dominan | boolean | Default false |
| ada_serangan_hama | boolean | Default false |
| catatan_observasi | text nullable | |
| timestamps | | |

### Tabel `rule_bases_lanjutan`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| kondisi_* | varchar nullable | 8 field kondisi (IF) |
| kondisi_pelepah | varchar(100) nullable | |
| kondisi_tandan | varchar(100) nullable | |
| ada_serangan_hama | boolean nullable | NULL=skip, true=harus ada |
| indikasi_masalah | varchar(255) | Label masalah (THEN) |
| jenis_pupuk_utama | varchar(100) | |
| jenis_pupuk_pendukung | varchar(100) nullable | |
| dosis_anjuran | varchar(150) | Teks deskriptif |
| metode_aplikasi | varchar(255) nullable | |
| waktu_aplikasi | varchar(150) nullable | |
| saran_tindakan | text | |
| status_kebutuhan | enum | Darurat/Segera/Normal/Tunda |
| prioritas | tinyint | 1(tertinggi)–10(terendah) |
| aktif | boolean | Default true |
| timestamps | | |

### Tabel `rekomendasi_rbs`
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
| saran_tindakan_utama | text | Gabungan 3 rule prioritas tertinggi |
| status_kebutuhan_dominan | enum | Darurat/Segera/Normal/Tunda |
| jumlah_rule_terpicu | tinyint | |
| dosis_urea | double nullable | kg/pokok (hitung dari umur×tanah×topografi) |
| dosis_kcl | double nullable | kg/pokok |
| total_urea | double nullable | dosis × SPH × luas_ha |
| total_kcl | double nullable | |
| catatan_dosis | text nullable | Konteks kapan dosis boleh diaplikasikan |
| timestamps | | |

---

## 7. Controller dan Alur Fitur

### RbsController — Inti Analisis
- `index(Request)` — List blok + filter anggota + filter blok
- `analisis(BlokLahan)` — Panggil RbsService → redirect ke detail
- `analisisSemua()` — Loop semua blok yang punya kondisi → batch
- `detail(BlokLahan)` — Load kondisi + rekomendasi + rules terpicu
- `apiPopup(BlokLahan)` — JSON response untuk popup WebGIS

### BlokLahanController
- `create()` → pass anggotas + existingBloks (untuk peta)
- `store()` → validasi 8 field + JSON check → create
- `edit()` → pass existingBloks (kecuali yang diedit)
- `show()` → load relasi + tampilkan detail + partial RBS

### DashboardController
- `index()` → load semua blok + build mapData array → pass ke view Leaflet

### LaporanController
- `index(Request)` → filter status + anggota + blok → summary total Urea/KCl/karung
- `show(RekomendasiRbs)` → detail satu rekomendasi

---

## 8. View Blade dan Tampilan

### Layout (`layouts/app.blade.php`)
- Sidebar fixed (z-9000): collapsible desktop, slide mobile
- Topbar sticky: hamburger + page title + tanggal
- Flash messages: success/error/warning
- Global confirm modal (z-9999): `confirmDelete()`, `confirmLogout()`, `showConfirm()`
- Global CSS: Leaflet z-index fix, hide-mobile class, overflow-x hidden

### Dashboard (`dashboard/index.blade.php`)
- Stats cards (5): Total Blok, Total Luas, Sudah Dianalisis, Darurat, Segera
- Peta Leaflet: polygon warna per status, permanent label nama blok, popup detail RBS
- Filter: dropdown pemilik + dropdown blok (muncul setelah pilih pemilik)
- Fullscreen peta: tombol expand, ESC untuk kecilkan
- JS: `updateStats()`, `renderMapLayers()`, `buildPopupContent()`, `toggleFullscreen()`

### Blok Lahan Create/Edit
- Searchable dropdown pemilik (component)
- Field: nama blok, SPH, tahun tanam, jenis tanah, topografi
- Peta Leaflet Draw: tab Gambar/GeoJSON Manual
- Existing polygons ditampilkan (kuning putus-putus)
- Auto-calc luas dari polygon (geodesic formula)
- Fullscreen peta: button "Perluas Peta" / "Kecilkan"
- Preview umur tanaman real-time

### Kondisi Lahan Create
- 5 seksi wizard: Identifikasi, Kondisi Tanah, Iklim, Gejala Visual, Catatan
- Searchable dropdown blok lahan
- Smart curah hujan dependent on musim
- Gejala defisiensi multi-select dengan penjelasan per unsur
- Toggle hama/gulma dengan visual feedback

### Komponen Searchable Select
- Reusable partial: dropdown dengan search input
- Dipakai di: form blok lahan (pilih anggota), form kondisi (pilih blok)

---

## 9. Analisis Fitur WebGIS dan GeoJSON

| Aspek | Status | Detail |
|-------|--------|--------|
| Leaflet digunakan | ✅ | v1.9.4 via CDN unpkg |
| File memuat Leaflet | `layouts/app.blade.php`, `dashboard/index.blade.php`, `blok_lahan/create.blade.php`, `blok_lahan/edit.blade.php` |
| Fitur gambar polygon | ✅ | Leaflet Draw 1.0.4 (polygon + rectangle) |
| Input textarea GeoJSON | ✅ | Tab "GeoJSON Manual" di create/edit blok |
| Upload file GeoJSON | ❌ | Tidak ada |
| GeoJSON disimpan ke DB | ✅ | Kolom `koordinat_geojson` di `blok_lahans` |
| Polygon ditampilkan di peta | ✅ | Dashboard + form create/edit (existing polygons kuning) |
| Label permanent polygon | ✅ | `bindTooltip(nama_blok, {permanent: true})` |
| Auto-calc luas | ✅ | Geodesic formula (Shoelace + radius bumi) → field readonly |
| Layer switching | ✅ | OSM + Satelit ESRI |
| Fullscreen peta | ✅ | Dashboard + create/edit blok |
| Responsive HP | ✅ | Map 300px mobile, 450px desktop. Sidebar z-index fix |

**Potensi bug peta:**
- Fullscreen di mobile: button "Kecilkan" mungkin masih tertutup di beberapa browser (safe area bottom)
- Polygon label bisa overlap jika blok berdekatan/kecil

---

## 10. Analisis Rule-Based System

### Input/Fakta
Dari tabel `kondisi_lahans`:
- warna_daun, ph_tanah, kelembaban_tanah, musim_saat_ini, kondisi_drainase, gejala_defisiensi, kondisi_pelepah, kondisi_tandan, ada_serangan_hama

Dari tabel `blok_lahans`:
- kategori_umur (accessor dari tahun_tanam)

### Rules
- Disimpan di database: tabel `rule_bases_lanjutan` (25 rules via seeder)
- Bisa ditambah/edit via UI (menu Rule Base)
- Setiap rule punya `aktif` boolean (bisa di-disable)

### Proses Forward Chaining (`RbsService::evaluasiRule()`)
1. Ambil kondisi lahan terbaru
2. Ambil kategori umur dari blok
3. Loop semua rule aktif (urut prioritas)
4. Untuk setiap rule: evaluasi AND logic — semua kondisi non-NULL harus cocok
5. Kumpulkan rules terpicu
6. Tentukan status dominan (hierarki: Darurat > Segera > Normal > Tunda)
7. Susun output: masalah, pupuk, saran, dosis, catatan

### Output Rekomendasi
- `masalah_teridentifikasi` — array masalah unik dari rules terpicu
- `rekomendasi_pupuk` — deduplicate by jenis_pupuk_utama [{jenis, dosis, metode, waktu}]
- `saran_tindakan_utama` — gabungan 3 rule prioritas tertinggi
- `status_kebutuhan_dominan` — Darurat/Segera/Normal/Tunda
- `dosis_urea`, `dosis_kcl` — kg/pokok (dari formula: base × multiplier_tanah × multiplier_topografi)
- `total_urea`, `total_kcl` — dosis × SPH × luas_ha
- `catatan_dosis` — konteks kapan dosis boleh diaplikasikan (berdasarkan status)

### Potensi Bug RBS
- Jika blok belum punya `tahun_tanam`/`jenis_tanah`/`topografi` → dosis return null (handled)
- Jika tidak ada rule terpicu → status Normal, saran standar (handled)
- Rules pH hanya evaluasi jika data pH tersedia di kondisi (handled)

---

## 11. Bug atau Masalah yang Terlihat

| # | Bug | File | Penyebab | Prioritas | Saran |
|---|-----|------|----------|-----------|-------|
| 1 | Model legacy masih ada | `KriteriaLahan.php`, `RekomendasiSpk.php`, `RuleBase.php` | Refactor bertahap, file lama belum dihapus | Rendah | Hapus file + tabel jika yakin tidak dipakai |
| 2 | Admin model masih referensi `rekomendasiSpks` | `Admin.php` | Legacy relasi | Rendah | Hapus method `rekomendasiSpks()` |
| 3 | Fullscreen peta di mobile mungkin tertutup browser bar | `blok_lahan/create.blade.php` | Safe area bottom browser mobile | Sedang | Tambah `padding-bottom: env(safe-area-inset-bottom)` |
| 4 | Searchable select: jika banyak options (100+) bisa lambat | `components/searchable-select.blade.php` | Render semua options di HTML | Rendah | Pertimbangkan lazy load / virtual scroll jika data besar |
| 5 | Sidebar collapse di desktop: map perlu invalidateSize | `layouts/app.blade.php` | Map tidak auto-resize saat sidebar collapse | Sedang | Dispatch `map.invalidateSize()` setelah transition selesai |

---

## 12. Error yang Perlu Saya Isi Manual

```
ERROR TERMINAL:
[paste error terminal di sini]

ERROR BROWSER:
[paste error browser di sini]

BUG YANG SAYA RASAKAN:
[paste masalah yang saya alami di sini, misalnya:
- Halaman X tidak bisa dibuka
- Tombol Y tidak berfungsi
- Tampilan Z rusak di HP
- Data tidak tersimpan saat submit form
]
```

---

## 13. Cara Menjalankan Project

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

# Clear cache jika ada masalah
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Login default:** username: `admin`, password: `password` (dari AdminSeeder)

---

## 14. Checklist Testing

- [ ] Login dengan credential benar
- [ ] Login dengan credential salah (harus error)
- [ ] Logout (modal konfirmasi)
- [ ] Dashboard: peta tampil, polygon muncul
- [ ] Dashboard: filter pemilik, stats update
- [ ] Dashboard: fullscreen peta
- [ ] Tambah anggota baru
- [ ] Edit anggota
- [ ] Hapus anggota (modal konfirmasi)
- [ ] Tambah blok lahan (gambar polygon)
- [ ] Tambah blok lahan (paste GeoJSON)
- [ ] Luas otomatis terhitung dari polygon
- [ ] Edit blok lahan (polygon muncul di peta)
- [ ] Detail blok lahan (info + RBS)
- [ ] Input kondisi lahan baru
- [ ] Curah hujan sesuai musim (smart dropdown)
- [ ] Gejala defisiensi multi-select
- [ ] Jalankan analisis 1 blok
- [ ] Jalankan analisis semua
- [ ] Detail hasil analisis (rules terpicu, pupuk, saran, dosis, catatan)
- [ ] Laporan: filter anggota + blok + status
- [ ] Laporan: cetak (window.print)
- [ ] Buka di HP: peta responsive
- [ ] Buka di HP: tabel tidak overflow horizontal
- [ ] Buka di HP: sidebar bisa buka/tutup
- [ ] Searchable dropdown berfungsi (cari, pilih)

---

## 15. Instruksi untuk Claude

Salin prompt berikut ke Claude beserta isi file ini:

---

> Berdasarkan PROJECT_CONTEXT_FOR_CLAUDE.md ini, tolong buatkan 1 file BUGFIX_PLAN.md lengkap berisi analisis bug, rencana perbaikan, file yang perlu diubah, contoh kode sebelum-sesudah, perintah terminal, dan checklist testing. Jangan langsung mengubah kode. Gunakan bahasa Indonesia dan jelaskan dengan bahasa yang mudah dipahami pemula.

---
