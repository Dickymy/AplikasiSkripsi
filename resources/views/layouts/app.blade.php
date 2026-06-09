<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Pendukung Keputusan Pemupukan Kelapa Sawit - Kelompok Tani">
    <title>@yield('title', 'Dashboard') — SPK Sawit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 font-[Inter]">

<div class="flex h-full">
    {{-- SIDEBAR --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full shadow-sm">

        {{-- Nama Aplikasi --}}
        <div class="flex items-center px-6 py-5 border-b border-slate-100">
            <div>
                <p class="text-sm font-bold text-slate-900 leading-tight">SPK Sawit</p>
                <p class="text-xs text-slate-500">Kelompok Tani</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Utama</p>

            <a href="{{ route('dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Peta Lahan (WebGIS)
            </a>

            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Data Master</p>

            <a href="{{ route('anggota.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('anggota.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Anggota Kelompok Tani
            </a>

            <a href="{{ route('blok-lahan.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('blok-lahan.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
                Manajemen Blok Lahan
            </a>

            <a href="{{ route('kondisi-lahan.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('kondisi-lahan.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Kondisi Lahan
            </a>

            <a href="{{ route('rule-base.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('rule-base.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Rule Base
            </a>

            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Analisis</p>

            <a href="{{ route('rbs.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('rbs.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Analisis Pemupukan
            </a>

            <a href="{{ route('laporan.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('laporan.*') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Laporan & Rekap
            </a>
        </nav>

        {{-- Admin Info --}}
        <div class="px-4 py-4 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(Auth::guard('admin')->user()->nama_lengkap ?? 'A', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ Auth::guard('admin')->user()->nama_lengkap ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ Auth::guard('admin')->user()->username ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="button" onclick="confirmLogout()" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MOBILE OVERLAY --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col lg:ml-64 min-h-screen">
        {{-- Top Bar --}}
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-500">@yield('page-subtitle', 'SPK Pemupukan Kelapa Sawit')</p>
                </div>
            </div>
            <div class="text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-6 pt-4 space-y-2">
            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ session('warning') }}
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // ─── Custom Confirm Modal ────────────────────────────────────
    function showConfirm(message, onConfirm) {
        var modal = document.getElementById('confirm-modal');
        var msgEl = document.getElementById('confirm-message');
        msgEl.textContent = message;
        modal.classList.remove('hidden');
        modal._onConfirm = onConfirm;
    }
    function closeConfirm() {
        document.getElementById('confirm-modal').classList.add('hidden');
    }
    function doConfirm() {
        var modal = document.getElementById('confirm-modal');
        if (modal._onConfirm) modal._onConfirm();
        closeConfirm();
    }

    // Helper untuk form delete dengan custom confirm
    function confirmDelete(formEl, nama) {
        showConfirm('Yakin ingin menghapus "' + nama + '"? Data terkait akan ikut terhapus.', function() {
            formEl.submit();
        });
    }
    function confirmLogout() {
        showConfirm('Apakah Anda yakin ingin keluar dari sistem?', function() {
            document.getElementById('logout-form').submit();
        });
    }
</script>

{{-- Global Confirm Modal --}}
<div id="confirm-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-sm w-full mx-4 animate-[fadeIn_0.15s_ease-out]">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi</h3>
                <p id="confirm-message" class="text-sm text-slate-600 mt-0.5"></p>
            </div>
        </div>
        <div class="flex gap-2 justify-end">
            <button onclick="closeConfirm()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
            <button onclick="doConfirm()" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-sm">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')
</body>
</html>
