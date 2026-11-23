<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('members.index') }}" class="text-xs font-semibold text-[#C6A343] hover:underline">
            &larr; Kembali ke Daftar Anggota
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kartu Identitas -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6">
            <div class="flex items-center gap-4">
                @if ($member->avatar_path)
                    <img src="{{ asset('storage/'.$member->avatar_path) }}" alt="{{ $member->full_name }}"
                        class="h-16 w-16 rounded-2xl object-cover ring-2 ring-[#C6A343]/40">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#C6A343]/15 font-display text-xl font-bold text-[#C6A343]">
                        {{ strtoupper(substr($member->full_name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h2 class="font-display text-lg font-bold text-[#FFFFFF]">{{ $member->full_name }}</h2>
                    <p class="font-mono text-xs text-[#C6A343]">NIM: {{ $member->nim }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 border-t border-zinc-800 pt-4 text-xs">
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Divisi</dt>
                    <dd class="font-semibold text-[#FFFFFF]">{{ $member->division?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Jabatan</dt>
                    <dd class="font-semibold text-[#FFFFFF]">{{ $member->position?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Angkatan</dt>
                    <dd class="text-[#FFFFFF]">{{ $member->cohort_year }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Email</dt>
                    <dd class="text-[#FFFFFF]">{{ $member->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">No. HP</dt>
                    <dd class="text-[#FFFFFF]">{{ $member->phone ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Bergabung</dt>
                    <dd class="text-[#FFFFFF]">{{ $member->joined_at->translatedFormat('d F Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Timeline Riwayat Aktivitas -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6 lg:col-span-2">
            <h3 class="font-display text-base font-bold text-[#FFFFFF] mb-4">Riwayat Aktivitas Anggota</h3>
            <div class="space-y-3">
                @forelse ($activities as $log)
                    <div class="flex items-start justify-between rounded-xl border border-zinc-800/80 bg-[#060606]/70 p-3.5 text-xs">
                        <div>
                            <p class="font-medium text-[#FFFFFF]">{{ $log->description }}</p>
                            <span class="font-mono text-[11px] text-[#C6A343]">{{ $log->action }}</span>
                        </div>
                        <span class="text-[11px] text-[#A1A1AA]">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-[#A1A1AA]">Belum ada riwayat aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
