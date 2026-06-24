# PROJECT INDEX — Sistem Rekomendasi Pemupukan Kelapa Sawit

> **Framework:** Laravel 11 · **PHP:** ^8.2 · **Database:** SQLite · **Last Updated:** 24 Juni 2026

---

## 1. GAMBARAN UMUM

Aplikasi web berbasis **Rule-Based System (RBS)** untuk memberikan rekomendasi pemupukan lahan kelapa sawit kepada kelompok tani. Sistem menganalisis kondisi lahan (pH tanah, gejala visual daun, iklim, dll.) dan mencocokkannya dengan knowledge base berupa rule IF-THEN untuk menghasilkan rekomendasi pupuk yang tepat sasaran.

**Alur Utama:**
```
Input Data Blok Lahan → Observasi Kondisi Lahan → Analisis RBS (Forward Chaining) → Rekomendasi Pupuk → Laporan PDF
```

---

## 2. STRUKTUR DIREKTORI

```
app/
├── Http/
│   ├── Controllers/          # 9 controller
│   └── Middleware/
│       └── AdminAuthenticated.php
├── Models/                   # 6 model
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── RbsService.php        # ← Logika utama RBS

database/
├── migrations/               # 22 file migrasi
├── seeders/
│   ├── AdminSeeder.php
│   ├── DatabaseSeeder.php
│   ├── RuleBaseLanjutanSeeder.php  # 20 rule default
│   └── RuleCurahHujanGulmaSeeder.php
└── database.sqlite

resources/views/              # 26 blade template
routes/
└── web.php                   # Semua route dalam 1 file
```

---

## 3. MODELS & RELASI DATABASE

### 3.1 Admin (`admins`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `username` | string | Login credential |
| `password` | string (hashed) | |
| `nama_lengkap` | string | |

**Relasi:** Tidak ada relasi HasMany langsung ke model lain (tapi `admin_id` disimpan di `rekomendasi_rbs`).  
**Auth Guard:** `admin` (custom guard, bukan `users` default)

---

### 3.2 Anggota (`anggotas`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `nama` | string(100) unique | Nama petani anggota |
| `no_hp` | string(20) nullable | |
| `alamat` | text nullable | |

**Relasi:**
- `hasMany(BlokLahan)` → satu anggota bisa punya banyak blok lahan

**Accessor:** `getJumlahBlokAttribute()` — hitung jumlah blok

---

### 3.3 BlokLahan (`blok_lahans`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `anggota_id` | FK → anggotas | |
| `nama_blok` | string(100) | |
| `luas_ha` | double | Luas dalam hektar |
| `sph` | integer | Standar Pohon per Hektar |
| `koordinat_geojson` | longText | Polygon koordinat peta |
| `tahun_tanam` | integer | Untuk hitung umur tanaman |
| `jenis_tanah` | string | Lempung/Gambut/PMK/dll |
| `topografi` | string | Datar/Bergelombang/Curam |

**Relasi:**
- `belongsTo(Anggota)`
- `hasMany(KondisiLahan)` 
- `hasOne(KondisiLahan)` → `kondisiTerbaru` (latestOfMany `tanggal_observasi`)
- `hasMany(RekomendasiRbs)`
- `hasOne(RekomendasiRbs)` → `rekomendasiRbsTerbaru` (where `is_latest=true`)

**Accessor:**
- `getNamaPemilikAttribute()` — dari relasi anggota
- `getUmurTanamanAttribute()` — `now()->year - tahun_tanam`
- `getKategoriUmurAttribute()` — `Belum Menghasilkan` (<3th) / `Remaja` (3-8) / `Menghasilkan Muda` (8-14) / `Menghasilkan Tua` (14-25) / `Tua Renta` (>25)

---

### 3.4 KondisiLahan (`kondisi_lahans`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `blok_lahan_id` | FK → blok_lahans CASCADE | |
| `tanggal_observasi` | date | |
| `tanggal_pemupukan_terakhir` | date nullable | |
| `ph_tanah` | decimal(4,2) nullable | Rentang 3.0–8.0 |
| `kelembaban_tanah` | enum | Sangat Kering/Kering/Normal/Lembab/Sangat Lembab |
| `curah_hujan_kategori` | enum | Sangat Rendah/Rendah/Normal/Tinggi/Sangat Tinggi |
| `musim_saat_ini` | enum | Musim Hujan/Musim Kemarau/Peralihan |
| `warna_daun` | enum | 8 pilihan (Hijau Normal s/d Bercak Nekrotik) |
| `kondisi_pelepah` | enum | Normal/Patah/Kering Prematur/Terhambat |
| `gejala_defisiensi` | json | Array: N, P, K, Mg, B, Fe, Zn |
| `kondisi_tandan` | enum | Normal/Kecil/Rontok Prematur/Busuk/Tidak Ada |
| `kondisi_drainase` | enum | Baik/Cukup/Buruk—Tergenang |
| `ada_gulma_dominan` | boolean | default false |
| `ada_serangan_hama` | boolean | default false |
| `catatan_observasi` | text nullable | |

**Relasi:**
- `belongsTo(BlokLahan)`
- `hasMany(RekomendasiRbs)`

**Accessor:** `getLabelPhAttribute()` — konversi pH ke label Sangat Masam/Masam/Optimal/Netral/Basa


---

### 3.5 RuleBaseLanjutan (`rule_bases_lanjutan`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| **KONDISI (IF)** | | |
| `kondisi_warna_daun` | string(100) nullable | |
| `kondisi_ph_min` | decimal(4,2) nullable | |
| `kondisi_ph_max` | decimal(4,2) nullable | |
| `kondisi_kelembaban` | string(50) nullable | |
| `kondisi_curah_hujan_kategori` | string(50) nullable | |
| `kondisi_musim` | string(50) nullable | |
| `kondisi_drainase` | string(50) nullable | |
| `kondisi_defisiensi` | string(50) nullable | Satu unsur target (N/P/K/Mg/dll) |
| `kondisi_kategori_umur` | string(50) nullable | NULL = berlaku semua umur |
| `kondisi_pelepah` | string(100) nullable | |
| `kondisi_tandan` | string(100) nullable | |
| `ada_serangan_hama` | boolean nullable | |
| `ada_gulma_dominan` | boolean nullable | |
| `kondisi_intermediate` | json nullable | Output flag untuk Rule Chaining |
| `prasyarat_intermediate` | json nullable | Prasyarat dari rule sebelumnya |
| **OUTPUT (THEN)** | | |
| `indikasi_masalah` | string(255) | |
| `jenis_pupuk_utama` | string(100) | |
| `jenis_pupuk_pendukung` | string(100) nullable | |
| `dosis_anjuran` | string(150) | |
| `metode_aplikasi` | string(255) nullable | |
| `waktu_aplikasi` | string(150) nullable | |
| `saran_tindakan` | text | |
| `status_kebutuhan` | enum | Darurat/Segera/Normal/Tunda |
| `prioritas` | tinyint | 1 (tertinggi) – 10 (terendah) |
| `aktif` | boolean | default true |
| `keterangan_rule` | text nullable | |

**Scope:** `scopeAktif()` — filter hanya rule aktif

---

### 3.6 RekomendasiRbs (`rekomendasi_rbs`)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `blok_lahan_id` | FK → blok_lahans CASCADE | |
| `kondisi_lahan_id` | FK → kondisi_lahans CASCADE | |
| `admin_id` | FK → admins CASCADE | |
| `tanggal_analisis` | date | |
| `is_latest` | boolean | Flag rekomendasi aktif/terbaru |
| `nomor_analisis` | integer nullable | Urutan analisis ke-N |
| `rules_terpicu` | json | Array rule yang cocok |
| `masalah_teridentifikasi` | json | Array string masalah |
| `rekomendasi_pupuk` | json | Array {jenis, dosis, metode, waktu} |
| `saran_tindakan_utama` | text | Top 3 saran digabung " \| " |
| `status_kebutuhan_dominan` | enum | Darurat/Segera/Normal/Tunda |
| `jumlah_rule_terpicu` | tinyint | |
| `dosis_urea` | double | kg/pohon |
| `dosis_kcl` | double | kg/pohon |
| `total_urea` | double | kg untuk seluruh blok |
| `total_kcl` | double | kg untuk seluruh blok |
| `catatan_dosis` | text nullable | |
| `jadwal_pemupukan` | json nullable | Fitur 2: jadwal 2 tahap |
| `validitas_rekomendasi` | string(50) | Estimasi Visual / Cukup Kuat |
| `catatan_validitas` | text nullable | |
| `confidence_score` | integer | 0–100 |
| `confidence_label` | string | Rendah/Sedang/Tinggi |
| `catatan_confidence` | text nullable | |
| `data_cukup` | boolean | Fitur 7 |
| `data_kurang` | json nullable | Field yang belum terisi |
| `notifikasi_data` | text nullable | |

**Relasi:** `belongsTo(BlokLahan)`, `belongsTo(KondisiLahan)`, `belongsTo(Admin)`

**Accessor:**
- `getWarnaBadgeAttribute()` → red/orange/green/gray
- `getLabelStatusAttribute()` → `Defisiensi Berat` / `Perlu Pupuk` / `Sehat` / `Tunda Pupuk`
- `getKarungUreaAttribute()` → `ceil(total_urea / 50)`
- `getKarungKclAttribute()` → `ceil(total_kcl / 50)`
- `getWarnaConfidenceAttribute()` → green/blue/amber
- `getWarnaValiditasAttribute()` → green/blue/amber

**Static:** `labelStatus(?string $status): string`


---

## 4. ROUTES (routes/web.php)

```
GET  /                           → redirect ke dashboard

# Auth (guest:admin only)
GET  /login                      → AuthController@showLoginForm       [login]
POST /login                      → AuthController@login               [login.submit]
POST /logout                     → AuthController@logout              [logout]

# Protected (AdminAuthenticated middleware)
GET  /dashboard                  → DashboardController@index          [dashboard]

# Resource: Anggota (except show)
GET  /anggota                    → AnggotaController@index            [anggota.index]
GET  /anggota/create             → AnggotaController@create           [anggota.create]
POST /anggota                    → AnggotaController@store            [anggota.store]
GET  /anggota/{id}/edit          → AnggotaController@edit             [anggota.edit]
PUT  /anggota/{id}               → AnggotaController@update           [anggota.update]
DEL  /anggota/{id}               → AnggotaController@destroy          [anggota.destroy]

# Resource: Blok Lahan (full)
GET  /blok-lahan                 → BlokLahanController@index          [blok-lahan.index]
GET  /blok-lahan/create          → BlokLahanController@create         [blok-lahan.create]
POST /blok-lahan                 → BlokLahanController@store          [blok-lahan.store]
GET  /blok-lahan/{id}            → BlokLahanController@show           [blok-lahan.show]
GET  /blok-lahan/{id}/edit       → BlokLahanController@edit           [blok-lahan.edit]
PUT  /blok-lahan/{id}            → BlokLahanController@update         [blok-lahan.update]
DEL  /blok-lahan/{id}            → BlokLahanController@destroy        [blok-lahan.destroy]

# Resource: Kondisi Lahan (except show)
GET  /kondisi-lahan              → KondisiLahanController@index       [kondisi-lahan.index]
GET  /kondisi-lahan/create       → KondisiLahanController@create      [kondisi-lahan.create]
POST /kondisi-lahan              → KondisiLahanController@store       [kondisi-lahan.store]
GET  /kondisi-lahan/{id}/edit    → KondisiLahanController@edit        [kondisi-lahan.edit]
PUT  /kondisi-lahan/{id}         → KondisiLahanController@update      [kondisi-lahan.update]
DEL  /kondisi-lahan/{id}         → KondisiLahanController@destroy     [kondisi-lahan.destroy]

# Resource: Rule Base (except show)
GET  /rule-base/info             → RuleBaseController@info            [rule-base.info]
GET  /rule-base                  → RuleBaseController@index           [rule-base.index]
GET  /rule-base/create           → RuleBaseController@create          [rule-base.create]
POST /rule-base                  → RuleBaseController@store           [rule-base.store]
GET  /rule-base/{id}/edit        → RuleBaseController@edit            [rule-base.edit]
PUT  /rule-base/{id}             → RuleBaseController@update          [rule-base.update]
DEL  /rule-base/{id}             → RuleBaseController@destroy         [rule-base.destroy]

# Panduan
GET  /panduan                    → view('panduan')                    [panduan]

# Analisis RBS
GET  /rbs                        → RbsController@index                [rbs.index]
GET  /rbs/daftar-blok-belum-analisis → RbsController@daftarBlokBelumAnalisis [rbs.daftarBlokBelumAnalisis]
POST /rbs/analisis/{blokLahan}   → RbsController@analisis             [rbs.analisis]
POST /rbs/analisis-semua         → RbsController@analisisSemua        [rbs.analisisSemua]
GET  /rbs/detail/{blokLahan}     → RbsController@detail               [rbs.detail]

# Laporan
GET  /laporan                    → LaporanController@index            [laporan.index]
GET  /laporan/{id}               → LaporanController@show             [laporan.show]
GET  /laporan/{id}/pdf           → LaporanController@exportPdf        [laporan.pdf]
GET  /laporan/{id}/ringkasan     → LaporanController@exportRingkasan  [laporan.ringkasan]

# API Endpoints
GET  /api/rbs-popup/{blokLahan}  → RbsController@apiPopup             [api.rbs.popup]
POST /api/cuaca/fetch            → CuacaController@fetch              [api.cuaca.fetch]
```


---

## 5. CONTROLLERS

### 5.1 AuthController
- `showLoginForm()` — tampilkan form login (redirect dashboard jika sudah login)
- `login(Request)` — autentikasi via `Auth::guard('admin')->attempt()`
- `logout(Request)` — invalidate session dan redirect ke login

---

### 5.2 DashboardController
- `index()` — WebGIS dashboard
  - Load semua blok lahan + relasi anggota, kondisi terbaru, rekomendasi terbaru
  - Build `$mapData` array (id, geojson, status RBS, dosis pupuk, dll.) untuk peta Leaflet
  - Hitung stats: total blok, sudah analisis, darurat, segera, belum kondisi
  - Hitung delta bulan lalu (D1) untuk perbandingan trend
  - Identifikasi `$blokPerluPerhatian` (E1): belum analisis atau > 90 hari

---

### 5.3 AnggotaController
- `index()` — daftar anggota dengan `withCount('blokLahans')`, paginate 10
- `create()` — form tambah anggota
- `store(Request)` — validasi (nama unique) + create
- `edit(Anggota)` — form edit
- `update(Request, Anggota)` — validasi (nama unique kecuali diri sendiri) + update
- `destroy(Anggota)` — cek apakah masih punya blok → tolak jika iya

---

### 5.4 BlokLahanController
- `index(Request)` — daftar blok, filter by anggota/status RBS, group by anggota
- `create()` — form tambah blok (load existing bloks untuk tampilan peta)
- `store(Request)` — validasi termasuk GeoJSON valid; warning jika SPH < 100 atau > 160
- `show(BlokLahan)` — detail blok dengan kondisi terbaru & rekomendasi terbaru
- `edit(BlokLahan)` — form edit (load blok lain untuk peta)
- `update(Request, BlokLahan)` — sama seperti store dengan warning SPH
- `destroy(BlokLahan)` — hapus blok (cascade ke kondisi & rekomendasi)

---

### 5.5 KondisiLahanController
- `index(Request)` — daftar kondisi grouped by anggota, filter by anggota, sort terbaru
- `create(Request)` — form input kondisi; auto-fetch centroid koordinat untuk cuaca API
- `store(Request)` — validasi + simpan; jalankan `validasiKonsistensi()` → flash warning
- `edit(KondisiLahan)` — form edit kondisi
- `update(Request, KondisiLahan)` — update + warning konsistensi
- `destroy(KondisiLahan)` — hapus
- **Private:** `hitungCentroid(?string)` — hitung titik tengah polygon GeoJSON
- **Private:** `validasiKonsistensi(array)` — 9 aturan validasi silang (musim vs kelembaban, dll.)

---

### 5.6 RuleBaseController
- `index()` — daftar semua rule, urut prioritas
- `info()` — halaman informasi/panduan rule base
- `create()` — form tambah rule
- `store(Request)` → `validateRule()` + create
- `edit(string $id)` — form edit rule
- `update(Request, string $id)` → `validateRule()` + update
- `destroy(string $id)` — hapus rule
- **Private:** `validateRule(Request)` — validasi semua field kondisi dan output rule

---

### 5.7 RbsController
- `index(Request)` — daftar blok + status analisis RBS, filter by anggota/blok, stats global
- `analisis(BlokLahan)` — trigger `RbsService::analisis()` untuk 1 blok
- `analisisSemua()` — trigger `RbsService::analisisSemua()` untuk semua blok berdata
- `detail(BlokLahan)` — tampilkan hasil analisis detail + histori rekomendasi
- `apiPopup(BlokLahan)` — JSON untuk popup peta WebGIS
- `daftarBlokBelumAnalisis()` — JSON list blok yang belum dianalisis (untuk progress bar)

---

### 5.8 LaporanController
- `index(Request)` — daftar rekomendasi, filter histori/status/anggota/blok, grouped by anggota
  - Hitung grand total Urea & KCl hanya dari blok layak (Normal/Segera)
  - Hitung kebutuhan karung (50 kg/karung)
- `show(RekomendasiRbs)` — detail laporan 1 rekomendasi
- `exportPdf(RekomendasiRbs)` — generate PDF via DomPDF → download
- `exportRingkasan(RekomendasiRbs)` — export teks plain (format WhatsApp-friendly); support `?format=json`

---

### 5.9 CuacaController
- `fetch(Request)` — API internal; ambil data iklim dari **Open-Meteo API**
  - Parameter: `lat`, `lng` (koordinat centroid blok)
  - Cache 12 jam per koordinat (rounded 2 desimal)
  - Ambil data historis 30 hari terakhir: `precipitation_sum` + `et0_fao_evapotranspiration`
  - Return: `curah_hujan_kategori` + `musim_saat_ini`
  - Fallback graceful (HTTP 200 + `success: false`) jika API gagal
- **Private:** `tentukanKategoriCurahHujan(float)` — BMKG-adapted thresholds
- **Private:** `tentukanMusimDinamis(float, float)` — Water Balance ratio P/ET0 (< 0.8 = kemarau, > 1.2 = hujan)


---

## 6. SERVICE: RbsService (`app/Services/RbsService.php`)

Ini adalah **otak utama sistem**. Mengimplementasikan Forward Chaining Rule-Based System dengan beberapa fitur lanjutan.

### Method Publik

#### `analisis(BlokLahan $blok): array`
Alur lengkap analisis untuk 1 blok:
1. Ambil `kondisiTerbaru` — throw Exception jika kosong
2. `cekKecukupanData()` → Fitur 7
3. Jika data tidak cukup → `hasilDataTidakCukup()`
4. Ambil `kategori_umur` dari blok
5. Ambil semua rule aktif, urut prioritas
6. Loop forward chaining: `cekPrasyaratIntermediate()` + `evaluasiRule()`; kumpulkan `$intermediateFlags`
7. Jika tidak ada rule → `hasilNormal()`
8. `susunHasil()` → simpan dengan `simpanDenganHistori()`

#### `analisisSemua(): array`
Iterasi semua `BlokLahan::whereHas('kondisiLahans')`, panggil `analisis()` per blok, return `['results'=>[], 'errors'=>[]]`.

---

### Metode Private — Evaluasi

#### `evaluasiRule(RuleBaseLanjutan, KondisiLahan, ?string $kategoriUmur): bool`
Cek semua kondisi yang diisi di rule (AND logic). Kondisi NULL di rule = diabaikan. Minimal 1 kondisi harus cocok (safety guard).

**Field yang dievaluasi:**
- `kondisi_warna_daun` → exact match
- `kondisi_ph_min` / `kondisi_ph_max` → range check
- `kondisi_kelembaban` → exact match
- `kondisi_curah_hujan_kategori` → exact match
- `kondisi_musim` → exact match
- `kondisi_drainase` → exact match
- `kondisi_defisiensi` → `in_array` check (array gejala)
- `kondisi_pelepah` → exact match
- `ada_serangan_hama` → boolean check (hanya cek jika rule = true)
- `ada_gulma_dominan` → boolean match
- `kondisi_tandan` → exact match
- `kondisi_kategori_umur` → exact match

#### `cekPrasyaratIntermediate(RuleBaseLanjutan, array $flags): bool`
Rule Chaining (A2) — rule lanjutan hanya aktif jika rule pendahulunya sudah terpicu.

---

### Metode Private — Fitur

| Metode | Fitur | Keterangan |
|--------|-------|-----------|
| `cekKecukupanData(KondisiLahan)` | **Fitur 7** | 7 field penting; cukup jika ≥5 terisi + warna daun ada |
| `tentukanValiditasRekomendasi()` | **Fitur 3** | `Cukup Kuat` vs `Estimasi Visual` |
| `hitungConfidence()` | **Fitur 6** | Skor 0–100: A=kelengkapan(40) + B=rule_terpicu(25) + C=visual-unsur(20) - D=penalti(20) |
| `generateJadwalPemupukan()` | **Fitur 2** | Split 2 tahap (Darurat: 70/30, Segera: 60/40, Normal: 50/50) |
| `simpanDenganHistori()` | **Fitur 1** | DB transaction; set `is_latest=false` pada record lama; cek duplikat sebelum buat baru |
| `susunHasil()` | Core | Kumpulkan masalah, pupuk, saran; hitung dosis; simpan |

### Dosis Standar Pupuk (hitungDosisStandar)
> Berdasarkan SPH dan kategori umur tanaman
- Urea (kg/pohon) dan KCl (kg/pohon) × SPH × luas_ha = total per blok

### Mapping Visual Unsur (untuk Confidence Score)
```php
'Hijau Pucat'         → ['N']
'Kuning Merata'       → ['N', 'Zn']
'Kuning Tepi'         → ['K']
'Oranye/Kemerahan'    → ['K']
'Kuning Antar Tulang' → ['Mg', 'Fe']
'Coklat Ujung'        → ['P', 'K']
'Bercak Nekrotik'     → ['K', 'P']
```


---

## 7. KNOWLEDGE BASE — Rule Default (RuleBaseLanjutanSeeder)

20 rule aktif di-seed sebagai data awal:

| # | Grup | Trigger Utama | Status | Prioritas |
|---|------|--------------|--------|-----------|
| 1 | Defisiensi N | Kuning Merata + defisiensi N | Segera | 2 |
| 2 | Defisiensi N ringan | Hijau Pucat + defisiensi N | Normal | 4 |
| 3 | Defisiensi K berat | Oranye/Kemerahan + defisiensi K | **Darurat** | 1 |
| 4 | Defisiensi K sedang | Kuning Tepi + defisiensi K | Segera | 2 |
| 5 | Defisiensi Mg | Kuning Antar Tulang + defisiensi Mg | Segera | 3 |
| 6 | Defisiensi B | defisiensi B + Pelepah Terhambat | Segera | 2 |
| 7 | pH Sangat Masam | pH 3.0–4.5 | **Darurat** | 1 |
| 8 | pH Masam | pH 4.5–5.5 | Normal | 4 |
| 9 | Drainase Buruk | Buruk—Tergenang + Sangat Lembab | Tunda | 1 |
| 10 | Kemarau Parah | Kemarau + Sangat Kering | Tunda | 2 |
| 11 | Kemarau Biasa | Kemarau + Kering | Normal | 5 |
| 12 | Tanaman Muda TBM | Umur Belum Menghasilkan + Kuning Merata | Segera | 2 |
| 13 | Tanaman Tua Renta | Umur Tua Renta | Tunda | 8 |
| 14 | Kondisi Optimal | Hijau Normal + pH 5.5–6.5 + Drainase Baik | Normal | 9 |
| 15 | Defisiensi P | Coklat Ujung + defisiensi P | Segera | 3 |
| 16 | Bercak Nekrotik/Hama | Bercak Nekrotik | Segera | 2 |
| 17 | Defisiensi Fe | Kuning Antar Tulang + defisiensi Fe + pH > 6.5 | Segera | 3 |
| 18 | Pelepah Kering | Kering Prematur | Segera | 3 |
| 19 | Tandan Rontok | Rontok Prematur | Segera | 2 |
| 20 | Busuk Pangkal Tandan | Busuk Pangkal | **Darurat** | 1 |
| 21 | Musim Hujan Normal | Musim Hujan + Normal | Normal | 6 |
| 22 | Defisiensi Zn | defisiensi Zn + Kuning Merata | Segera | 3 |
| 23 | Serangan Hama | ada_serangan_hama = true | Segera | 3 |
| 24 | Tanaman Remaja | Remaja + Hijau Normal | Normal | 6 |
| 25 | Tanaman Tua Produktif | Menghasilkan Tua + Hijau Pucat | Normal | 5 |

> Catatan: Jumlah rule bisa berubah. RuleCurahHujanGulmaSeeder menambah rule terkait curah hujan dan gulma.

---

## 8. VIEWS (Blade Templates)

### Layout Utama
- `layouts/app.blade.php` — Sidebar navigasi, topbar, flash message, Leaflet CSS/JS inject

### Auth
- `auth/login.blade.php` — Form login username & password

### Dashboard
- `dashboard/index.blade.php` — Peta WebGIS (Leaflet.js), popup status per blok, stat cards, tabel blok perlu perhatian

### Anggota
- `anggota/index.blade.php` — Tabel daftar anggota + jumlah blok, pagination
- `anggota/create.blade.php` — Form tambah anggota
- `anggota/edit.blade.php` — Form edit anggota

### Blok Lahan
- `blok_lahan/index.blade.php` — Tabel blok grouped by anggota, filter status/anggota
- `blok_lahan/create.blade.php` — Form + peta Leaflet draw polygon (koordinat GeoJSON)
- `blok_lahan/edit.blade.php` — Form edit + peta
- `blok_lahan/show.blade.php` — Detail blok: info umum, kondisi terbaru, rekomendasi terbaru

### Kondisi Lahan
- `kondisi_lahan/index.blade.php` — Daftar kondisi grouped by anggota, filter
- `kondisi_lahan/create.blade.php` — Form input observasi; auto-fetch cuaca dari Open-Meteo via AJAX
- `kondisi_lahan/edit.blade.php` — Form edit observasi

### Analisis RBS
- `rbs/index.blade.php` — Daftar blok + status analisis, filter, tombol analisis per blok / analisis semua
- `rbs/detail.blade.php` — Detail hasil analisis: status, confidence, validitas, masalah, pupuk, jadwal, histori
- `rbs/partials/_hasil_rbs.blade.php` — Partial component hasil RBS (digunakan di detail)

### Rule Base
- `rule_base/index.blade.php` — Tabel semua rule dengan kondisi IF dan output THEN
- `rule_base/create.blade.php` — Form tambah rule
- `rule_base/edit.blade.php` — Form edit rule
- `rule_base/info.blade.php` — Halaman penjelasan cara kerja Rule-Based System

### Laporan
- `laporan/index.blade.php` — Ringkasan rekomendasi per anggota, grand total pupuk, export
- `laporan/show.blade.php` — Detail laporan satu rekomendasi
- `laporan/pdf.blade.php` — Template PDF (DomPDF): header, data blok, rekomendasi, tanda tangan

### Lainnya
- `panduan.blade.php` — Panduan penggunaan sistem
- `components/status-badge.blade.php` — Badge status dengan warna dinamis
- `components/searchable-select.blade.php` — Dropdown dengan fitur search
- `components/custom-select.blade.php` — Custom select component
- `components/filter-searchable.blade.php` — Filter dropdown dengan search


---

## 9. SKEMA DATABASE — ERD Ringkas

```
admins
  └── id, username, password, nama_lengkap

anggotas
  └── id, nama (unique), no_hp, alamat
      │
      └─── [1:N] blok_lahans
                  └── id, anggota_id, nama_blok, luas_ha, sph,
                      koordinat_geojson, tahun_tanam, jenis_tanah, topografi
                      │
                      ├─── [1:N] kondisi_lahans
                      │           └── id, blok_lahan_id, tanggal_observasi,
                      │               ph_tanah, kelembaban_tanah, curah_hujan_kategori,
                      │               musim_saat_ini, warna_daun, kondisi_pelepah,
                      │               gejala_defisiensi (json), kondisi_tandan,
                      │               kondisi_drainase, ada_gulma_dominan,
                      │               ada_serangan_hama, catatan_observasi
                      │
                      └─── [1:N] rekomendasi_rbs
                                  └── id, blok_lahan_id, kondisi_lahan_id, admin_id,
                                      tanggal_analisis, is_latest, nomor_analisis,
                                      rules_terpicu (json), masalah_teridentifikasi (json),
                                      rekomendasi_pupuk (json), saran_tindakan_utama,
                                      status_kebutuhan_dominan, jumlah_rule_terpicu,
                                      dosis_urea, dosis_kcl, total_urea, total_kcl,
                                      jadwal_pemupukan (json), validitas_rekomendasi,
                                      confidence_score, confidence_label, ...

rule_bases_lanjutan (independen — knowledge base)
  └── id, kondisi_* (nullable), output_*, status_kebutuhan, prioritas, aktif
```

---

## 10. MIGRASI — Urutan Kronologis

| File | Aksi |
|------|------|
| `0001_01_01_000000` | Create users table (default Laravel) |
| `0001_01_01_000001` | Create cache table |
| `0001_01_01_000002` | Create jobs table |
| `2026_05_20_205515` | Create admins table |
| `2026_05_20_205515` | Create blok_lahans table (basis) |
| `2026_05_20_205516` | Create kriteria_lahans table |
| `2026_05_20_205516` | Create rule_bases table |
| `2026_05_20_205517` | Create rekomendasi_spks table |
| `2026_05_20_231656` | Add nama_pemilik to blok_lahans |
| `2026_06_04_000000` | Add panen fields to blok_lahans |
| `2026_06_04_000001` | Modify jenis_tanah column |
| `2026_06_04_100000` | Create kondisi_lahans table |
| `2026_06_04_100001` | Create rule_bases_lanjutan table |
| `2026_06_04_100002` | Create rekomendasi_rbs table |
| `2026_06_04_100003` | Add missing columns to rule_bases_lanjutan |
| `2026_06_04_200000` | Add dosis columns to rekomendasi_rbs |
| `2026_06_07_000000` | Drop panen fields from blok_lahans |
| `2026_06_07_100000` | Create anggotas table |
| `2026_06_07_100001` | Add anggota_id to blok_lahans |
| `2026_06_07_200000` | Merge kriteria (tahun_tanam, jenis_tanah, topografi) ke blok_lahans |
| `2026_06_07_200001` | Add catatan_dosis to rekomendasi_rbs |
| `2026_06_12_213121` | Add intermediate fields to rule_bases_lanjutan |
| `2026_06_12_213130` | Add tanggal_pemupukan_terakhir to kondisi_lahans |
| `2026_06_12_213138` | Create realisasi_pemupukans table |
| `2026_06_14_000001` | Add histori fields (is_latest, nomor_analisis, jadwal, validitas, confidence, data_cukup) |
| `2026_06_14_000002` | Add curah_hujan + gulma columns to rule_bases_lanjutan |

---

## 11. DEPENDENSI UTAMA (composer.json)

| Package | Versi | Fungsi |
|---------|-------|--------|
| `laravel/framework` | ^11.0 | Framework utama |
| `barryvdh/laravel-dompdf` | ^3.1 | Generate laporan PDF |
| `laravel/tinker` | ^2.9 | REPL untuk debugging |

**Dev dependencies:** PHPUnit, Faker, Pint (code style), Ignition (error page), Sail (Docker)

---

## 12. INTEGRASI EKSTERNAL

### Open-Meteo API
- **URL:** `https://api.open-meteo.com/v1/forecast`
- **Trigger:** Form input kondisi lahan (AJAX POST ke `/api/cuaca/fetch`)
- **Parameter:** `lat`, `lng`, `past_days=30`, `daily=precipitation_sum,et0_fao_evapotranspiration`
- **Cache:** 12 jam per koordinat (key: `cuaca_{lat}_{lng}`)
- **Output:** `curah_hujan_kategori`, `musim_saat_ini` sebagai default form
- **Fallback:** Graceful degradation — jika API gagal, user isi manual

### Leaflet.js (Frontend Maps)
- WebGIS dashboard: tampilkan polygon blok lahan dengan warna per status RBS
- Form blok lahan: draw polygon untuk input koordinat GeoJSON
- Popup klik → AJAX ke `/api/rbs-popup/{blokLahan}`

---

## 13. MIDDLEWARE & AUTENTIKASI

### AdminAuthenticated (`app/Http/Middleware/AdminAuthenticated.php`)
- Cek `Auth::guard('admin')->check()`
- Redirect ke `/login` jika belum login
- Diterapkan pada semua route kecuali `/login` dan `/logout`

### Auth Config (`config/auth.php`)
- Guard: `admin` → model `App\Models\Admin`, provider `admins`
- Session-based authentication

---

## 14. FITUR SISTEM (Ringkasan)

| # | Fitur | Implementasi |
|---|-------|-------------|
| 1 | **Histori Rekomendasi** | `is_latest` flag; `nomor_analisis`; `simpanDenganHistori()` mencegah duplikat |
| 2 | **Jadwal Pemupukan 2 Tahap** | `generateJadwalPemupukan()`: split berdasarkan status (Darurat 70/30, Segera 60/40) |
| 3 | **Validitas Rekomendasi** | `Cukup Kuat` / `Estimasi Visual` berdasarkan kombinasi data tersedia |
| 4 | **Integrasi Cuaca** | Open-Meteo API + Water Balance ratio untuk deteksi musim dinamis |
| 5 | **WebGIS Peta** | Leaflet.js + GeoJSON polygon + popup status real-time |
| 6 | **Confidence Score** | Skor 0–100 dari 4 komponen; penalti data kontradiktif |
| 7 | **Notifikasi Data Belum Cukup** | Minimal 5/7 field penting; warning spesifik field yang kurang |
| 8 | **Validasi Konsistensi** | 9 aturan cross-field validation (musim vs kelembaban, dll.) |
| 9 | **Rule Chaining** | `kondisi_intermediate` + `prasyarat_intermediate` untuk chain rule lanjutan |
| 10 | **Laporan PDF** | DomPDF; export teks WhatsApp-friendly |
| 11 | **Analisis Massal** | Analisis semua blok berdata dalam satu klik |

---

## 15. KONVENSI KODE

- **Bahasa:** Indonesia (nama variabel, pesan error, UI)
- **Routing:** Kebab-case (`blok-lahan`, `kondisi-lahan`, `rule-base`)
- **Model naming:** PascalCase singular; tabel plural (kecuali `rekomendasi_rbs`)
- **Status RBS di DB:** `Darurat` / `Segera` / `Normal` / `Tunda`
- **Status RBS di UI:** `Defisiensi Berat` / `Perlu Pupuk` / `Sehat` / `Tunda Pupuk`
- **Validasi:** Dilakukan di Controller dengan `$request->validate()`; pesan custom Bahasa Indonesia
- **JSON columns:** `rules_terpicu`, `masalah_teridentifikasi`, `rekomendasi_pupuk`, `gejala_defisiensi`, `jadwal_pemupukan`, `data_kurang` → di-cast sebagai `array`
- **Soft delete:** Tidak digunakan; semua delete adalah hard delete dengan cascade

