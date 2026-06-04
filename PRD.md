# Product Requirements Document (PRD)
## Sistem Pendukung Keputusan Pemupukan Kelapa Sawit (SPK Sawit)

**Versi:** 1.0  
**Tanggal:** 4 Juni 2026  
**Penulis:** Tim Pengembang  
**Status:** Final — Implementasi Selesai

---

## 1. Ringkasan Eksekutif

SPK Sawit adalah aplikasi web berbasis Laravel 11 yang membantu kelompok tani menentukan dosis pupuk (Urea & KCl) yang tepat per blok lahan kelapa sawit. Aplikasi ini menggunakan dua mekanisme inferensi:

1. **Forward Chaining (SPK)** — Penentuan dosis berdasarkan parameter agronomis tetap (umur tanaman, jenis tanah, topografi)
2. **Rule-Based System (RBS)** — Analisis diagnostik berdasarkan gejala visual tanaman dan kondisi lingkungan real-time

Dilengkapi peta interaktif WebGIS berbasis Leaflet.js untuk visualisasi spasial status pemupukan seluruh blok lahan.

---

## 2. Latar Belakang & Masalah

### Masalah yang Diselesaikan
- Petani kelapa sawit kesulitan menentukan dosis pupuk yang tepat karena banyaknya variabel (umur tanaman, jenis tanah, topografi, kondisi visual)
- Tidak ada sistem terkomputerisasi untuk membantu diagnosa masalah nutrisi tanaman
- Data lahan dan rekomendasi pemupukan tidak terdokumentasi dengan baik
- Tidak ada visualisasi spasial untuk memantau status pemupukan per blok

### Solusi
Aplikasi SPK berbasis web yang menggabungkan inferensi Forward Chaining untuk dosis standar dan Rule-Based System untuk diagnostik kondisi real-time, dengan visualisasi peta interaktif.

---

## 3. Target Pengguna

| Role | Deskripsi |
|------|-----------|
| Admin | Pengguna tunggal sistem (ketua/sekretaris kelompok tani) yang memiliki akses penuh ke seluruh modul |

Aplikasi bersifat **single-tenant** untuk satu kelompok tani.

---

## 4. Teknologi Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11, PHP 8.2 |
| Frontend | Blade Templates, Tailwind CSS v4 |
| Build Tool | Vite 5 |
| Database | MySQL |
| Maps | Leaflet.js 1.9.4 + OpenStreetMap + ESRI Satellite |
| Auth | Laravel Guard Custom (`admin`) |

---

## 5. Arsitektur Sistem

### 5.1 Struktur Aplikasi

```
app/
├── Http/Controllers/
│   ├── AuthController.php          — Login/Logout
│   ├── DashboardController.php     — WebGIS Peta
│   ├── BlokLahanController.php     — CRUD Blok Lahan
│   ├── KriteriaLahanController.php — CRUD Kriteria
│   ├── KondisiLahanController.php  — CRUD Kondisi
│   ├── RuleBaseController.php      — CRUD Rule SPK
│   ├── SpkController.php           — Analisis Forward Chaining
│   ├── RbsController.php           — Analisis RBS
│   └── LaporanController.php       — Laporan & Rekap
├── Models/
│   ├── Admin.php
│   ├── BlokLahan.php
│   ├── KriteriaLahan.php
│   ├── KondisiLahan.php
│   ├── RuleBase.php
│   ├── RuleBaseLanjutan.php
│   ├── RekomendasiSpk.php
│   └── RekomendasiRbs.php
└── Services/
    ├── SpkService.php              — Logic Forward Chaining
    └── RbsService.php              — Logic Rule-Based System
```

### 5.2 Diagram Relasi Database

```
┌─────────────┐       ┌──────────────────┐       ┌────────────────────┐
│   admins    │       │   blok_lahans    │       │  kriteria_lahans   │
├─────────────┤       ├──────────────────┤       ├────────────────────┤
│ id          │       │ id               │◄──────│ blok_lahan_id (FK) │
│ username    │       │ nama_blok        │       │ tahun_tanam        │
│ password    │       │ nama_pemilik     │       │ jenis_tanah        │
│ nama_lengkap│       │ luas_ha          │       │ topografi          │
└──────┬──────┘       │ sph              │       └────────────────────┘
       │              │ koordinat_geojson│
       │              │ total_tonase_panen│      ┌────────────────────┐
       │              │ yield_per_hektar │◄──────│  kondisi_lahans    │
       │              └────────┬─────────┘      ├────────────────────┤
       │                       │                │ blok_lahan_id (FK) │
       │    ┌──────────────────┼────────────┐   │ tanggal_observasi  │
       │    │                  │            │   │ ph_tanah           │
       │    ▼                  ▼            ▼   │ warna_daun         │
┌──────┴─────────┐  ┌─────────────────┐  ┌─────┴──────────────┐    │ gejala_defisiensi │
│rekomendasi_spks│  │rekomendasi_rbs  │  │rule_bases_lanjutan │    │ ...               │
├────────────────┤  ├─────────────────┤  ├────────────────────┤    └────────────────────┘
│ blok_lahan_id  │  │ blok_lahan_id   │  │ kondisi_warna_daun │
│ admin_id       │  │ kondisi_lahan_id│  │ kondisi_ph_min/max │
│ dosis_urea     │  │ admin_id        │  │ kondisi_kelembaban │
│ dosis_kcl      │  │ rules_terpicu   │  │ kondisi_pelepah    │
│ total_urea     │  │ masalah         │  │ kondisi_tandan     │
│ total_kcl      │  │ rekomendasi_pupuk│ │ ada_serangan_hama  │
│ status_akhir   │  │ status_dominan  │  │ indikasi_masalah   │
└────────────────┘  └─────────────────┘  │ jenis_pupuk_utama  │
                                          │ dosis_anjuran      │
┌────────────────┐                        │ status_kebutuhan   │
│  rule_bases    │                        │ prioritas          │
├────────────────┤                        └────────────────────┘
│ parameter_kondisi │
│ takaran_urea      │
│ takaran_kcl       │
│ status_pemupukan  │
└───────────────────┘
```

---

## 6. Fitur & Requirements

---

### 6.1 Modul Autentikasi

**User Story:** Sebagai admin kelompok tani, saya ingin login ke sistem dengan credential yang aman, agar hanya pihak berwenang yang dapat mengakses data pemupukan.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-1.1 | Admin dapat login dengan username + password yang valid → diarahkan ke dashboard |
| AC-1.2 | Credential tidak valid → pesan error, kembali ke halaman login |
| AC-1.3 | Opsi "Remember Me" mempertahankan sesi lebih lama |
| AC-1.4 | Akses route protected tanpa login → redirect ke halaman login |
| AC-1.5 | Logout menghapus sesi dan redirect ke login |
| AC-1.6 | Guard yang digunakan: `admin` (bukan default `web`) |

---

### 6.2 Dashboard WebGIS (Peta Interaktif)

**User Story:** Sebagai admin, saya ingin melihat visualisasi spasial seluruh blok lahan dalam peta interaktif, agar saya dapat memantau status pemupukan secara cepat dan menyeluruh.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-2.1 | Peta Leaflet.js menampilkan poligon GeoJSON per blok lahan |
| AC-2.2 | Warna poligon berdasarkan status RBS: merah=Darurat, oranye=Segera, hijau=Normal, abu=Tunda, gelap=Belum Dianalisis |
| AC-2.3 | Klik poligon → popup: nama blok, pemilik, masalah RBS, rekomendasi pupuk, saran, link detail |
| AC-2.4 | Stats cards: total blok, total luas, sudah dianalisis SPK, segera pupuk SPK, RBS Darurat, RBS Segera |
| AC-2.5 | Filter dropdown pemilik lahan → hanya tampil poligon pemilik tersebut |
| AC-2.6 | Layer switching: OSM ↔ ESRI Satellite |

---

### 6.3 Manajemen Blok Lahan

**User Story:** Sebagai admin, saya ingin mengelola data blok lahan (CRUD), agar seluruh area kebun terdokumentasi untuk analisis.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-3.1 | CRUD lengkap: tambah, lihat, edit, hapus blok lahan |
| AC-3.2 | Field wajib: nama_blok, nama_pemilik, luas_ha, sph, koordinat_geojson |
| AC-3.3 | Field opsional: total_tonase_panen → yield_per_hektar dihitung otomatis (tonase/luas) |
| AC-3.4 | Validasi GeoJSON format sebelum simpan |
| AC-3.5 | Hapus blok → cascade hapus kriteria, kondisi, dan rekomendasi terkait |
| AC-3.6 | Halaman detail: info lahan, kriteria, rekomendasi SPK, riwayat SPK, hasil RBS |

---

### 6.4 Manajemen Kriteria Lahan

**User Story:** Sebagai admin, saya ingin menginput data kriteria agronomis (tahun tanam, jenis tanah, topografi) per blok, agar Forward Chaining dapat berjalan.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-4.1 | Setiap blok hanya punya maksimal 1 kriteria (one-to-one) |
| AC-4.2 | 10 pilihan jenis tanah: Tanah Lempung, Tanah Lempung Berpasir, Tanah Berpasir, Tanah Liat, Tanah Gambut, Tanah Aluvial, Tanah PMK, Tanah Laterit, Tanah Berbatu, Lainnya |
| AC-4.3 | 3 pilihan topografi: Datar 0-15°, Bergelombang 15-30°, Curam >30° |
| AC-4.4 | Umur tanaman dihitung dinamis: tahun_sekarang − tahun_tanam |
| AC-4.5 | Kategori umur otomatis: <3=Belum Menghasilkan, 3–8=Remaja, 9–14=Menghasilkan Muda, 15–25=Menghasilkan Tua, >25=Tua Renta |

---

### 6.5 Manajemen Kondisi Lahan (Input RBS)

**User Story:** Sebagai admin, saya ingin menginput data observasi kondisi lahan secara periodik, agar RBS dapat mengevaluasi masalah dan memberikan rekomendasi kontekstual.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-5.1 | Form wizard 5 seksi: Identifikasi Blok, Kondisi Tanah, Kondisi Iklim, Gejala Visual, Catatan |
| AC-5.2 | Parameter tanah: pH (3.0–8.0), kelembaban (5 pilihan), drainase (3 pilihan) |
| AC-5.3 | Parameter iklim: musim (Hujan/Kemarau/Peralihan), curah hujan (5 kategori) |
| AC-5.4 | Gejala visual: warna daun (8 pilihan), kondisi pelepah (4 pilihan), gejala defisiensi (multi-select: N,P,K,Mg,B,Fe,Zn), kondisi tandan (5 pilihan) |
| AC-5.5 | Kondisi fisik: gulma dominan (boolean), serangan hama (boolean) |
| AC-5.6 | Gejala defisiensi disimpan sebagai JSON array |
| AC-5.7 | Satu blok bisa punya banyak observasi; RBS menggunakan yang terbaru (latestOfMany) |

---

### 6.6 Rule Base SPK (Forward Chaining)

**User Story:** Sebagai admin, saya ingin mengelola tabel aturan Forward Chaining, agar dosis pupuk sesuai pedoman PPKS.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-6.1 | Format key: "Kategori_Umur\|Jenis_Tanah\|Topografi" |
| AC-6.2 | 150 kombinasi: 5 kategori umur × 10 jenis tanah × 3 topografi |
| AC-6.3 | Output per rule: takaran_urea (kg/pokok), takaran_kcl (kg/pokok), status_pemupukan |
| AC-6.4 | 3 status: Segera Pupuk, Pemupukan Normal, Tunda Pemupukan |
| AC-6.5 | Formula dosis: base_dosis × multiplier_tanah × multiplier_topografi, dibulatkan ke 0.25 terdekat |

#### Formula Dosis SPK

**Base Dosis per Kategori Umur:**

| Kategori | Urea (kg/pokok) | KCl (kg/pokok) |
|----------|:---------------:|:--------------:|
| Belum Menghasilkan | 0.50 | 0.50 |
| Remaja | 1.50 | 1.00 |
| Menghasilkan Muda | 2.25 | 1.75 |
| Menghasilkan Tua | 2.75 | 2.25 |
| Tua Renta | 1.50 | 1.50 |

**Multiplier Jenis Tanah (contoh):**

| Jenis Tanah | Urea | KCl |
|-------------|:----:|:---:|
| Tanah Lempung | 1.0 | 1.0 |
| Tanah Berpasir | 1.3 | 1.4 |
| Tanah Gambut | 0.6 | 1.5 |
| Tanah Liat | 0.9 | 0.9 |

**Multiplier Topografi:**

| Topografi | Urea | KCl |
|-----------|:----:|:---:|
| Datar 0-15° | 1.0 | 1.0 |
| Bergelombang 15-30° | 1.1 | 1.1 |
| Curam >30° | 1.2 | 1.2 |

---

### 6.7 Analisis SPK (Forward Chaining)

**User Story:** Sebagai admin, saya ingin menjalankan analisis Forward Chaining per blok atau semua sekaligus, agar sistem menghasilkan rekomendasi dosis pupuk.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-7.1 | Alur: ambil kriteria → hitung umur → kategori umur → pattern matching → hitung dosis total |
| AC-7.2 | Total kebutuhan: total_urea = dosis × SPH × luas_ha |
| AC-7.3 | Kebutuhan karung: ceiling(total_kg / 50) |
| AC-7.4 | Simpan dengan updateOrCreate per blok (1 rekomendasi aktif per blok) |
| AC-7.5 | "Analisis Semua": batch processing semua blok yang punya kriteria |
| AC-7.6 | Error jika blok belum punya kriteria atau rule tidak ditemukan |

#### Alur Forward Chaining

```
┌────────────────┐    ┌───────────────────┐    ┌──────────────────┐
│ Data Kriteria  │───▶│ Penentuan Fakta   │───▶│ Pattern Matching │
│ • tahun_tanam  │    │ • umur = now-tanam│    │ key = umur|tanah │
│ • jenis_tanah  │    │ • kategori_umur   │    │       |topografi │
│ • topografi    │    │                   │    │                  │
└────────────────┘    └───────────────────┘    └────────┬─────────┘
                                                         │
                      ┌───────────────────┐    ┌────────▼─────────┐
                      │  Simpan Konklusi  │◀───│  Eksekusi (Fire) │
                      │ • rekomendasi_spks│    │ • dosis × SPH    │
                      │ • updateOrCreate  │    │ • × luas_ha      │
                      └───────────────────┘    └──────────────────┘
```

---

### 6.8 Rule Base Lanjutan (RBS)

**User Story:** Sebagai admin, saya ingin mengelola aturan RBS berdasarkan gejala visual dan kondisi lingkungan, agar rekomendasi kontekstual dapat disesuaikan.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-8.1 | Multi-kondisi input (IF): warna_daun, pH range, kelembaban, musim, drainase, defisiensi, kategori_umur, pelepah, tandan, serangan_hama |
| AC-8.2 | Output (THEN): indikasi_masalah, jenis_pupuk_utama/pendukung, dosis_anjuran, metode_aplikasi, waktu_aplikasi, saran_tindakan |
| AC-8.3 | 4 level status: Darurat, Segera, Normal, Tunda |
| AC-8.4 | Prioritas: 1 (tertinggi) – 10 (terendah) |
| AC-8.5 | Field `aktif` (boolean) untuk enable/disable rule tanpa hapus |
| AC-8.6 | Kondisi NULL = diabaikan saat evaluasi (wildcard) |
| AC-8.7 | ~25 rules bawaan mencakup: defisiensi N/P/K/Mg/B/Fe/Zn, pH masam, drainase buruk, kemarau, tanaman muda/tua, serangan hama |

---

### 6.9 Analisis RBS (Rule-Based System)

**User Story:** Sebagai admin, saya ingin menjalankan analisis RBS per blok atau semua sekaligus, agar sistem mengevaluasi kondisi terkini dan memberikan rekomendasi berbasis gejala.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-9.1 | Ambil kondisi lahan terbaru → evaluasi setiap rule aktif (urut prioritas) |
| AC-9.2 | Logika AND: semua kondisi non-NULL di rule harus terpenuhi agar rule terpicu |
| AC-9.3 | pH: range check (min ≤ pH ≤ max) |
| AC-9.4 | Defisiensi: array contains check |
| AC-9.5 | Status dominan: Darurat(4) > Segera(3) > Normal(2) > Tunda(1) |
| AC-9.6 | Output: masalah unik, pupuk deduplicate by jenis_utama, saran 3 prioritas tertinggi |
| AC-9.7 | Simpan updateOrCreate per blok (1 rekomendasi RBS aktif per blok) |
| AC-9.8 | Tidak ada rule terpicu → status Normal, saran "Lanjutkan pemupukan standar" |
| AC-9.9 | Error jika belum ada data kondisi lahan |

#### Alur RBS

```
┌──────────────────┐    ┌──────────────────────┐    ┌──────────────────┐
│ Kondisi Lahan    │───▶│ Evaluasi Rule (AND)  │───▶│ Susun Output     │
│ • pH tanah       │    │ Untuk setiap rule:   │    │ • masalah unik   │
│ • warna daun     │    │ IF semua kondisi     │    │ • pupuk dedup    │
│ • kelembaban     │    │    non-NULL cocok    │    │ • saran top 3    │
│ • defisiensi []  │    │ THEN rule terpicu    │    │ • status dominan │
│ • musim          │    │                      │    │                  │
│ • pelepah, tandan│    └──────────────────────┘    └────────┬─────────┘
│ • drainase       │                                          │
│ • hama           │    ┌──────────────────────┐    ┌────────▼─────────┐
└──────────────────┘    │  Simpan Konklusi     │◀───│ Tentukan Status  │
                        │ • rekomendasi_rbs    │    │ Darurat > Segera │
                        │ • updateOrCreate     │    │ > Normal > Tunda │
                        └──────────────────────┘    └──────────────────┘
```

---

### 6.10 Independensi SPK dan RBS

**User Story:** Sebagai admin, saya ingin hasil SPK dan RBS berjalan independen tanpa saling menimpa, agar saya mendapat dua perspektif rekomendasi.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-10.1 | SPK dan RBS berjalan paralel, tidak saling overwrite |
| AC-10.2 | Disimpan di tabel terpisah: `rekomendasi_spks` dan `rekomendasi_rbs` |
| AC-10.3 | Kedua hasil ditampilkan bersamaan di halaman detail blok lahan |

---

### 6.11 Laporan & Rekap

**User Story:** Sebagai admin, saya ingin melihat rekap kebutuhan pupuk total, agar saya dapat merencanakan pengadaan pupuk.

#### Acceptance Criteria

| # | Kriteria |
|---|----------|
| AC-11.1 | Daftar seluruh rekomendasi SPK dengan detail per blok |
| AC-11.2 | Summary: total Urea (kg + karung), total KCl (kg + karung) |
| AC-11.3 | Filter by status pemupukan |
| AC-11.4 | Filter by nama pemilik lahan |
| AC-11.5 | Detail per rekomendasi: blok, kriteria, parameter rule, dosis, total, status |

---

## 7. Struktur Database

### 7.1 Tabel `admins`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| username | varchar(50) | Unique |
| password | varchar | Hashed (bcrypt) |
| nama_lengkap | varchar(100) | |
| timestamps | | created_at, updated_at |

### 7.2 Tabel `blok_lahans`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| nama_blok | varchar(100) | |
| nama_pemilik | varchar(100) | |
| luas_ha | double | Hektar |
| sph | integer | Standar Pohon/Ha |
| koordinat_geojson | longtext | Format GeoJSON Polygon |
| total_tonase_panen | decimal(10,2) nullable | Ton |
| yield_per_hektar | decimal(10,2) nullable | Ton/Ha (auto-hitung) |
| timestamps | | |

### 7.3 Tabel `kriteria_lahans`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE DELETE |
| tahun_tanam | integer | |
| jenis_tanah | varchar(255) | 10 pilihan |
| topografi | enum | Datar/Bergelombang/Curam |
| timestamps | | |

### 7.4 Tabel `rule_bases`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| parameter_kondisi | varchar(255) | Format: "Umur\|Tanah\|Topografi" |
| takaran_urea | double | kg/pokok |
| takaran_kcl | double | kg/pokok |
| status_pemupukan | varchar(100) | Segera/Normal/Tunda |
| timestamps | | |

### 7.5 Tabel `rekomendasi_spks`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE DELETE |
| admin_id | FK → admins | CASCADE DELETE |
| tanggal_analisis | date | |
| dosis_urea | double | kg/pokok |
| dosis_kcl | double | kg/pokok |
| total_urea | double | kg total blok |
| total_kcl | double | kg total blok |
| status_akhir | varchar(100) | |
| timestamps | | |

### 7.6 Tabel `kondisi_lahans`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE DELETE |
| tanggal_observasi | date | |
| ph_tanah | decimal(4,2) nullable | 3.0 – 8.0 |
| kelembaban_tanah | enum nullable | 5 pilihan |
| curah_hujan_kategori | enum nullable | 5 pilihan |
| musim_saat_ini | enum nullable | Hujan/Kemarau/Peralihan |
| warna_daun | enum nullable | 8 pilihan |
| kondisi_pelepah | enum nullable | 4 pilihan |
| gejala_defisiensi | JSON nullable | Array: ['N','P','K',...] |
| kondisi_tandan | enum nullable | 5 pilihan |
| kondisi_drainase | enum nullable | 3 pilihan |
| ada_gulma_dominan | boolean | Default false |
| ada_serangan_hama | boolean | Default false |
| catatan_observasi | text nullable | |
| timestamps | | |

### 7.7 Tabel `rule_bases_lanjutan`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| kondisi_warna_daun | varchar(100) nullable | NULL = diabaikan |
| kondisi_ph_min | decimal(4,2) nullable | Batas bawah pH |
| kondisi_ph_max | decimal(4,2) nullable | Batas atas pH |
| kondisi_kelembaban | varchar(50) nullable | |
| kondisi_musim | varchar(50) nullable | |
| kondisi_drainase | varchar(50) nullable | |
| kondisi_defisiensi | varchar(50) nullable | Satu unsur target |
| kondisi_kategori_umur | varchar(50) nullable | |
| kondisi_pelepah | varchar(100) nullable | |
| kondisi_tandan | varchar(100) nullable | |
| ada_serangan_hama | boolean nullable | NULL=tidak cek, true=harus ada |
| indikasi_masalah | varchar(255) | Label masalah |
| jenis_pupuk_utama | varchar(100) | |
| jenis_pupuk_pendukung | varchar(100) nullable | |
| dosis_anjuran | varchar(150) | |
| metode_aplikasi | varchar(255) nullable | |
| waktu_aplikasi | varchar(150) nullable | |
| saran_tindakan | text | |
| status_kebutuhan | enum | Darurat/Segera/Normal/Tunda |
| prioritas | tinyint unsigned | 1–10 (1=tertinggi) |
| aktif | boolean | Default true |
| keterangan_rule | text nullable | |
| timestamps | | |

### 7.8 Tabel `rekomendasi_rbs`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| blok_lahan_id | FK → blok_lahans | CASCADE DELETE |
| kondisi_lahan_id | FK → kondisi_lahans | CASCADE DELETE |
| admin_id | FK → admins | CASCADE DELETE |
| tanggal_analisis | date | |
| rules_terpicu | JSON | [{rule_id, indikasi, pupuk, status, prioritas}] |
| masalah_teridentifikasi | JSON | Array string masalah |
| rekomendasi_pupuk | JSON | [{jenis_utama, dosis, metode, waktu}] |
| saran_tindakan_utama | text | |
| status_kebutuhan_dominan | enum | Darurat/Segera/Normal/Tunda |
| jumlah_rule_terpicu | tinyint unsigned | |
| timestamps | | |

---

## 8. Relasi Antar Entitas

| Model | Relasi | Target |
|-------|--------|--------|
| Admin | hasMany | RekomendasiSpk, RekomendasiRbs |
| BlokLahan | hasOne | KriteriaLahan |
| BlokLahan | hasMany | KondisiLahan, RekomendasiSpk, RekomendasiRbs |
| BlokLahan | hasOne (latestOfMany) | rekomendasiTerbaru, kondisiTerbaru, rekomendasiRbsTerbaru |
| KriteriaLahan | belongsTo | BlokLahan |
| KondisiLahan | belongsTo | BlokLahan |
| KondisiLahan | hasMany | RekomendasiRbs |
| RekomendasiSpk | belongsTo | BlokLahan, Admin |
| RekomendasiRbs | belongsTo | BlokLahan, KondisiLahan, Admin |

---

## 9. Routing

| Method | URI | Nama | Controller |
|--------|-----|------|------------|
| GET | / | — | Redirect → dashboard |
| GET | /login | login | AuthController@showLoginForm |
| POST | /login | login.submit | AuthController@login |
| POST | /logout | logout | AuthController@logout |
| GET | /dashboard | dashboard | DashboardController@index |
| RESOURCE | /blok-lahan | blok-lahan.* | BlokLahanController |
| RESOURCE | /kriteria-lahan | kriteria-lahan.* | KriteriaLahanController (no show) |
| RESOURCE | /kondisi-lahan | kondisi-lahan.* | KondisiLahanController (no show) |
| RESOURCE | /rule-base | rule-base.* | RuleBaseController (no show) |
| GET | /spk | spk.index | SpkController@index |
| POST | /spk/analisis/{blok} | spk.analisis | SpkController@analisis |
| POST | /spk/analisis-semua | spk.analisis-semua | SpkController@analisisSemua |
| GET | /spk/detail/{blok} | spk.detail | SpkController@detail |
| GET | /rbs | rbs.index | RbsController@index |
| POST | /rbs/analisis/{blok} | rbs.analisis | RbsController@analisis |
| POST | /rbs/analisis-semua | rbs.analisisSemua | RbsController@analisisSemua |
| GET | /rbs/detail/{blok} | rbs.detail | RbsController@detail |
| GET | /laporan | laporan.index | LaporanController@index |
| GET | /laporan/{rek} | laporan.show | LaporanController@show |
| GET | /api/rbs-popup/{blok} | api.rbs.popup | RbsController@apiPopup |

---

## 10. Navigasi Sidebar

| Seksi | Menu Item | Route |
|-------|-----------|-------|
| Utama | Peta Lahan (WebGIS) | dashboard |
| Data Master | Manajemen Blok Lahan | blok-lahan.index |
| Data Master | Kriteria Lahan | kriteria-lahan.index |
| Data Master | Kondisi Lahan | kondisi-lahan.index |
| Data Master | Rule Base SPK | rule-base.index |
| Analisis | Analisis SPK | spk.index |
| Analisis | Analisis RBS | rbs.index |
| Analisis | Laporan & Rekap | laporan.index |

---

## 11. Batasan & Constraint

| # | Batasan |
|---|---------|
| 1 | Field `total_tonase_panen` dan `yield_per_hektar` BUKAN input untuk RBS |
| 2 | RBS fokus pada: gejala visual, pH tanah, kelembaban, musim, drainase, hama |
| 3 | SPK dan RBS berjalan paralel, tidak saling overwrite |
| 4 | Setiap blok: 1 rekomendasi SPK aktif + 1 rekomendasi RBS aktif (updateOrCreate) |
| 5 | Histori tidak disimpan — setiap analisis baru menimpa yang lama |
| 6 | Single-tenant: 1 kelompok tani, 1 admin |
| 7 | Semua field kondisi di rule RBS bersifat nullable (NULL = tidak dicek) |

---

## 12. Seeder & Data Awal

| Seeder | Data |
|--------|------|
| AdminSeeder | 1 admin default |
| RuleBaseSeeder | 150 rules Forward Chaining (5 umur × 10 tanah × 3 topografi) |
| RuleBaseLanjutanSeeder | 25 rules RBS (defisiensi N/P/K/Mg/B/Fe/Zn, pH masam, drainase buruk, kemarau, tanaman muda/tua, hama, pelepah, tandan) |

---

## 13. Non-Functional Requirements

| # | Requirement |
|---|-------------|
| NFR-1 | Response time dashboard WebGIS < 3 detik (termasuk render peta) |
| NFR-2 | Eager loading di semua controller untuk mencegah N+1 query |
| NFR-3 | Responsive design: desktop + tablet + mobile (Tailwind CSS breakpoints) |
| NFR-4 | Validasi input di server-side pada semua form |
| NFR-5 | Flash message (success/error/warning) untuk setiap aksi pengguna |
| NFR-6 | Pagination 15 item per halaman pada daftar data banyak |
| NFR-7 | Password admin di-hash dengan bcrypt (12 rounds) |

---

## 14. Flow Aplikasi

### 14.1 Flow Utama Pengguna (User Journey)

```
┌──────────┐     ┌────────────┐     ┌──────────────────────────────────────┐
│  Login   │────▶│ Dashboard  │────▶│  Pilih Aksi:                         │
│          │     │  (WebGIS)  │     │  • Kelola Blok Lahan                 │
└──────────┘     └────────────┘     │  • Input Kriteria Lahan              │
                                    │  • Input Kondisi Lahan               │
                                    │  • Jalankan Analisis SPK             │
                                    │  • Jalankan Analisis RBS             │
                                    │  • Lihat Laporan                     │
                                    └──────────────────────────────────────┘
```

### 14.2 Flow Lengkap: Dari Input Data Hingga Rekomendasi

```
                          ┌─────────────────────────┐
                          │   1. INPUT BLOK LAHAN   │
                          │  nama, luas, sph, peta  │
                          └────────────┬────────────┘
                                       │
                    ┌──────────────────┼──────────────────┐
                    ▼                                      ▼
     ┌──────────────────────────┐          ┌──────────────────────────┐
     │  2A. INPUT KRITERIA      │          │  2B. INPUT KONDISI       │
     │  • Tahun tanam           │          │  • pH tanah              │
     │  • Jenis tanah           │          │  • Warna daun            │
     │  • Topografi             │          │  • Kelembaban            │
     │                          │          │  • Defisiensi            │
     │  (1x per blok)          │          │  • Musim, drainase, dll  │
     │                          │          │  (periodik/berkala)      │
     └────────────┬─────────────┘          └────────────┬─────────────┘
                  │                                      │
                  ▼                                      ▼
     ┌──────────────────────────┐          ┌──────────────────────────┐
     │  3A. ANALISIS SPK        │          │  3B. ANALISIS RBS        │
     │  (Forward Chaining)      │          │  (Rule-Based System)     │
     └────────────┬─────────────┘          └────────────┬─────────────┘
                  │                                      │
                  ▼                                      ▼
     ┌──────────────────────────┐          ┌──────────────────────────┐
     │  4A. REKOMENDASI SPK     │          │  4B. REKOMENDASI RBS     │
     │  • Dosis Urea/KCl       │          │  • Masalah teridentifikasi│
     │  • Total kebutuhan kg    │          │  • Pupuk spesifik        │
     │  • Jumlah karung         │          │  • Dosis & metode        │
     │  • Status pemupukan      │          │  • Saran tindakan        │
     └────────────┬─────────────┘          └────────────┬─────────────┘
                  │                                      │
                  └──────────────────┬───────────────────┘
                                     ▼
                          ┌──────────────────────┐
                          │  5. VISUALISASI      │
                          │  • Peta WebGIS       │
                          │  • Detail Blok Lahan │
                          │  • Laporan Rekap     │
                          └──────────────────────┘
```

### 14.3 Flow Detail: Analisis SPK (Forward Chaining)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     FORWARD CHAINING ENGINE                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  STEP 1: PENGAMBILAN FAKTA                                              │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ BlokLahan → KriteriaLahan                         │                  │
│  │   • tahun_tanam = 2015                            │                  │
│  │   • jenis_tanah = "Tanah Lempung"                 │                  │
│  │   • topografi = "Datar 0-15°"                     │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 2: PENENTUAN KATEGORI UMUR                                        │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ umur = 2026 − 2015 = 11 tahun                    │                  │
│  │ kategori = "Menghasilkan Muda" (9–14 thn)        │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 3: PATTERN MATCHING                                               │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ key = "Menghasilkan Muda|Tanah Lempung|Datar 0-15°"│                 │
│  │ SELECT * FROM rule_bases                           │                  │
│  │ WHERE parameter_kondisi = key                     │                  │
│  │                                                   │                  │
│  │ HASIL: takaran_urea=2.25, takaran_kcl=1.75       │                  │
│  │         status_pemupukan="Pemupukan Normal"       │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 4: KALKULASI LOGISTIK                                             │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ SPH = 136, Luas = 5.0 Ha                         │                  │
│  │                                                   │                  │
│  │ total_urea = 2.25 × 136 × 5.0 = 1,530 kg        │                  │
│  │ total_kcl  = 1.75 × 136 × 5.0 = 1,190 kg        │                  │
│  │                                                   │                  │
│  │ karung_urea = ⌈1530/50⌉ = 31 karung              │                  │
│  │ karung_kcl  = ⌈1190/50⌉ = 24 karung              │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 5: SIMPAN KONKLUSI                                                │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ rekomendasi_spks::updateOrCreate(blok_lahan_id)   │                  │
│  │   dosis_urea = 2.25                               │                  │
│  │   dosis_kcl = 1.75                                │                  │
│  │   total_urea = 1530                               │                  │
│  │   total_kcl = 1190                                │                  │
│  │   status_akhir = "Pemupukan Normal"               │                  │
│  └───────────────────────────────────────────────────┘                  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 14.4 Flow Detail: Analisis RBS (Rule-Based System)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     RULE-BASED SYSTEM ENGINE                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  STEP 1: AMBIL DATA KONDISI TERBARU                                     │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ BlokLahan → KondisiTerbaru (latestOfMany)         │                  │
│  │   • tanggal = 2026-06-04                          │                  │
│  │   • ph_tanah = 4.2                                │                  │
│  │   • warna_daun = "Kuning Merata"                  │                  │
│  │   • kelembaban = "Normal"                         │                  │
│  │   • musim = "Musim Hujan"                         │                  │
│  │   • gejala_defisiensi = ["N"]                     │                  │
│  │   • drainase = "Baik"                             │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 2: AMBIL KATEGORI UMUR (dari kriteria)                            │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ KriteriaLahan → kategori_umur = "Menghasilkan Muda"│                 │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 3: EVALUASI SELURUH RULE (AND Logic per Rule)                     │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ Rule #7: pH_min=3.0, pH_max=4.5                   │                  │
│  │   ✓ 3.0 ≤ 4.2 ≤ 4.5 → COCOK                     │                  │
│  │   → TERPICU: "pH Sangat Masam"                    │                  │
│  │                                                   │                  │
│  │ Rule #1: warna_daun="Kuning Merata", defisiensi="N"│                 │
│  │   ✓ warna_daun == "Kuning Merata" → cocok         │                  │
│  │   ✓ "N" in ["N"] → cocok                          │                  │
│  │   → TERPICU: "Defisiensi Nitrogen"                │                  │
│  │                                                   │                  │
│  │ Rule #16: musim="Musim Hujan", kelembaban="Normal"│                  │
│  │   ✓ musim cocok                                   │                  │
│  │   ✓ kelembaban cocok                              │                  │
│  │   → TERPICU: "Kondisi Optimal Pemupukan"          │                  │
│  │                                                   │                  │
│  │ Rule #3: warna_daun="Oranye/Kemerahan"            │                  │
│  │   ✗ "Kuning Merata" ≠ "Oranye/Kemerahan"         │                  │
│  │   → TIDAK COCOK                                   │                  │
│  │                                                   │                  │
│  │ ... (evaluasi 25 rules)                           │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 4: TENTUKAN STATUS DOMINAN                                        │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ Rules terpicu:                                    │                  │
│  │   #7  → Darurat (hierarki=4, prioritas=1)        │                  │
│  │   #1  → Segera  (hierarki=3, prioritas=2)        │                  │
│  │   #16 → Normal  (hierarki=2, prioritas=6)        │                  │
│  │                                                   │                  │
│  │ Status dominan = MAX(hierarki) = Darurat          │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 5: SUSUN OUTPUT                                                   │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ masalah = [                                       │                  │
│  │   "pH Sangat Masam — Penghambatan Penyerapan",    │                  │
│  │   "Defisiensi Nitrogen — Klorosis Umum",          │                  │
│  │   "Kondisi Optimal untuk Pemupukan"               │                  │
│  │ ]                                                 │                  │
│  │                                                   │                  │
│  │ rekomendasi_pupuk = [                             │                  │
│  │   {utama: "Dolomit", dosis: "500–1000 kg/Ha"},   │                  │
│  │   {utama: "Urea (46% N)", dosis: "1.5–2.0 kg/pk"}│                  │
│  │   {utama: "Urea+KCl (dosis penuh)", ...}         │                  │
│  │ ]                                                 │                  │
│  │                                                   │                  │
│  │ saran = gabungan 3 rule prioritas tertinggi       │                  │
│  │ jumlah_rule_terpicu = 3                           │                  │
│  └───────────────────────────────────────────────────┘                  │
│                           │                                             │
│                           ▼                                             │
│  STEP 6: SIMPAN KONKLUSI                                                │
│  ┌───────────────────────────────────────────────────┐                  │
│  │ rekomendasi_rbs::updateOrCreate(blok_lahan_id)    │                  │
│  │   status_kebutuhan_dominan = "Darurat"            │                  │
│  │   jumlah_rule_terpicu = 3                         │                  │
│  └───────────────────────────────────────────────────┘                  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 14.5 Flow CRUD Blok Lahan → Analisis → Hasil

```
┌─────────┐   ┌──────────────┐   ┌───────────────┐   ┌──────────────────┐
│ Tambah  │──▶│ Input Data   │──▶│ Simpan ke DB  │──▶│ Tampil di Tabel  │
│ Blok    │   │ Blok Lahan   │   │ + validasi    │   │ & Peta WebGIS    │
└─────────┘   └──────────────┘   └───────────────┘   └────────┬─────────┘
                                                                │
              ┌──────────────────────────────────────────────────┘
              ▼
┌─────────────────┐   ┌─────────────────┐   ┌─────────────────────────┐
│ Tambah Kriteria │──▶│ Tambah Kondisi  │──▶│ Jalankan Analisis       │
│ (1x per blok)   │   │ (berkala)       │   │ • SPK: per blok / semua │
└─────────────────┘   └─────────────────┘   │ • RBS: per blok / semua │
                                             └────────────┬────────────┘
                                                          │
                                                          ▼
                                             ┌────────────────────────┐
                                             │ Hasil Ditampilkan:     │
                                             │ • Dashboard Peta       │
                                             │ • Detail Blok Lahan    │
                                             │ • Halaman Analisis     │
                                             │ • Laporan & Rekap      │
                                             └────────────────────────┘
```

### 14.6 Flow Login & Proteksi Akses

```
┌──────────────────┐          ┌──────────────────┐
│ User Buka URL    │─────────▶│ Route Protected? │
└──────────────────┘          └────────┬─────────┘
                                       │
                              ┌────────┴────────┐
                              ▼                  ▼
                         [YA]                 [TIDAK]
                              │                  │
                              ▼                  ▼
                    ┌──────────────┐    ┌──────────────┐
                    │ Cek Sesi     │    │ Tampilkan    │
                    │ Admin Auth?  │    │ Halaman      │
                    └──────┬───────┘    └──────────────┘
                           │
                  ┌────────┴────────┐
                  ▼                  ▼
             [SUDAH LOGIN]      [BELUM LOGIN]
                  │                  │
                  ▼                  ▼
         ┌──────────────┐   ┌──────────────┐
         │ Akses Halaman│   │ Redirect ke  │
         │ yang Diminta │   │ /login       │
         └──────────────┘   └──────┬───────┘
                                    │
                                    ▼
                           ┌──────────────────┐
                           │ Form Login       │
                           │ username + pass  │
                           └────────┬─────────┘
                                    │
                           ┌────────┴────────┐
                           ▼                  ▼
                      [VALID]            [INVALID]
                           │                  │
                           ▼                  ▼
                  ┌──────────────┐   ┌──────────────┐
                  │ Regenerate   │   │ Flash Error  │
                  │ Session      │   │ "Username/   │
                  │ Redirect ke  │   │  password    │
                  │ Dashboard    │   │  salah"      │
                  └──────────────┘   └──────────────┘
```

### 14.7 Flow Peta WebGIS (Dashboard)

```
┌─────────────────────────────────────────────────────────────────┐
│                      DASHBOARD WEBGIS                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. Controller Load Data                                         │
│     BlokLahan::with([                                            │
│        'rekomendasiTerbaru',                                     │
│        'kriteriaLahan',                                          │
│        'rekomendasiRbsTerbaru',                                  │
│        'kondisiTerbaru'                                          │
│     ])->get()                                                    │
│                          │                                       │
│                          ▼                                       │
│  2. Transform ke mapData JSON                                    │
│     Setiap blok: {                                               │
│        id, nama_blok, geojson,                                   │
│        status_rbs, masalah_rbs,                                  │
│        pupuk_rbs, saran_rbs, ...                                 │
│     }                                                            │
│                          │                                       │
│                          ▼                                       │
│  3. Render Leaflet Map                                           │
│     ┌────────────────────────────────────────────┐               │
│     │  Untuk setiap blok di mapData:             │               │
│     │    • Buat poligon GeoJSON                  │               │
│     │    • Set warna berdasarkan status_rbs      │               │
│     │    • Bind popup dengan data RBS            │               │
│     │    • Hover effect (opacity)                │               │
│     └────────────────────────────────────────────┘               │
│                          │                                       │
│                          ▼                                       │
│  4. Interaksi User                                               │
│     • Filter pemilik → re-render layers                          │
│     • Klik poligon → popup detail                                │
│     • Switch layer → OSM / Satelit                               │
│     • "Detail RBS →" link di popup                               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 14.8 Flow Analisis Batch ("Analisis Semua")

```
┌───────────────┐     ┌─────────────────────────────┐
│ Admin Klik    │────▶│ Ambil Semua Blok yang       │
│ "Analisis     │     │ memenuhi syarat:            │
│  Semua"       │     │ • SPK: punya kriteria_lahan │
│               │     │ • RBS: punya kondisi_lahan  │
└───────────────┘     └──────────────┬──────────────┘
                                     │
                                     ▼
                      ┌────────────────────────────┐
                      │  LOOP: Untuk setiap blok   │
                      │  ┌──────────────────────┐  │
                      │  │ try {                │  │
                      │  │   analisis(blok)     │  │
                      │  │   results[] ← OK    │  │
                      │  │ } catch (Exception) {│  │
                      │  │   errors[] ← msg    │  │
                      │  │ }                    │  │
                      │  └──────────────────────┘  │
                      └──────────────┬─────────────┘
                                     │
                                     ▼
                      ┌────────────────────────────┐
                      │ Redirect + Flash Message:  │
                      │                            │
                      │ "Analisis selesai:         │
                      │  X blok berhasil.          │
                      │  Y blok gagal: [errors]"   │
                      │                            │
                      │ Flash type:                │
                      │  • success (jika 0 error)  │
                      │  • warning (jika ada error)│
                      └────────────────────────────┘
```

---

## 15. Changelog

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0 | 4 Juni 2026 | Initial PRD — dokumentasi seluruh fitur yang telah diimplementasikan |
| 1.1 | 4 Juni 2026 | Tambah Seksi 14: Flow Aplikasi (8 diagram flow) |
