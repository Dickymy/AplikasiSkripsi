# PROJECT INDEX — SPK Pemupukan Kelapa Sawit

> **Framework:** Laravel 11 (PHP 8.2) + Tailwind CSS 4 + Vite + Leaflet.js  
> **Database:** SQLite  
> **Metode Analisis:** Rule-Based System (Forward Chaining)  
> **Tanggal Generate:** 23 Juni 2026

---

## DAFTAR ISI

1. [Struktur Proyek](#struktur-proyek)
2. [Konfigurasi & Dependensi](#konfigurasi--dependensi)
3. [Routes (web.php)](#routes-webphp)
4. [Middleware](#middleware)
5. [Models](#models)
6. [Controllers](#controllers)
7. [Services](#services)
8. [Database Migrations](#database-migrations)
9. [Database Seeders](#database-seeders)
10. [Views (Blade Templates)](#views-blade-templates)
11. [Config](#config)
12. [Frontend Assets](#frontend-assets)

---

## STRUKTUR PROYEK

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AnggotaController.php
│   │   │   ├── AuthController.php
│   │   │   ├── BlokLahanController.php
│   │   │   ├── Controller.php
│   │   │   ├── CuacaController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── KondisiLahanController.php
│   │   │   ├── LaporanController.php
│   │   │   ├── RbsController.php
│   │   │   └── RuleBaseController.php
│   │   └── Middleware/
│   │       └── AdminAuthenticated.php
│   ├── Models/
│   │   ├── Admin.php
│   │   ├── Anggota.php
│   │   ├── BlokLahan.php
│   │   ├── KondisiLahan.php
│   │   ├── RekomendasiRbs.php
│   │   └── RuleBaseLanjutan.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── RbsService.php
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── migrations/ (26 file)
│   └── seeders/
│       ├── AdminSeeder.php
│       ├── DatabaseSeeder.php
│       ├── RuleBaseLanjutanSeeder.php
│       └── RuleCurahHujanGulmaSeeder.php
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── overlap-detector.js
│   └── views/
│       ├── layouts/app.blade.php
│       ├── auth/login.blade.php
│       ├── dashboard/index.blade.php
│       ├── anggota/ (create, edit, index)
│       ├── blok_lahan/ (create, edit, index, show)
│       ├── kondisi_lahan/ (create, edit, index)
│       ├── rbs/ (index, detail, partials/_hasil_rbs)
│       ├── rule_base/ (create, edit, index, info)
│       ├── laporan/ (index, show, pdf)
│       ├── components/ (filter-searchable, searchable-select, status-badge)
│       └── panduan.blade.php
├── routes/
│   ├── web.php
│   └── console.php
├── composer.json
├── package.json
└── vite.config.js
```

---

## KONFIGURASI & DEPENDENSI

### composer.json (PHP Dependencies)
```json
{
    "require": {
        "php": "^8.2",
        "barryvdh/laravel-dompdf": "^3.1",
        "laravel/framework": "^11.0",
        "laravel/tinker": "^2.9"
    }
}
```

### package.json (Frontend Dependencies)
```json
{
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.11",
        "@tailwindcss/vite": "^4.3.0",
        "autoprefixer": "^10.5.0",
        "axios": "^1.6.4",
        "laravel-vite-plugin": "^1.0",
        "tailwindcss": "^4.3.0",
        "vite": "^5.0"
    },
    "dependencies": {
        "@turf/turf": "^7.3.5"
    }
}
```

### vite.config.js
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```
### bootstrap/app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\AdminAuthenticated::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### bootstrap/providers.php
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
];
```

---

## ROUTES (web.php)

```php
<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlokLahanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KondisiLahanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RbsController;
use App\Http\Controllers\RuleBaseController;
use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', fn() => redirect()->route('dashboard'));

// Authentication routes (guest only)
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes — requires admin authentication
Route::middleware(AdminAuthenticated::class)->group(function () {

    // Dashboard (WebGIS)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Anggota Kelompok Tani
    Route::resource('anggota', AnggotaController::class)->except(['show']);

    // Manajemen Blok Lahan (termasuk kriteria agronomis)
    Route::resource('blok-lahan', BlokLahanController::class);

    // Kondisi Lahan
    Route::resource('kondisi-lahan', KondisiLahanController::class)->except(['show']);

    // Rule Base RBS
    Route::get('rule-base/info', [RuleBaseController::class, 'info'])->name('rule-base.info');
    Route::resource('rule-base', RuleBaseController::class)->except(['show']);

    // Panduan Penggunaan
    Route::get('/panduan', function () {
        return view('panduan');
    })->name('panduan');

    // Analisis RBS (Rule-Based System)
    Route::prefix('rbs')->name('rbs.')->group(function () {
        Route::get('/', [RbsController::class, 'index'])->name('index');
        Route::get('/daftar-blok-belum-analisis', [RbsController::class, 'daftarBlokBelumAnalisis'])->name('daftarBlokBelumAnalisis');
        Route::post('/analisis/{blokLahan}', [RbsController::class, 'analisis'])->name('analisis');
        Route::post('/analisis-semua', [RbsController::class, 'analisisSemua'])->name('analisisSemua');
        Route::get('/detail/{blokLahan}', [RbsController::class, 'detail'])->name('detail');
    });

    // Laporan (berbasis rekomendasi RBS)
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/{rekomendasiRbs}/pdf', [LaporanController::class, 'exportPdf'])->name('pdf');
        Route::get('/{rekomendasiRbs}/ringkasan', [LaporanController::class, 'exportRingkasan'])->name('ringkasan');
        Route::get('/{rekomendasiRbs}', [LaporanController::class, 'show'])->name('show');
    });

    // API endpoint — RBS popup WebGIS
    Route::get('/api/rbs-popup/{blokLahan}', [RbsController::class, 'apiPopup'])->name('api.rbs.popup');

    // API endpoint — Cuaca otomatis dari Open-Meteo
    Route::post('/api/cuaca/fetch', [\App\Http\Controllers\CuacaController::class, 'fetch'])->name('api.cuaca.fetch');
});
```

---

## MIDDLEWARE

### AdminAuthenticated.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
```

---

## MODELS

### Admin.php
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
```

### Anggota.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
    ];

    public function blokLahans(): HasMany
    {
        return $this->hasMany(BlokLahan::class, 'anggota_id');
    }

    public function getJumlahBlokAttribute(): int
    {
        return $this->blokLahans()->count();
    }
}
```

### BlokLahan.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BlokLahan extends Model
{
    protected $fillable = [
        'anggota_id',
        'nama_blok',
        'luas_ha',
        'sph',
        'koordinat_geojson',
        'tahun_tanam',
        'jenis_tanah',
        'topografi',
    ];

    protected function casts(): array
    {
        return [
            'luas_ha'    => 'double',
            'sph'        => 'integer',
            'tahun_tanam' => 'integer',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function kondisiLahans(): HasMany
    {
        return $this->hasMany(KondisiLahan::class, 'blok_lahan_id');
    }

    public function kondisiTerbaru(): HasOne
    {
        return $this->hasOne(KondisiLahan::class, 'blok_lahan_id')->latestOfMany('tanggal_observasi');
    }

    public function rekomendasiRbs(): HasMany
    {
        return $this->hasMany(RekomendasiRbs::class, 'blok_lahan_id');
    }

    public function rekomendasiRbsTerbaru(): HasOne
    {
        return $this->hasOne(RekomendasiRbs::class, 'blok_lahan_id')
            ->where('is_latest', true)
            ->latestOfMany('tanggal_analisis');
    }

    public function getNamaPemilikAttribute(): string
    {
        return $this->anggota?->nama ?? '-';
    }

    public function getUmurTanamanAttribute(): ?int
    {
        return $this->tahun_tanam ? (now()->year - $this->tahun_tanam) : null;
    }

    public function getKategoriUmurAttribute(): ?string
    {
        $umur = $this->umur_tanaman;
        if ($umur === null) return null;

        if ($umur < 3) return 'Belum Menghasilkan';
        if ($umur <= 8) return 'Remaja';
        if ($umur <= 14) return 'Menghasilkan Muda';
        if ($umur <= 25) return 'Menghasilkan Tua';
        return 'Tua Renta';
    }
}
```

### KondisiLahan.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KondisiLahan extends Model
{
    protected $fillable = [
        'blok_lahan_id',
        'tanggal_observasi',
        'tanggal_pemupukan_terakhir',
        'ph_tanah',
        'kelembaban_tanah',
        'curah_hujan_kategori',
        'musim_saat_ini',
        'warna_daun',
        'kondisi_pelepah',
        'gejala_defisiensi',
        'kondisi_tandan',
        'kondisi_drainase',
        'ada_gulma_dominan',
        'ada_serangan_hama',
        'catatan_observasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_observasi'          => 'date',
            'tanggal_pemupukan_terakhir' => 'date',
            'gejala_defisiensi'          => 'array',
            'ada_gulma_dominan'          => 'boolean',
            'ada_serangan_hama'          => 'boolean',
            'ph_tanah'                   => 'decimal:2',
        ];
    }

    public function blokLahan(): BelongsTo
    {
        return $this->belongsTo(BlokLahan::class);
    }

    public function rekomendasiRbs(): HasMany
    {
        return $this->hasMany(RekomendasiRbs::class);
    }

    public function getLabelPhAttribute(): string
    {
        if (is_null($this->ph_tanah)) return '-';
        return match(true) {
            $this->ph_tanah < 4.0  => 'Sangat Masam',
            $this->ph_tanah < 5.5  => 'Masam',
            $this->ph_tanah < 6.5  => 'Agak Masam (Optimal)',
            $this->ph_tanah < 7.5  => 'Netral',
            default                => 'Basa',
        };
    }
}
```

### RuleBaseLanjutan.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleBaseLanjutan extends Model
{
    protected $table = 'rule_bases_lanjutan';

    protected $fillable = [
        'kondisi_warna_daun',
        'kondisi_ph_min',
        'kondisi_ph_max',
        'kondisi_kelembaban',
        'kondisi_curah_hujan_kategori',
        'kondisi_musim',
        'kondisi_drainase',
        'kondisi_defisiensi',
        'kondisi_kategori_umur',
        'kondisi_pelepah',
        'kondisi_tandan',
        'ada_serangan_hama',
        'ada_gulma_dominan',
        'kondisi_intermediate',
        'prasyarat_intermediate',
        'indikasi_masalah',
        'jenis_pupuk_utama',
        'jenis_pupuk_pendukung',
        'dosis_anjuran',
        'metode_aplikasi',
        'waktu_aplikasi',
        'saran_tindakan',
        'status_kebutuhan',
        'prioritas',
        'aktif',
        'keterangan_rule',
    ];

    protected function casts(): array
    {
        return [
            'aktif'                    => 'boolean',
            'ada_serangan_hama'        => 'boolean',
            'ada_gulma_dominan'        => 'boolean',
            'kondisi_ph_min'           => 'decimal:2',
            'kondisi_ph_max'           => 'decimal:2',
            'prioritas'                => 'integer',
            'kondisi_intermediate'     => 'array',
            'prasyarat_intermediate'   => 'array',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
```

### RekomendasiRbs.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiRbs extends Model
{
    protected $table = 'rekomendasi_rbs';

    protected $fillable = [
        'blok_lahan_id',
        'kondisi_lahan_id',
        'admin_id',
        'tanggal_analisis',
        'is_latest',
        'nomor_analisis',
        'rules_terpicu',
        'masalah_teridentifikasi',
        'rekomendasi_pupuk',
        'saran_tindakan_utama',
        'status_kebutuhan_dominan',
        'jumlah_rule_terpicu',
        'dosis_urea',
        'dosis_kcl',
        'total_urea',
        'total_kcl',
        'catatan_dosis',
        'jadwal_pemupukan',
        'validitas_rekomendasi',
        'catatan_validitas',
        'confidence_score',
        'confidence_label',
        'catatan_confidence',
        'data_cukup',
        'data_kurang',
        'notifikasi_data',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_analisis'        => 'date',
            'rules_terpicu'           => 'array',
            'masalah_teridentifikasi' => 'array',
            'rekomendasi_pupuk'       => 'array',
            'jadwal_pemupukan'        => 'array',
            'data_kurang'             => 'array',
            'is_latest'               => 'boolean',
            'data_cukup'              => 'boolean',
            'dosis_urea'              => 'double',
            'dosis_kcl'               => 'double',
            'total_urea'              => 'double',
            'total_kcl'               => 'double',
            'confidence_score'        => 'integer',
        ];
    }

    public function blokLahan(): BelongsTo
    {
        return $this->belongsTo(BlokLahan::class, 'blok_lahan_id');
    }

    public function kondisiLahan(): BelongsTo
    {
        return $this->belongsTo(KondisiLahan::class, 'kondisi_lahan_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function getWarnaBadgeAttribute(): string
    {
        return match($this->status_kebutuhan_dominan) {
            'Darurat' => 'red',
            'Segera'  => 'orange',
            'Normal'  => 'green',
            'Tunda'   => 'gray',
            default   => 'blue',
        };
    }

    public static function labelStatus(?string $status): string
    {
        return match($status) {
            'Darurat' => 'Defisiensi Berat',
            'Segera'  => 'Perlu Pupuk',
            'Normal'  => 'Sehat',
            'Tunda'   => 'Tunda Pupuk',
            default   => 'Belum Dicek',
        };
    }

    public function getKarungUreaAttribute(): int
    {
        return $this->total_urea ? (int) ceil($this->total_urea / 50) : 0;
    }

    public function getKarungKclAttribute(): int
    {
        return $this->total_kcl ? (int) ceil($this->total_kcl / 50) : 0;
    }

    public function getWarnaConfidenceAttribute(): string
    {
        return match($this->confidence_label) {
            'Tinggi' => 'green',
            'Sedang' => 'blue',
            default  => 'amber',
        };
    }

    public function getWarnaValiditasAttribute(): string
    {
        return match($this->validitas_rekomendasi) {
            'Terverifikasi' => 'green',
            'Cukup Kuat'    => 'blue',
            default         => 'amber',
        };
    }

    public function scopeLatest_only($query)
    {
        return $query->where('is_latest', true);
    }
}
```

---

## CONTROLLERS

### AuthController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
```

### AnggotaController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    public function index()
    {
        $anggotas = Anggota::withCount('blokLahans')->orderBy('nama')->paginate(10);
        return view('anggota.index', compact('anggotas'));
    }

    public function create()
    {
        return view('anggota.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'   => ['required', 'string', 'max:100', 'unique:anggotas,nama'],
            'no_hp'  => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ], [
            'nama.required' => 'Nama anggota wajib diisi.',
            'nama.unique'   => 'Nama anggota ini sudah terdaftar.',
        ]);

        Anggota::create($validated);
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Anggota $anggotum)
    {
        return view('anggota.edit', ['anggota' => $anggotum]);
    }

    public function update(Request $request, Anggota $anggotum)
    {
        $validated = $request->validate([
            'nama'   => ['required', 'string', 'max:100', 'unique:anggotas,nama,' . $anggotum->id],
            'no_hp'  => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ]);

        $anggotum->update($validated);
        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggotum)
    {
        if ($anggotum->blokLahans()->exists()) {
            return redirect()->route('anggota.index')
                ->with('error', "Anggota '{$anggotum->nama}' tidak bisa dihapus karena masih memiliki blok lahan.");
        }

        $anggotum->delete();
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus.');
    }
}
```

### BlokLahanController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\BlokLahan;
use Illuminate\Http\Request;

class BlokLahanController extends Controller
{
    public function index(Request $request)
    {
        $query = BlokLahan::with(['anggota', 'rekomendasiRbsTerbaru', 'kondisiTerbaru']);

        if ($request->filled('anggota_id')) {
            $query->where('anggota_id', $request->anggota_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'Belum') {
                $query->whereDoesntHave('rekomendasiRbsTerbaru');
            } else {
                $query->whereHas('rekomendasiRbsTerbaru', function ($q) use ($request) {
                    $q->where('status_kebutuhan_dominan', $request->status);
                });
            }
        }

        $allFiltered = $query->latest()->get();

        $grouped = $allFiltered->groupBy('anggota_id')->map(function ($bloks) {
            $anggota = $bloks->first()->anggota;
            return [
                'anggota'         => $anggota,
                'bloks'           => $bloks,
                'latest_activity' => $bloks->max(fn($b) => $b->updated_at?->timestamp ?? 0),
            ];
        })->sortByDesc('latest_activity')->values();

        $anggotas = \App\Models\Anggota::orderBy('nama')->get();
        $totalBlok = BlokLahan::count();

        return view('blok_lahan.index', compact('grouped', 'anggotas', 'totalBlok'));
    }

    public function create()
    {
        $anggotas = Anggota::orderBy('nama')->get();
        $existingBloks = BlokLahan::select('id', 'nama_blok', 'koordinat_geojson')->get()
            ->map(fn($b) => ['nama' => $b->nama_blok, 'geojson' => json_decode($b->koordinat_geojson, true)])
            ->filter(fn($b) => $b['geojson'] !== null)->values();

        return view('blok_lahan.create', compact('anggotas', 'existingBloks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id'        => ['required', 'exists:anggotas,id'],
            'nama_blok'         => ['required', 'string', 'max:100'],
            'luas_ha'           => ['required', 'numeric', 'min:0.01'],
            'sph'               => ['required', 'integer', 'min:1'],
            'koordinat_geojson' => ['required', 'string'],
            'tahun_tanam'       => ['required', 'integer', 'min:1990', 'max:' . now()->year],
            'jenis_tanah'       => ['required', 'in:Tanah Lempung,Tanah Lempung Berpasir,Tanah Berpasir,Tanah Liat,Tanah Gambut,Tanah Aluvial,Tanah Podsolik Merah Kuning (PMK),Tanah Laterit,Tanah Berbatu,Lainnya'],
            'topografi'         => ['required', 'in:Datar 0-15°,Bergelombang 15-30°,Curam >30°'],
        ]);

        json_decode($validated['koordinat_geojson']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['koordinat_geojson' => 'Format GeoJSON tidak valid.'])->withInput();
        }

        BlokLahan::create($validated);

        $redirect = redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil ditambahkan.');

        if ($validated['sph'] < 100 || $validated['sph'] > 160) {
            $redirect = $redirect->with('warning', "SPH yang dimasukkan ({$validated['sph']} pohon/ha) di luar rentang normal kelapa sawit (136–148 pohon/ha).");
        }

        return $redirect;
    }

    public function show(BlokLahan $blokLahan)
    {
        $blokLahan->load(['anggota', 'kondisiTerbaru', 'rekomendasiRbsTerbaru']);
        return view('blok_lahan.show', compact('blokLahan'));
    }

    public function edit(BlokLahan $blokLahan)
    {
        $anggotas = Anggota::orderBy('nama')->get();
        $existingBloks = BlokLahan::where('id', '!=', $blokLahan->id)
            ->select('id', 'nama_blok', 'koordinat_geojson')->get()
            ->map(fn($b) => ['nama' => $b->nama_blok, 'geojson' => json_decode($b->koordinat_geojson, true)])
            ->filter(fn($b) => $b['geojson'] !== null)->values();

        return view('blok_lahan.edit', compact('blokLahan', 'anggotas', 'existingBloks'));
    }

    public function update(Request $request, BlokLahan $blokLahan)
    {
        // Validasi sama seperti store
        $validated = $request->validate([...]);
        $blokLahan->update($validated);
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil diperbarui.');
    }

    public function destroy(BlokLahan $blokLahan)
    {
        $blokLahan->delete();
        return redirect()->route('blok-lahan.index')->with('success', 'Blok lahan berhasil dihapus.');
    }
}
```

### DashboardController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\RekomendasiRbs;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $blokLahans = BlokLahan::with([
            'anggota',
            'rekomendasiRbsTerbaru',
            'kondisiTerbaru',
        ])->get();

        $mapData = $blokLahans->map(function ($blok) {
            $rbs = $blok->rekomendasiRbsTerbaru;
            $statusDb = $rbs?->status_kebutuhan_dominan ?? 'Belum Dianalisis';

            return [
                'id'               => $blok->id,
                'nama_blok'        => $blok->nama_blok,
                'nama_pemilik'     => $blok->nama_pemilik,
                'luas_ha'          => $blok->luas_ha,
                'sph'              => $blok->sph,
                'umur_tanaman'     => $blok->umur_tanaman,
                'geojson'          => json_decode($blok->koordinat_geojson, true),
                'status_rbs'       => $statusDb,
                'status_label'     => RekomendasiRbs::labelStatus($statusDb),
                'masalah_rbs'      => $rbs?->masalah_teridentifikasi ?? [],
                'pupuk_rbs'        => $rbs?->rekomendasi_pupuk ?? [],
                'saran_rbs'        => $rbs?->saran_tindakan_utama ?? '',
                'tgl_analisis_rbs' => $rbs?->tanggal_analisis?->format('d/m/Y') ?? '-',
                'jumlah_rule'      => $rbs?->jumlah_rule_terpicu ?? 0,
                'dosis_urea'       => $rbs?->dosis_urea,
                'dosis_kcl'        => $rbs?->dosis_kcl,
                'total_urea'       => $rbs?->total_urea,
                'total_kcl'        => $rbs?->total_kcl,
            ];
        });

        // Stats, Delta bulan lalu, Blok perlu perhatian
        $stats = [...];
        $statsBulanLalu = [...];
        $blokPerluPerhatian = $blokLahans->filter(function ($blok) {
            if ($blok->kondisiTerbaru && !$blok->rekomendasiRbsTerbaru) return true;
            if ($blok->rekomendasiRbsTerbaru && $blok->rekomendasiRbsTerbaru->tanggal_analisis->diffInDays(now()) > 90) return true;
            return false;
        })->values();

        return view('dashboard.index', compact('mapData', 'stats', 'statsBulanLalu', 'blokPerluPerhatian'));
    }
}
```

### KondisiLahanController.php
```php
<?php
// CRUD Kondisi Lahan dengan fitur:
// - Filter by anggota, grouped by anggota
// - Hitung centroid polygon untuk API cuaca
// - Validasi konsistensi logis lintas-field (A4)
//   - Musim kemarau + kelembaban tinggi → warning
//   - Musim hujan + kelembaban rendah → warning
//   - Drainase tergenang + curah hujan rendah → warning
//   - Curah hujan tinggi + kelembaban kering → warning
//   - Daun hijau normal + ada gejala defisiensi → warning
// - Auto-fetch cuaca via Open-Meteo API (centroid polygon)
// Lihat file lengkap: app/Http/Controllers/KondisiLahanController.php
```

### CuacaController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CuacaController extends Controller
{
    /**
     * Ambil data curah hujan dari Open-Meteo API berdasarkan koordinat.
     * Mengembalikan curah hujan 30 hari terakhir + kategori + musim otomatis.
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Ambil data curah hujan 30 hari terakhir dari Open-Meteo
        // Hitung rata-rata harian → kategorikan berdasarkan BMKG
        // < 2 mm/hari = Sangat Rendah
        // 2-5 mm/hari = Rendah
        // 5-13 mm/hari = Normal
        // 13-25 mm/hari = Tinggi
        // > 25 mm/hari = Sangat Tinggi

        // Deteksi musim berdasarkan bulan + curah hujan aktual
        // Indonesia: Nov-Mar = Musim Hujan, Mei-Sep = Musim Kemarau
        // Apr, Okt = Peralihan

        return response()->json([
            'success'              => true,
            'curah_hujan_kategori' => $kategoriCurahHujan,
            'musim_saat_ini'       => $musim,
            'detail'               => [...],
        ]);
    }
}
```

### RbsController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\BlokLahan;
use App\Models\RekomendasiRbs;
use App\Models\RuleBaseLanjutan;
use App\Services\RbsService;
use Illuminate\Http\Request;

class RbsController extends Controller
{
    public function __construct(private RbsService $rbsService) {}

    // index() — Daftar blok + status analisis (grouped by anggota, filter, stats)
    // analisis($blokLahan) — Analisis satu blok via RbsService
    // analisisSemua() — Analisis semua blok yang memiliki data kondisi
    // detail($blokLahan) — Detail hasil analisis + histori rekomendasi
    // apiPopup($blokLahan) — JSON untuk popup peta WebGIS
    // daftarBlokBelumAnalisis() — JSON daftar blok belum analisis (AJAX)
}
```

### RuleBaseController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\RuleBaseLanjutan;
use Illuminate\Http\Request;

class RuleBaseController extends Controller
{
    // CRUD rule base RBS
    // index() — tampilkan semua rule sorted by prioritas
    // info() — halaman info penjelasan rule base
    // create/store/edit/update/destroy — manajemen rule
    // validateRule() — validasi kondisi IF + output THEN
}
```

### LaporanController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\BlokLahan;
use App\Models\RekomendasiRbs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // index() — Rekap laporan grouped by anggota, filter status/anggota/blok
    //   - Grand total Urea & KCl (hanya blok layak: Normal + Segera)
    //   - Hitung karung (50 kg/karung)
    // show($rekomendasiRbs) — Detail satu rekomendasi
    // exportPdf($rekomendasiRbs) — Export PDF via DomPDF
    // exportRingkasan($rekomendasiRbs) — Format teks WhatsApp-friendly
}
```

---

## SERVICES

### RbsService.php (Rule-Based System Engine)

```php
<?php

namespace App\Services;

use App\Models\BlokLahan;
use App\Models\KondisiLahan;
use App\Models\RuleBaseLanjutan;
use App\Models\RekomendasiRbs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RbsService
{
    /**
     * Mapping warna daun → dugaan unsur hara (untuk confidence score).
     */
    private array $mappingVisualUnsur = [
        'Hijau Pucat'          => ['N'],
        'Kuning Merata'        => ['N', 'Zn'],
        'Kuning Tepi'          => ['K'],
        'Oranye/Kemerahan'     => ['K'],
        'Kuning Antar Tulang'  => ['Mg', 'Fe'],
        'Coklat Ujung'         => ['P', 'K'],
        'Bercak Nekrotik'      => ['K', 'P'],
    ];

    /**
     * ALUR UTAMA ANALISIS:
     * 1. Ambil kondisi lahan terbaru
     * 2. Cek kecukupan data (Fitur 7)
     * 3. Cek minimal 1 field terisi
     * 4. Ambil kategori umur dari blok
     * 5. Ambil semua rule aktif (urut prioritas)
     * 6. Forward Chaining + Rule Chaining (intermediate flags)
     * 7. Jika tidak ada rule terpicu → status Normal
     * 8. Susun output dari rule terpicu
     */
    public function analisis(BlokLahan $blok): array { ... }

    public function analisisSemua(): array { ... }

    // ═══ FITUR 7: Cek Kecukupan Data ═══
    // Minimal 5 dari 7 field penting harus terisi
    // Field penting: warna_daun, ph_tanah, kelembaban_tanah,
    //   curah_hujan, musim, drainase, tgl_pemupukan_terakhir
    private function cekKecukupanData(KondisiLahan $kondisi): array { ... }

    // ═══ FITUR 3: Validitas Rekomendasi ═══
    // - "Cukup Kuat": warna_daun + pH + (kelembaban OR curah_hujan) + drainase
    // - "Estimasi Visual": data pendukung tidak lengkap
    private function tentukanValiditasRekomendasi(...): array { ... }

    // ═══ FITUR 6: Confidence Score (0-100) ═══
    // A. Kelengkapan Data — Maks 40 poin
    // B. Jumlah Rule Terpicu — Maks 25 poin
    // C. Kesesuaian Visual + Dugaan Unsur — Maks 20 poin
    // D. Penalti Data Kontradiktif — Maks -20 poin
    // Label: Tinggi (≥75), Sedang (≥50), Rendah (<50)
    private function hitungConfidence(...): array { ... }

    // ═══ FITUR 2: Jadwal Pemupukan Per Tahap ═══
    // Pembagian: Darurat 70/30, Segera 60/40, Normal 50/50
    // Catatan kontekstual berdasarkan curah hujan & drainase
    private function generateJadwalPemupukan(...): array { ... }

    // ═══ CORE: Evaluasi Rule (Forward Chaining) ═══
    // Semua kondisi yang diisi di rule harus terpenuhi (AND logic)
    // Kondisi NULL di rule = diabaikan
    // Cek: warna_daun, pH range, kelembaban, curah_hujan, musim,
    //       drainase, defisiensi, pelepah, hama, gulma, tandan, umur
    private function evaluasiRule(RuleBaseLanjutan $rule, KondisiLahan $kondisi, ?string $kategoriUmur): bool { ... }

    // Rule Chaining (A2): cek prasyarat intermediate
    private function cekPrasyaratIntermediate(RuleBaseLanjutan $rule, array $intermediateFlags): bool { ... }

    // ═══ FITUR 1: Histori (simpan setiap analisis) ═══
    // Jika hasil sama persis → hanya update tanggal
    // Jika berbeda → set is_latest=false pada record lama, buat baru
    private function simpanDenganHistori(int $blokLahanId, array $data): RekomendasiRbs { ... }

    // ═══ Hitung Dosis Standar Urea & KCl ═══
    // Base dosis berdasarkan kategori umur
    // Multiplier: jenis tanah × topografi × waktu sejak pupuk terakhir
    // Formula: dosis_per_pokok × SPH × luas_ha = total_kg
    private function hitungDosisStandar(BlokLahan $blok, ?KondisiLahan $kondisi): array
    {
        // Base dosis per kategori umur (kg/pokok):
        // Belum Menghasilkan: Urea 0.5, KCl 0.5
        // Remaja: Urea 1.5, KCl 1.0
        // Menghasilkan Muda: Urea 2.25, KCl 1.75
        // Menghasilkan Tua: Urea 2.75, KCl 2.25
        // Tua Renta: Urea 1.5, KCl 1.5

        // Multiplier Tanah:
        // Lempung: 1.0/1.0 | Berpasir: 1.3/1.4 | Gambut: 0.6/1.5
        // PMK: 1.1/1.2 | Liat: 0.9/0.9

        // Multiplier Topografi:
        // Datar: 1.0 | Bergelombang: 1.1 | Curam: 1.2

        // Multiplier Waktu Pemupukan Terakhir:
        // < 60 hari: ×0.75 (masih baru)
        // 60-120 hari: ×1.0 (normal)
        // > 120 hari: ×1.25 (terlambat)
    }
}
```

---

## DATABASE MIGRATIONS

### Tabel: admins
```php
Schema::create('admins', function (Blueprint $table) {
    $table->id();
    $table->string('username', 50)->unique();
    $table->string('password');
    $table->string('nama_lengkap', 100);
    $table->timestamps();
});
```

### Tabel: anggotas
```php
Schema::create('anggotas', function (Blueprint $table) {
    $table->id();
    $table->string('nama', 100)->unique();
    $table->string('no_hp', 20)->nullable();
    $table->text('alamat')->nullable();
    $table->timestamps();
});
```

### Tabel: blok_lahans
```php
Schema::create('blok_lahans', function (Blueprint $table) {
    $table->id();
    $table->string('nama_blok', 100);
    $table->double('luas_ha');
    $table->integer('sph')->comment('Standar Pohon per Hektar');
    $table->longText('koordinat_geojson');
    $table->timestamps();
});
// + migrasi tambahan:
// - nama_pemilik (deprecated, diganti relasi anggota)
// - anggota_id (FK ke anggotas)
// - tahun_tanam, jenis_tanah, topografi (merge dari kriteria_lahans)
```

### Tabel: kondisi_lahans
```php
Schema::create('kondisi_lahans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('blok_lahan_id')->constrained('blok_lahans')->onDelete('cascade');
    $table->date('tanggal_observasi');
    // Parameter Tanah
    $table->decimal('ph_tanah', 4, 2)->nullable();
    $table->enum('kelembaban_tanah', ['Sangat Kering','Kering','Normal','Lembab','Sangat Lembab'])->nullable();
    // Parameter Iklim
    $table->enum('curah_hujan_kategori', ['Sangat Rendah','Rendah','Normal','Tinggi','Sangat Tinggi'])->nullable();
    $table->enum('musim_saat_ini', ['Musim Hujan','Musim Kemarau','Peralihan'])->nullable();
    // Gejala Visual Daun
    $table->enum('warna_daun', ['Hijau Normal','Hijau Pucat','Kuning Merata','Kuning Tepi','Kuning Antar Tulang','Oranye/Kemerahan','Coklat Ujung','Bercak Nekrotik'])->nullable();
    $table->enum('kondisi_pelepah', ['Normal','Patah/Menggantung','Kering Prematur','Pertumbuhan Terhambat'])->nullable();
    $table->json('gejala_defisiensi')->nullable()->comment('array: N,P,K,Mg,B,Fe,Zn');
    // Gejala Visual Buah & Tandan
    $table->enum('kondisi_tandan', ['Normal','Kecil','Rontok Prematur','Busuk Pangkal','Tidak Ada Tandan'])->nullable();
    // Kondisi Fisik Lahan
    $table->enum('kondisi_drainase', ['Baik','Cukup','Buruk — Tergenang'])->nullable();
    $table->boolean('ada_gulma_dominan')->default(false);
    $table->boolean('ada_serangan_hama')->default(false);
    $table->text('catatan_observasi')->nullable();
    // + migrasi tambahan: tanggal_pemupukan_terakhir
    $table->timestamps();
});
```

### Tabel: rule_bases_lanjutan
```php
Schema::create('rule_bases_lanjutan', function (Blueprint $table) {
    $table->id();
    // Kondisi (IF)
    $table->string('kondisi_warna_daun', 100)->nullable();
    $table->decimal('kondisi_ph_min', 4, 2)->nullable();
    $table->decimal('kondisi_ph_max', 4, 2)->nullable();
    $table->string('kondisi_kelembaban', 50)->nullable();
    $table->string('kondisi_musim', 50)->nullable();
    $table->string('kondisi_drainase', 50)->nullable();
    $table->string('kondisi_defisiensi', 50)->nullable();
    $table->string('kondisi_kategori_umur', 50)->nullable();
    // + migrasi tambahan: kondisi_pelepah, kondisi_tandan, ada_serangan_hama,
    //   ada_gulma_dominan, kondisi_curah_hujan_kategori,
    //   kondisi_intermediate (JSON), prasyarat_intermediate (JSON)
    // Hasil (THEN)
    $table->string('indikasi_masalah', 255);
    $table->string('jenis_pupuk_utama', 100);
    $table->string('jenis_pupuk_pendukung', 100)->nullable();
    $table->string('dosis_anjuran', 150);
    $table->string('metode_aplikasi', 255)->nullable();
    $table->string('waktu_aplikasi', 150)->nullable();
    $table->text('saran_tindakan');
    $table->enum('status_kebutuhan', ['Darurat','Segera','Normal','Tunda'])->default('Normal');
    $table->tinyInteger('prioritas')->unsigned()->default(5);
    $table->boolean('aktif')->default(true);
    $table->text('keterangan_rule')->nullable();
    $table->timestamps();
});
```

### Tabel: rekomendasi_rbs
```php
Schema::create('rekomendasi_rbs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('blok_lahan_id')->constrained('blok_lahans')->onDelete('cascade');
    $table->foreignId('kondisi_lahan_id')->constrained('kondisi_lahans')->onDelete('cascade');
    $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
    $table->date('tanggal_analisis');
    $table->json('rules_terpicu');
    $table->json('masalah_teridentifikasi');
    $table->json('rekomendasi_pupuk');
    $table->text('saran_tindakan_utama');
    $table->enum('status_kebutuhan_dominan', ['Darurat','Segera','Normal','Tunda']);
    $table->tinyInteger('jumlah_rule_terpicu')->unsigned()->default(0);
    // + migrasi tambahan:
    //   dosis_urea, dosis_kcl, total_urea, total_kcl, catatan_dosis
    //   jadwal_pemupukan (JSON), validitas_rekomendasi, catatan_validitas
    //   confidence_score, confidence_label, catatan_confidence
    //   data_cukup, data_kurang (JSON), notifikasi_data
    //   is_latest (boolean), nomor_analisis (integer)
    $table->timestamps();
});
```

---

## DATABASE SEEDERS

### DatabaseSeeder.php
```php
$this->call([
    AdminSeeder::class,
    RuleBaseLanjutanSeeder::class,
]);
```

### AdminSeeder.php
```php
Admin::create([
    'username'     => 'admin',
    'password'     => 'admin123',
    'nama_lengkap' => 'Administrator',
]);
```

### RuleBaseLanjutanSeeder.php (20 rules)
Rule yang di-seed mencakup:
| Grup | Kondisi | Status |
|------|---------|--------|
| 1. Defisiensi N | Kuning Merata + def. N | Segera |
| 2. Defisiensi N ringan | Hijau Pucat + def. N | Normal |
| 3. Defisiensi K berat | Oranye/Kemerahan + def. K | Darurat |
| 4. Defisiensi K sedang | Kuning Tepi + def. K | Segera |
| 5. Defisiensi Mg | Kuning Antar Tulang + def. Mg | Segera |
| 6. Defisiensi B | def. B + pelepah terhambat | Segera |
| 7. pH Sangat Masam | pH 3.0–4.5 | Darurat |
| 8. pH Masam | pH 4.5–5.5 | Normal |
| 9. Waterlogging | Drainase buruk + sangat lembab | Tunda |
| 10. Kemarau parah | Kemarau + sangat kering | Tunda |
| 11. Kemarau biasa | Kemarau + kering | Normal |
| 12. TBM defisiensi N | Umur < 3 thn + kuning | Segera |
| 13. Tanaman Tua Renta | Umur > 25 thn | Tunda |
| 14. Kondisi Optimal | Hijau + pH 5.5-6.5 + drainase baik | Normal |
| 15. Defisiensi P | Coklat ujung + def. P | Segera |
| 16. Hama + bercak | Bercak nekrotik | Segera |
| 17. Defisiensi Fe | Kuning antar tulang + pH > 6.5 | Segera |
| 18. Pelepah kering | Pelepah kering prematur | Segera |
| 19. Tandan rontok | Tandan rontok prematur | Segera |
| 20. Busuk pangkal | Tandan busuk pangkal | Darurat |
| + Grup musim hujan normal, tanaman remaja, menghasilkan tua, def. Zn, serangan hama |

### RuleCurahHujanGulmaSeeder.php (3 rules)
| Kondisi | Status |
|---------|--------|
| Curah hujan sangat tinggi | Tunda |
| Curah hujan sangat rendah | Tunda |
| Gulma dominan | Segera |

---

## VIEWS (BLADE TEMPLATES)

### layouts/app.blade.php
- Layout utama dengan sidebar navigasi responsif
- Menu: Dashboard (WebGIS), Anggota, Blok Lahan, Kondisi Lahan, Rule Base, Analisis, Laporan, Panduan
- Notifikasi bell (blok status Darurat)
- Flash messages (success, error, warning)
- Custom confirm modal (hapus, logout)
- Back-to-top button
- Double-submit prevention (global)
- Leaflet.js CDN, Vite assets (Tailwind + app.js)

### dashboard/index.blade.php
- Peta WebGIS (Leaflet.js) dengan polygon blok lahan berwarna per status
- Stats cards: total blok, total luas, dianalisis, darurat, segera
- Delta perbandingan bulan lalu
- Blok perlu perhatian (belum analisis atau > 90 hari)
- Luas per status
- Filter: status (toggle), pemilik, blok
- Custom zoom slider (continuous zoom on hold)
- Fullscreen toggle
- Layer switcher (OSM / Satelit Esri)
- Popup: info blok + status + masalah + rekomendasi pupuk + link detail

### anggota/ (index, create, edit)
- CRUD anggota kelompok tani
- Tabel dengan jumlah blok per anggota
- Pagination

### blok_lahan/ (index, create, edit, show)
- CRUD blok lahan dengan peta interaktif (draw polygon)
- Form: pemilik, nama, luas, SPH, koordinat GeoJSON, tahun tanam, jenis tanah, topografi
- Grouped by anggota
- Filter by anggota, status RBS
- Overlap detection (@turf/turf)
- Show: detail blok + kondisi terbaru + status RBS

### kondisi_lahan/ (index, create, edit)
- CRUD kondisi/observasi lahan
- Form: parameter tanah, iklim, gejala visual, drainase, hama, gulma
- Auto-fetch cuaca dari Open-Meteo API (berdasarkan centroid polygon)
- Cascading filter: anggota → blok
- Grouped by anggota

### rbs/ (index, detail)
- Index: daftar blok + status analisis, filter anggota/blok, stats
- Tombol: Analisis satu / Analisis semua
- Detail: hasil lengkap analisis RBS
  - Status + confidence + validitas
  - Masalah teridentifikasi
  - Rekomendasi pupuk + dosis
  - Jadwal pemupukan per tahap
  - Rules terpicu
  - Histori rekomendasi

### rule_base/ (index, create, edit, info)
- CRUD rule base RBS
- Form kondisi (IF): warna daun, pH, kelembaban, curah hujan, musim, drainase, defisiensi, umur, pelepah, tandan, hama, gulma
- Form output (THEN): indikasi, pupuk, dosis, metode, waktu, saran, status, prioritas
- Halaman info: penjelasan cara kerja rule base

### laporan/ (index, show, pdf)
- Rekap laporan grouped by anggota
- Grand total kebutuhan pupuk (Urea + KCl dalam kg dan karung)
- Filter: status, anggota, blok, histori
- Show: detail satu rekomendasi
- Export PDF (DomPDF)
- Export ringkasan teks (WhatsApp-friendly)

### components/
- filter-searchable.blade.php — dropdown filter dengan pencarian
- searchable-select.blade.php — select input dengan autocomplete
- status-badge.blade.php — badge warna status RBS

### panduan.blade.php
- Halaman panduan penggunaan sistem

---

## CONFIG

### config/auth.php
```php
'defaults' => ['guard' => 'admin'],

'guards' => [
    'web'   => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'session', 'provider' => 'admins'],
],

'providers' => [
    'users'  => ['driver' => 'eloquent', 'model' => App\Models\User::class],
    'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
],
```

### AppServiceProvider.php
```php
public function boot(): void
{
    // Share notifikasi blok kritis (E3) ke semua view yang pakai layout app
    View::composer('layouts.app', function ($view) {
        $blokDarurat = BlokLahan::whereHas('rekomendasiRbsTerbaru', function ($q) {
            $q->where('status_kebutuhan_dominan', 'Darurat');
        })->with('anggota')->limit(5)->get();

        $jumlahDarurat = RekomendasiRbs::where('status_kebutuhan_dominan', 'Darurat')->count();

        $view->with('notifBlokDarurat', $blokDarurat);
        $view->with('jumlahNotifDarurat', $jumlahDarurat);
    });
}
```

---

## FRONTEND ASSETS

### resources/js/app.js
- Bootstrap Axios
- Import modul

### resources/js/overlap-detector.js
- Deteksi tumpang tindih polygon blok lahan menggunakan @turf/turf

### resources/css/app.css
- Tailwind CSS 4 base styles

---

## RINGKASAN FITUR UTAMA

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | WebGIS Dashboard | Peta interaktif Leaflet.js dengan polygon berwarna per status |
| 2 | Manajemen Anggota | CRUD anggota kelompok tani |
| 3 | Manajemen Blok Lahan | CRUD + peta draw polygon + overlap detection |
| 4 | Input Kondisi Lahan | Observasi parameter tanah, iklim, visual + auto cuaca |
| 5 | Rule-Based System | Forward chaining + rule chaining, 20+ rules |
| 6 | Analisis Pemupukan | Hitung dosis (umur × tanah × topografi × waktu) |
| 7 | Confidence Score | Skor keyakinan 0-100 berdasarkan kelengkapan data |
| 8 | Validitas Rekomendasi | Cukup Kuat / Estimasi Visual |
| 9 | Jadwal Pemupukan | Split dosis per tahap (70/30, 60/40, 50/50) |
| 10 | Histori Rekomendasi | Simpan setiap analisis, tracking perubahan |
| 11 | Laporan & Export | Rekap per anggota, PDF, ringkasan WhatsApp |
| 12 | Notifikasi Darurat | Bell notification blok defisiensi berat |
| 13 | API Cuaca | Auto-fetch Open-Meteo, kategorisasi BMKG |
| 14 | Konsistensi Data | Warning kontradiksi logis antar field |
| 15 | Responsive UI | Mobile-first, Tailwind CSS 4 |
