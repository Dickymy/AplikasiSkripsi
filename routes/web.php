<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlokLahanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KondisiLahanController;
use App\Http\Controllers\KriteriaLahanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RbsController;
use App\Http\Controllers\RuleBaseController;
use App\Http\Controllers\SpkController;
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

    // Manajemen Blok Lahan
    Route::resource('blok-lahan', BlokLahanController::class);

    // Kriteria Lahan
    Route::resource('kriteria-lahan', KriteriaLahanController::class)->except(['show']);

    // Rule Base
    Route::resource('rule-base', RuleBaseController::class)->except(['show']);

    // SPK Forward Chaining
    Route::prefix('spk')->name('spk.')->group(function () {
        Route::get('/', [SpkController::class, 'index'])->name('index');
        Route::post('/analisis/{blokLahan}', [SpkController::class, 'analisis'])->name('analisis');
        Route::post('/analisis-semua', [SpkController::class, 'analisisSemua'])->name('analisis-semua');
        Route::get('/detail/{blokLahan}', [SpkController::class, 'detail'])->name('detail');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/{rekomendasiSpk}', [LaporanController::class, 'show'])->name('show');
    });

    // Kondisi Lahan
    Route::resource('kondisi-lahan', KondisiLahanController::class)->except(['show']);

    // RBS (Rule-Based System)
    Route::prefix('rbs')->name('rbs.')->group(function () {
        Route::get('/', [RbsController::class, 'index'])->name('index');
        Route::post('/analisis/{blokLahan}', [RbsController::class, 'analisis'])->name('analisis');
        Route::post('/analisis-semua', [RbsController::class, 'analisisSemua'])->name('analisisSemua');
        Route::get('/detail/{blokLahan}', [RbsController::class, 'detail'])->name('detail');
    });

    // API endpoint — RBS popup WebGIS
    Route::get('/api/rbs-popup/{blokLahan}', [RbsController::class, 'apiPopup'])->name('api.rbs.popup');
});
