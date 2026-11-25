<div class="space-y-4 sm:space-y-6">
    <!-- Hero Telemetry Banner -->
    <div class="ev-card ev-telemetry-grid relative overflow-hidden rounded-2xl p-4 sm:p-6 lg:p-7">
        <div class="pointer-events-none absolute -right-16 -top-16 h-60 w-60 rounded-full bg-[#A80D0D]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/4 h-52 w-52 rounded-full bg-[#C6A343]/15 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-6">
            <div class="min-w-0 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#C6A343]/35 bg-[#C6A343]/10 px-2.5 py-1 sm:px-3">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-[#C6A343] animate-pulse"></span>
                    <span class="truncate font-mono text-[9px] font-semibold uppercase tracking-widest text-[#C6A343] sm:text-[10px]">
                        Live Telemetry &amp; Operational System
                    </span>
                </div>
                <h2 class="mt-2.5 font-display text-xl font-bold leading-snug tracking-tight text-white sm:mt-3 sm:text-2xl lg:text-[26px]">
                    Selamat Datang, {{ auth()->user()?->name ?? 'Pengguna' }}
                </h2>
                <p class="mt-1.5 text-xs leading-relaxed text-zinc-400 sm:text-[13px]">
                    Pantau metrik kehadiran anggota secara real-time, progres kontribusi teknis maupun non-teknis, dan arsip dokumentasi KUKANG EV ITERA.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2.5 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
                <a href="{{ route('attendance.scanner') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#C6A343] to-[#DFBE63] px-4 py-2.5 text-xs font-bold text-[#060606] shadow-lg shadow-[#C6A343]/20 transition hover:brightness-110 sm:w-auto">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2"/>
                    </svg>
                    <span>Scan QR Absensi</span>
                </a>
                <a href="{{ route('contributions.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-900/90 px-4 py-2.5 text-xs font-semibold text-white transition hover:border-[#C6A343]/60 hover:text-[#C6A343] sm:w-auto">
                    <span>+ Input Kontribusi</span>
                </a>
                @if ($isAdmin)
                    <a href="{{ route('reports.index') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#A80D0D]/60 bg-[#A80D0D]/20 px-4 py-2.5 text-xs font-semibold text-red-200 transition hover:bg-[#A80D0D]/35 sm:w-auto">
                        <span>Export &amp; Sync Excel</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Telemetry Cards (2 Kolom di HP, 4 Kolom di Desktop) -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="ev-card ev-card-hover min-w-0 rounded-2xl p-3.5 sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-[11px] font-medium text-zinc-400 sm:text-xs">Anggota Aktif</p>
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#C6A343]/15 text-[#C6A343] ring-1 ring-[#C6A343]/35 sm:h-8 sm:w-8">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2.5 font-display text-2xl font-bold tabular-nums text-white sm:mt-3 sm:text-3xl">{{ $activeMembersCount }}</p>
            <p class="mt-1 flex items-center gap-1.5 truncate text-[10px] text-[#C6A343] sm:mt-1.5 sm:text-[11px]">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#C6A343]"></span>
                <span class="truncate">Terdaftar di ITERA</span>
            </p>
        </div>

        <!-- Card 2 -->
        <div class="ev-card ev-card-hover min-w-0 rounded-2xl p-3.5 sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-[11px] font-medium text-zinc-400 sm:text-xs">Rata-Rata Hadir</p>
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#A80D0D]/20 text-red-400 ring-1 ring-[#A80D0D]/40 sm:h-8 sm:w-8">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2.5 font-display text-2xl font-bold tabular-nums text-[#C6A343] sm:mt-3 sm:text-3xl">{{ $avgAttendanceRate }}%</p>
            <p class="mt-1 truncate text-[10px] text-zinc-400 sm:mt-1.5 sm:text-[11px]">
                <span class="font-semibold text-white">{{ $totalAttendancesCount }}</span> / {{ $totalEventsCount }} agenda
            </p>
        </div>

        <!-- Card 3 -->
        <div class="ev-card ev-card-hover min-w-0 rounded-2xl p-3.5 sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-[11px] font-medium text-zinc-400 sm:text-xs">Kontribusi Approved</p>
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#C6A343]/15 text-[#C6A343] ring-1 ring-[#C6A343]/35 sm:h-8 sm:w-8">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2.5 font-display text-2xl font-bold tabular-nums text-white sm:mt-3 sm:text-3xl">{{ $approvedContributionsCount }}</p>
            <p class="mt-1 flex items-center gap-1.5 truncate text-[10px] text-red-400 sm:mt-1.5 sm:text-[11px]">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#DC2626]"></span>
                <span class="truncate">{{ $pendingContributionsCount }} menunggu</span>
            </p>
        </div>

        <!-- Card 4 -->
        <div class="ev-card ev-card-hover min-w-0 rounded-2xl p-3.5 sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-[11px] font-medium text-zinc-400 sm:text-xs">Arsip Dokumentasi</p>
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-zinc-800 text-zinc-200 ring-1 ring-zinc-700 sm:h-8 sm:w-8">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2.5 font-display text-2xl font-bold tabular-nums text-white sm:mt-3 sm:text-3xl">{{ $totalDocumentationsCount }}</p>
            <p class="mt-1 truncate text-[10px] text-zinc-400 sm:mt-1.5 sm:text-[11px]">
                <span class="font-semibold text-white">{{ $upcomingEventsCount }}</span> agenda aktif
            </p>
        </div>
    </div>

    <!-- Visualisasi Analitik Chart.js -->
    <div wire:ignore class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3"
        x-data="{
            initCharts() {
                if (!window.Chart) return;

                const trendCanvas = document.getElementById('trendChart');
                if (trendCanvas) {
                    const ctx = trendCanvas.getContext('2d');
                    const goldGrad = ctx.createLinearGradient(0, 0, 0, 260);
                    goldGrad.addColorStop(0, 'rgba(198, 163, 67, 0.32)');
                    goldGrad.addColorStop(1, 'rgba(198, 163, 67, 0.0)');

                    const redGrad = ctx.createLinearGradient(0, 0, 0, 260);
                    redGrad.addColorStop(0, 'rgba(220, 38, 38, 0.28)');
                    redGrad.addColorStop(1, 'rgba(168, 13, 13, 0.0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @js($trendLabels),
                            datasets: [
                                {
                                    label: 'Check-In Absensi',
                                    data: @js($trendAttendances),
                                    borderColor: '#C6A343',
                                    backgroundColor: goldGrad,
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#C6A343',
                                    pointBorderColor: '#060606',
                                    pointBorderWidth: 2,
                                    pointRadius: 3.5,
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Laporan Kontribusi',
                                    data: @js($trendContributions),
                                    borderColor: '#DC2626',
                                    backgroundColor: redGrad,
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#DC2626',
                                    pointBorderColor: '#060606',
                                    pointBorderWidth: 2,
                                    pointRadius: 3.5,
                                    tension: 0.4,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: { color: '#E4E4E7', usePointStyle: true, boxWidth: 8, font: { family: 'Inter', size: 11 } }
                                },
                                tooltip: {
                                    backgroundColor: '#121216',
                                    titleColor: '#FFFFFF',
                                    bodyColor: '#D4D4D8',
                                    borderColor: 'rgba(198, 163, 67, 0.35)',
                                    borderWidth: 1,
                                    padding: 10,
                                    cornerRadius: 10
                                }
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#A1A1AA', font: { family: 'JetBrains Mono', size: 10 } },
                                    grid: { color: 'rgba(255,255,255,0.05)' }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#A1A1AA', precision: 0, font: { family: 'JetBrains Mono', size: 10 } },
                                    grid: { color: 'rgba(255,255,255,0.05)' }
                                }
                            }
                        }
                    });
                }

                const divCanvas = document.getElementById('divisionChart');
                if (divCanvas) {
                    new Chart(divCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: @js($divisionLabels),
                            datasets: [
                                {
                                    label: 'Anggota Aktif',
                                    data: @js($divisionMemberCounts),
                                    backgroundColor: '#C6A343',
                                    borderRadius: 6,
                                    barPercentage: 0.65
                                },
                                {
                                    label: 'Total Kontribusi',
                                    data: @js($divisionContributionCounts),
                                    backgroundColor: '#A80D0D',
                                    borderRadius: 6,
                                    barPercentage: 0.65
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: { color: '#E4E4E7', usePointStyle: true, boxWidth: 8, font: { family: 'Inter', size: 11 } }
                                },
                                tooltip: {
                                    backgroundColor: '#121216',
                                    titleColor: '#FFFFFF',
                                    bodyColor: '#D4D4D8',
                                    borderColor: 'rgba(198, 163, 67, 0.35)',
                                    borderWidth: 1,
                                    padding: 10,
                                    cornerRadius: 10
                                }
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#A1A1AA', font: { family: 'JetBrains Mono', size: 10 } },
                                    grid: { display: false }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#A1A1AA', precision: 0, font: { family: 'JetBrains Mono', size: 10 } },
                                    grid: { color: 'rgba(255,255,255,0.05)' }
                                }
                            }
                        }
                    });
                }
            }
        }"
        x-init="initCharts()">

        <div class="ev-card min-w-0 rounded-2xl p-4 sm:p-6 lg:col-span-2">
            <div class="mb-3 sm:mb-4">
                <h3 class="font-display text-sm font-bold text-white sm:text-base">Tren Absensi &amp; Kontribusi (6 Bulan Terakhir)</h3>
                <p class="mt-0.5 text-[11px] text-zinc-400 sm:text-xs">Perbandingan partisipasi kehadiran dan output kerja anggota per bulan.</p>
            </div>
            <div class="relative h-56 w-full sm:h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="ev-card min-w-0 rounded-2xl p-4 sm:p-6">
            <div class="mb-3 sm:mb-4">
                <h3 class="font-display text-sm font-bold text-white sm:text-base">Distribusi per Divisi</h3>
                <p class="mt-0.5 text-[11px] text-zinc-400 sm:text-xs">Anggota aktif vs kontribusi tercatat.</p>
            </div>
            <div class="relative h-56 w-full sm:h-64">
                <canvas id="divisionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Live Check-In Feed & Antrean Kontribusi -->
    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        <!-- Live Check-in -->
        <div class="ev-card min-w-0 rounded-2xl p-4 sm:p-6">
            <div class="mb-3 flex items-center justify-between gap-2 sm:mb-4">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-[#C6A343]"></span>
                    <h3 class="truncate font-display text-sm font-bold text-white sm:text-base">Feed Check-In Terbaru</h3>
                </div>
                <a href="{{ route('events.index') }}" class="shrink-0 text-[11px] font-medium text-[#C6A343] transition hover:text-white sm:text-xs">Semua &rarr;</a>
            </div>
            <div class="space-y-2.5 text-xs sm:space-y-3">
                @forelse ($latestCheckIns as $att)
                    <div class="flex items-center justify-between gap-2.5 rounded-xl border border-zinc-800/90 bg-[#060606]/90 px-3 py-2.5 transition hover:border-[#C6A343]/40 sm:px-4 sm:py-3">
                        <div class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#C6A343]/15 font-display text-[11px] font-bold text-[#C6A343]">
                                {{ strtoupper(substr($att->member?->full_name ?? 'AG', 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-white">
                                    {{ $att->member?->full_name ?? 'Anggota Dihapus' }}
                                    <span class="font-mono text-[10px] font-normal text-[#C6A343] sm:text-[11px]">({{ $att->member?->nim ?? '-' }})</span>
                                </p>
                                <p class="truncate text-[11px] text-zinc-400">{{ $att->event?->title ?? 'Agenda Dihapus' }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="block font-mono text-[10px] font-semibold text-[#C6A343] sm:text-[11px]">
                                {{ $att->checked_in_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-' }} WIB
                            </span>
                            <span class="mt-0.5 inline-block rounded bg-zinc-800 px-1.5 py-0.5 font-mono text-[9px] uppercase text-zinc-300">
                                {{ $att->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-800 py-8 text-center text-zinc-500">
                        Belum ada aktivitas check-in tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Kontribusi Terbaru -->
        <div class="ev-card min-w-0 rounded-2xl p-4 sm:p-6">
            <div class="mb-3 flex items-center justify-between gap-2 sm:mb-4">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-[#DC2626]"></span>
                    <h3 class="truncate font-display text-sm font-bold text-white sm:text-base">
                        {{ $isAdmin ? 'Kontribusi Terbaru & Verifikasi' : 'Riwayat Kontribusi Anda' }}
                    </h3>
                </div>
                <a href="{{ route('contributions.index') }}" class="shrink-0 text-[11px] font-medium text-[#C6A343] transition hover:text-white sm:text-xs">Semua &rarr;</a>
            </div>
            <div class="space-y-2.5 text-xs sm:space-y-3">
                @forelse ($recentContributions as $cont)
                    <div class="flex items-center justify-between gap-2.5 rounded-xl border border-zinc-800/90 bg-[#060606]/90 px-3 py-2.5 transition hover:border-[#A80D0D]/50 sm:px-4 sm:py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-white">
                                {{ $cont->member?->full_name ?? 'Anggota Dihapus' }}
                                <span class="text-zinc-600">•</span>
                                <span class="text-[#C6A343]">{{ $cont->category }}</span>
                            </p>
                            <p class="mt-0.5 truncate text-[11px] text-zinc-400">{{ $cont->work_output }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 font-mono text-[9px] font-semibold uppercase sm:px-2.5 sm:py-1 sm:text-[10px] {{ $cont->status === 'approved' ? 'bg-[#C6A343]/15 text-[#C6A343] ring-1 ring-[#C6A343]/40' : 'bg-[#A80D0D]/20 text-red-300 ring-1 ring-[#A80D0D]/50' }}">
                            {{ $cont->status }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-800 py-8 text-center text-zinc-500">
                        Belum ada laporan kontribusi.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
