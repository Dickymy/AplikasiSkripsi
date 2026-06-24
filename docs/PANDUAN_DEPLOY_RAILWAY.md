# Panduan Deploy ke Railway

## Stack: Laravel 11 + MySQL + Vite/Tailwind

---

## BAGIAN 1 — Persiapan di Komputer (lakukan sekali)

### 1.1 Install Git (jika belum)
Download dan install dari: https://git-scm.com/download/win

### 1.2 Buat akun GitHub
Daftar di: https://github.com (gratis)

### 1.3 Buat repository GitHub baru
1. Login GitHub → klik tombol **New** (hijau)
2. Nama repo: `sistem-pupuk-sawit` (atau bebas)
3. Pilih **Private** (agar kode tidak publik)
4. Klik **Create repository**
5. Catat URL repo, contoh: `https://github.com/namauser/sistem-pupuk-sawit`

### 1.4 Push project ke GitHub
Buka CMD/Terminal di folder project, jalankan:

```bash
git init
git add .
git commit -m "Initial commit — deploy ke Railway"
git branch -M main
git remote add origin https://github.com/NAMAUSER/NAMAREPO.git
git push -u origin main
```

> Jika diminta login GitHub, masukkan username dan password/token.
> Untuk personal access token: GitHub → Settings → Developer Settings → Personal Access Tokens

---

## BAGIAN 2 — Setup Railway

### 2.1 Daftar Railway
1. Buka: https://railway.app
2. Klik **Login** → pilih **Login with GitHub**
3. Authorize Railway untuk akses GitHub kamu

### 2.2 Buat Project Baru
1. Di dashboard Railway → klik **New Project**
2. Pilih **Deploy from GitHub repo**
3. Pilih repo `sistem-pupuk-sawit` yang tadi dibuat
4. Railway akan mulai scan project — **jangan panic**, biarkan dulu

### 2.3 Tambah Database MySQL
1. Di dalam project Railway → klik **+ New**
2. Pilih **Database** → pilih **MySQL**
3. Railway akan otomatis buat database MySQL dan muncul di sidebar
4. Klik MySQL yang baru dibuat → tab **Variables**
5. Catat nilai-nilai berikut:
   - `MYSQL_HOST`
   - `MYSQL_PORT` (biasanya 3306)
   - `MYSQL_DATABASE`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`

---

## BAGIAN 3 — Konfigurasi Environment Variables di Railway

### 3.1 Generate APP_KEY
Di komputer lokal, jalankan:
```bash
php artisan key:generate --show
```
Hasilnya berupa string panjang seperti: `base64:xxxxx...`
**Catat/copy string ini.**

### 3.2 Set Variables di Railway
Klik service Laravel kamu → tab **Variables** → klik **RAW Editor**
Paste semua variabel berikut (ganti nilai yang ada tanda `←`):

```
APP_NAME=Sistem Rekomendasi Pemupukan
APP_ENV=production
APP_KEY=base64:XXXXXXXX←ganti dengan hasil php artisan key:generate
APP_DEBUG=false
APP_TIMEZONE=Asia/Makassar
APP_URL=https://NAMAAPP.up.railway.app←Railway isi otomatis

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

> **Penting:** `${{MySQL.MYSQL_HOST}}` adalah sintaks Railway untuk mengambil
> variabel dari service MySQL secara otomatis — ketik persis seperti itu.

### 3.3 Set APP_URL
Setelah deploy selesai, Railway akan memberi domain.
Kembali ke Variables dan update:
```
APP_URL=https://namaapp.up.railway.app
```

---

## BAGIAN 4 — Trigger Deploy

1. Setelah variables diset → klik **Deploy** atau Railway otomatis redeploy
2. Klik tab **Deployments** untuk monitor progress
3. Klik deployment yang berjalan → lihat **logs** real-time

### Yang terjadi saat deploy (otomatis dari nixpacks.toml):
```
[1] composer install --no-dev --optimize-autoloader
[2] npm ci
[3] npm run build  (build Vite + Tailwind)
[4] php artisan config:cache
[5] php artisan route:cache
[6] php artisan view:cache
[7] php artisan migrate --force  (buat tabel di MySQL)
[8] php artisan db:seed --force  (isi admin + rule base)
[9] php -S 0.0.0.0:$PORT -t public  (jalankan server)
```

Deploy selesai biasanya **3–5 menit**.

---

## BAGIAN 5 — Setelah Deploy Berhasil

### 5.1 Akses aplikasi
Railway memberi URL seperti: `https://sistem-pupuk-sawit.up.railway.app`
Buka di browser → harusnya muncul halaman login.

### 5.2 Login pertama
```
Username: admin
Password: admin123
```
**Segera ganti password setelah login pertama!**

### 5.3 Custom Domain (opsional)
Jika ingin domain sendiri seperti `pupuksawit.com`:
1. Beli domain di Niagahoster (~Rp 150rb/tahun untuk .com)
2. Di Railway → Settings → Domains → Add Custom Domain
3. Ikuti instruksi DNS yang Railway berikan
4. Update `APP_URL` di Variables

---

## BAGIAN 6 — Cara Update Kode Setelah Perubahan

Setiap kali ada perubahan kode, cukup:
```bash
git add .
git commit -m "Deskripsi perubahan"
git push
```
Railway akan otomatis detect push dan redeploy. Sekitar 2–3 menit.

---

## TROUBLESHOOTING

### ❌ Deploy gagal — "Class not found"
```bash
# Jalankan lokal, commit hasilnya
composer dump-autoload
git add composer.lock
git commit -m "fix: update composer autoload"
git push
```

### ❌ Halaman muncul tapi styling rusak (CSS/JS hilang)
Pastikan `public/build` ada di `.gitignore` (sudah benar).
Railway harus menjalankan `npm run build`. Cek logs build.

### ❌ Error "SQLSTATE" atau database connection refused
Cek kembali variabel `DB_HOST`, `DB_PORT`, dll di Railway Variables.
Pastikan menggunakan syntax `${{MySQL.MYSQL_HOST}}` dengan benar.

### ❌ Error 500 setelah deploy
Sementara set `APP_DEBUG=true` di Variables → deploy ulang → lihat error
detail → setelah fix, kembalikan ke `APP_DEBUG=false`.

### ❌ Session/login tidak berfungsi
Pastikan `SESSION_DRIVER=database` dan tabel sessions sudah dibuat
(migrasi `create_cache_table` sudah include sessions).

---

## CATATAN PENTING UNTUK SKRIPSI

1. **Free tier Railway**: $5 credit/bulan — cukup untuk demo sidang
2. **Data tersimpan di MySQL Railway**: aman selama project aktif
3. **Jangan hapus project Railway** sebelum sidang selesai
4. **Backup data**: bisa export dari `/laporan` → PDF sebelum presentasi
5. **Kalau mau extend gratis**: Railway beri $5 free setiap bulan bagi akun yang verify dengan kartu kredit (tidak ditagih jika usage < $5)

