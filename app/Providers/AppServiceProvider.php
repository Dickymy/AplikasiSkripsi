<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share notifikasi blok kritis (E3) ke semua view yang pakai layout app
        View::composer('layouts.app', function ($view) {
            $blokDarurat = \App\Models\BlokLahan::whereHas('rekomendasiRbsTerbaru', function ($q) {
                $q->where('status_kebutuhan_dominan', 'Darurat');
            })->with('anggota')->limit(5)->get();

            $jumlahDarurat = \App\Models\RekomendasiRbs::where('status_kebutuhan_dominan', 'Darurat')->count();

            $view->with('notifBlokDarurat', $blokDarurat);
            $view->with('jumlahNotifDarurat', $jumlahDarurat);
        });
    }
}
