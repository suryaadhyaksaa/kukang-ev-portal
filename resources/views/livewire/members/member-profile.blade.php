<div class="space-y-6">
    <!-- Header Profil -->
    <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/90 p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if ($member->avatar_path)
                    <img src="{{ asset('storage/'.$member->avatar_path) }}" alt="{{ $member->full_name }}"
                        class="h-20 w-20 rounded-2xl object-cover ring-2 ring-[#C6A343]">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-[#C6A343]/20 to-[#C6A343]/20 font-display text-2xl font-bold text-[#C6A343] ring-1 ring-[#C6A343]/40">
                        {{ strtoupper(substr($member->full_name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <span class="font-mono text-xs font-semibold text-[#C6A343]">NIM: {{ $member->nim }} • Angkatan {{ $member->cohort_year }}</span>
                    <h2 class="font-display text-xl font-bold text-[#FFFFFF]">{{ $member->full_name }}</h2>
                    <p class="text-xs text-[#A1A1AA]">
                        {{ $member->division?->name ?? 'Divisi Belum Ditetapkan' }} • {{ $member->position?->name ?? 'Anggota' }}
                    </p>
                </div>
            </div>

            <span class="self-start rounded-full bg-[#C6A343]/15 px-3.5 py-1 text-xs font-semibold uppercase text-[#C6A343]">
                Status: {{ $member->status }}
            </span>
        </div>

        <!-- KPI Statistik Anggota -->
        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-zinc-800 pt-6 lg:grid-cols-5">
            <div class="rounded-xl bg-[#060606]/80 p-4">
                <p class="text-[11px] text-[#A1A1AA]">Total Kehadiran</p>
                <p class="mt-1 font-display text-2xl font-bold text-[#FFFFFF]">{{ $totalAttendances }}</p>
            </div>
            <div class="rounded-xl bg-[#060606]/80 p-4">
                <p class="text-[11px] text-[#A1A1AA]">Persentase Hadir</p>
                <p class="mt-1 font-display text-2xl font-bold text-[#C6A343]">{{ $attendancePercentage }}%</p>
                <p class="text-[10px] text-[#A1A1AA]">dari {{ $registeredEventsCount }} agenda wajib</p>
            </div>
            <div class="rounded-xl bg-[#060606]/80 p-4">
                <p class="text-[11px] text-[#A1A1AA]">Total Kontribusi</p>
                <p class="mt-1 font-display text-2xl font-bold text-[#C6A343]">{{ $totalContributions }}</p>
            </div>
            <div class="rounded-xl bg-[#060606]/80 p-4">
                <p class="text-[11px] text-[#A1A1AA]">Kontribusi Approved</p>
                <p class="mt-1 font-display text-2xl font-bold text-[#C6A343]">{{ $approvedContributions }}</p>
            </div>
            <div class="rounded-xl bg-[#060606]/80 p-4">
                <p class="text-[11px] text-[#A1A1AA]">Dokumentasi Diunggah</p>
                <p class="mt-1 font-display text-2xl font-bold text-[#FFFFFF]">{{ $totalDocumentations }}</p>
            </div>
        </div>
    </div>

    <!-- Timeline Riwayat Aktivitas -->
    <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6">
        <h3 class="font-display text-base font-bold text-[#FFFFFF] mb-4">Timeline Riwayat Kegiatan, Check-In & Verifikasi</h3>
        <div class="space-y-3">
            @forelse ($timeline as $item)
                <div class="flex items-start justify-between rounded-xl border border-zinc-800/80 bg-[#060606]/70 p-3.5 text-xs">
                    <div>
                        <p class="font-medium text-[#FFFFFF]">{{ $item->description }}</p>
                        <span class="font-mono text-[11px] text-[#C6A343]">{{ $item->action }}</span>
                    </div>
                    <span class="whitespace-nowrap text-[11px] text-[#A1A1AA]">
                        {{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                    </span>
                </div>
            @empty
                <p class="text-xs text-[#A1A1AA]">Belum ada riwayat aktivitas.</p>
            @endforelse
        </div>
    </div>
</div>
