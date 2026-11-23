<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA & Mobile Native Meta (Android / iOS) -->
    <meta name="theme-color" content="#060606">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="KUKANG EV">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/favicon.ico">

    <title>{{ $title ?? 'Dashboard' }} — KUKANG EV ITERA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false }"
    x-on:livewire:navigated.window="sidebarOpen = false"
    class="min-h-screen antialiased selection:bg-[#C6A343] selection:text-[#060606]">
    <div class="flex min-h-screen">
        <!-- Mobile Backdrop -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" style="display: none;"
            class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-zinc-800/90 bg-[#0A0A0D]/95 backdrop-blur-xl transition-transform duration-200 will-change-transform lg:static lg:z-auto lg:translate-x-0">

            <!-- Brand Header dengan Emblem KUKANG EV -->
            <div class="flex items-center justify-between border-b border-zinc-800/90 px-6 py-5">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#C6A343]/30 bg-[#060606] shadow-lg shadow-black/60">
                        <svg class="h-8 w-8" viewBox="0 0 100 100" fill="none">
                            <!-- Sisi Kiri Emas -->
                            <path d="M32 22C18 31 12 49 18 65C24 81 42 90 60 86V76C46 79 32 72 27 59C23 49 26 37 32 30V22Z" fill="#C6A343"/>
                            <path d="M27 62C30 48 37 40 45 40C49 40 51 44 48 48C45 52 38 53 35 66L27 62Z" fill="#C6A343"/>
                            <circle cx="43" cy="44" r="2.6" fill="#060606"/>
                            <!-- Sisi Kanan Merah -->
                            <path d="M40 16V26C56 22 72 30 77 45C81 56 78 68 70 76V85C85 75 91 56 85 39C79 22 60 12 40 16Z" fill="#A80D0D"/>
                            <path d="M75 64C72 49 65 40 57 40C53 40 51 44 54 48C57 52 64 53 67 68L75 64Z" fill="#A80D0D"/>
                            <circle cx="59" cy="44" r="2.6" fill="#060606"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-display text-base font-bold tracking-wider text-white">KUKANG EV</span>
                            <span class="rounded border border-[#A80D0D]/50 bg-[#A80D0D]/20 px-1.5 py-0.5 font-mono text-[9px] font-bold text-[#FCA5A5]">2027</span>
                        </div>
                        <span class="block font-mono text-[10px] tracking-widest text-[#C6A343] uppercase">ITERA • Motorsport EV</span>
                    </div>
                </div>

                <button @click="sidebarOpen = false" type="button" class="rounded-lg p-1.5 text-zinc-400 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links (SPA Mode dengan wire:navigate) -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-3.5 py-5 text-[13px] font-medium">
                <p class="px-3 pb-2 font-mono text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Operasional Tim</p>

                <a href="{{ route('dashboard') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    <span>Dashboard Analitik</span>
                </a>

                <a href="{{ route('attendance.scanner') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('attendance.scanner') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('attendance.scanner') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10"/>
                    </svg>
                    <span>Scan QR Absensi</span>
                </a>

                <a href="{{ route('events.index') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('events.*') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('events.*') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Agenda &amp; Kegiatan</span>
                </a>

                <a href="{{ route('contributions.index') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('contributions.*') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('contributions.*') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Contribution Tracker</span>
                </a>

                <a href="{{ route('documentation.index') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('documentation.*') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('documentation.*') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Galeri Dokumentasi</span>
                </a>

                <a href="{{ route('profile.show') }}" wire:navigate
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('profile.show') ? 'bg-gradient-to-r from-[#C6A343]/20 via-[#C6A343]/10 to-[#A80D0D]/15 text-white font-semibold ring-1 ring-[#C6A343]/40 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('profile.show') ? 'text-[#C6A343]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profil &amp; Riwayat Saya</span>
                </a>

                @role('super_admin|admin')
                    <div class="pt-5">
                        <p class="px-3 pb-2 font-mono text-[10px] font-semibold uppercase tracking-widest text-[#C6A343]">Administrasi &amp; Audit</p>

                        <a href="{{ route('members.index') }}" wire:navigate
                            class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('members.*') ? 'bg-gradient-to-r from-[#A80D0D]/25 to-[#C6A343]/10 text-white font-semibold ring-1 ring-[#A80D0D]/50 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('members.*') ? 'text-[#EF4444]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Master Data Anggota</span>
                        </a>

                        <a href="{{ route('divisions.index') }}" wire:navigate
                            class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('divisions.*') ? 'bg-gradient-to-r from-[#A80D0D]/25 to-[#C6A343]/10 text-white font-semibold ring-1 ring-[#A80D0D]/50 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('divisions.*') ? 'text-[#EF4444]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Divisi &amp; Jabatan</span>
                        </a>

                        <a href="{{ route('reports.index') }}" wire:navigate
                            class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-all {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-[#A80D0D]/25 to-[#C6A343]/10 text-white font-semibold ring-1 ring-[#A80D0D]/50 shadow-sm' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('reports.*') ? 'text-[#EF4444]' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Export Excel &amp; Cloud Sync</span>
                        </a>
                    </div>
                @endrole
            </nav>

            <!-- User Footer Card -->
            <div class="border-t border-zinc-800/90 p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl border border-zinc-800 bg-[#060606] p-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#C6A343]/25 to-[#A80D0D]/25 font-display text-xs font-bold text-[#C6A343] ring-1 ring-[#C6A343]/40">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate font-mono text-[10px] uppercase tracking-wider text-[#C6A343]">
                            {{ str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? 'anggota') }}
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900/70 py-2 text-xs font-semibold text-zinc-400 transition hover:border-[#A80D0D]/60 hover:bg-[#A80D0D]/20 hover:text-red-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800/90 bg-[#060606]/90 px-4 py-3 backdrop-blur-xl lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button @click="sidebarOpen = true" type="button"
                        class="shrink-0 rounded-xl border border-zinc-800 bg-zinc-900 p-2 text-zinc-400 active:scale-95 hover:text-white lg:hidden">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="truncate font-display text-base font-bold tracking-tight text-white sm:text-lg">
                        {{ $header ?? $title ?? 'KUKANG EV ITERA' }}
                    </h1>
                </div>

                <div class="flex shrink-0 items-center gap-2 text-xs">
                    <span class="hidden items-center gap-1.5 rounded-full border border-[#C6A343]/30 bg-[#C6A343]/10 px-3 py-1 font-mono text-[10px] font-semibold text-[#C6A343] md:inline-flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#C6A343] animate-ping"></span>
                        REVERB LIVE
                    </span>
                    <span class="rounded-full border border-zinc-800 bg-[#0F0F12] px-3 py-1 font-mono text-[10px] text-zinc-300 sm:px-3.5 sm:text-[11px]">
                        {{ now()->timezone('Asia/Jakarta')->format('d M Y • H:i') }} WIB
                    </span>
                </div>
            </header>

            <main class="flex-1 p-4 pb-24 sm:p-6 sm:pb-24 lg:p-8 lg:pb-8">
                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (Android & iOS) -->
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-800/90 bg-[#0A0A0D]/95 px-1 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden">
        <div class="grid grid-cols-5 items-center py-1.5 text-[10px] font-medium">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex flex-col items-center gap-1 py-1 transition active:scale-95 {{ request()->routeIs('dashboard') ? 'text-[#C6A343]' : 'text-zinc-400' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('events.index') }}" wire:navigate
                class="flex flex-col items-center gap-1 py-1 transition active:scale-95 {{ request()->routeIs('events.*') ? 'text-[#C6A343]' : 'text-zinc-400' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Kegiatan</span>
            </a>

            <a href="{{ route('attendance.scanner') }}" wire:navigate
                class="flex flex-col items-center justify-center transition active:scale-95">
                <span class="-mt-5 flex h-11 w-11 items-center justify-center rounded-full border border-[#DFBE63]/50 bg-gradient-to-br from-[#C6A343] to-[#DFBE63] text-[#060606] shadow-lg shadow-[#C6A343]/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10"/>
                    </svg>
                </span>
                <span class="mt-0.5 text-[9px] font-bold text-[#C6A343]">Scan QR</span>
            </a>

            <a href="{{ route('contributions.index') }}" wire:navigate
                class="flex flex-col items-center gap-1 py-1 transition active:scale-95 {{ request()->routeIs('contributions.*') ? 'text-[#C6A343]' : 'text-zinc-400' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Kontribusi</span>
            </a>

            <a href="{{ route('profile.show') }}" wire:navigate
                class="flex flex-col items-center gap-1 py-1 transition active:scale-95 {{ request()->routeIs('profile.show') ? 'text-[#C6A343]' : 'text-zinc-400' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profil</span>
            </a>
        </div>
    </nav>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
        }
    </script>
</body>
</html>
