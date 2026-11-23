<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Detail Kegiatan & Panel QR Generator -->
        <div class="space-y-6 rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6">
            <div>
                <span class="font-mono text-xs font-semibold text-[#C6A343]">{{ $event->event_code }}</span>
                <h2 class="mt-1 font-display text-xl font-bold text-[#FFFFFF]">{{ $event->title }}</h2>
                <p class="mt-2 text-xs text-[#A1A1AA]">{{ $event->description }}</p>
            </div>

            <dl class="space-y-2 border-t border-zinc-800 pt-4 text-xs">
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Tanggal</dt>
                    <dd class="text-[#FFFFFF]">{{ $event->event_date->translatedFormat('d F Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Waktu</dt>
                    <dd class="text-[#FFFFFF]">{{ substr($event->start_time, 0, 5) }} - {{ substr($event->end_time, 0, 5) }} WIB</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Lokasi</dt>
                    <dd class="text-[#FFFFFF]">{{ $event->location }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Kehadiran</dt>
                    <dd class="font-bold text-[#C6A343]">{{ $attendances->count() }} / {{ $participantsCount }} Peserta</dd>
                </div>
            </dl>

            @can('generateQr', $event)
                <div class="border-t border-zinc-800 pt-5 space-y-4">
                    <h3 class="font-display text-sm font-bold text-[#FFFFFF]">Generator QR Code Absensi</h3>

                    <div class="flex items-center gap-2 text-xs">
                        <select wire:model="validMinutes" class="flex-1 rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            <option value="15">Berlaku 15 Menit</option>
                            <option value="30">Berlaku 30 Menit</option>
                            <option value="60">Berlaku 1 Jam</option>
                            <option value="120">Berlaku 2 Jam</option>
                            <option value="240">Berlaku 4 Jam</option>
                        </select>
                        <button wire:click="generateQr" type="button"
                            class="rounded-xl bg-[#C6A343] px-4 py-2 font-bold text-[#060606] hover:opacity-90">
                            Generate QR
                        </button>
                    </div>

                    @if ($qrSvgDataUri)
                        <div class="flex flex-col items-center rounded-2xl border border-[#C6A343]/30 bg-white p-4">
                            <img src="{{ $qrSvgDataUri }}" alt="QR Absensi {{ $event->title }}" class="h-56 w-56">
                        </div>

                        <div class="rounded-xl border border-zinc-800 bg-[#060606] p-3 text-center text-xs">
                            <p class="text-[#A1A1AA]">Kode Input Manual Cadangan:</p>
                            <p class="mt-1 font-mono text-lg font-bold tracking-widest text-[#C6A343]">{{ $activeManualCode }}</p>
                            <p class="mt-1 text-[11px] text-[#A1A1AA]">Berlaku hingga: {{ $qrExpiresAt }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button wire:click="downloadQrPng" type="button"
                                class="rounded-xl border border-[#C6A343]/50 bg-[#A80D0D]/10 py-2 font-semibold text-[#C6A343]">
                                Download PNG
                            </button>
                            <button type="button" onclick="window.print()"
                                class="rounded-xl border border-zinc-700 bg-[#060606] py-2 font-semibold text-[#FFFFFF]">
                                Cetak QR
                            </button>
                        </div>

                        <button wire:click="deactivateActiveQr" type="button"
                            class="w-full rounded-xl border border-red-500/30 bg-red-500/10 py-2 text-xs font-semibold text-red-400">
                            Nonaktifkan QR Sekarang
                        </button>
                    @elseif ($activeManualCode)
                        <div class="rounded-xl border border-[#C6A343]/30 bg-[#C6A343]/10 p-3 text-xs text-[#C6A343]">
                            Sesi QR aktif tersedia (Kode Manual: <span class="font-mono font-bold">{{ $activeManualCode }}</span>, berlaku s/d {{ $qrExpiresAt }}). Klik <strong>Generate QR</strong> untuk merotasi dan menampilkan gambar QR baru.
                        </div>
                    @endif
                </div>
            @endcan
        </div>

        <!-- Live Attendance Table -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6 lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">Daftar Kehadiran Real-Time</h3>
                    <p class="text-xs text-[#A1A1AA]">Terurut berdasarkan waktu check-in server (Asia/Jakarta).</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C6A343]/15 px-3 py-1 text-xs font-semibold text-[#C6A343]">
                    {{ $attendances->count() }} Hadir
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-800 text-left text-xs">
                    <thead class="bg-[#060606]/80 text-[11px] font-semibold uppercase text-[#A1A1AA]">
                        <tr>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Nama Anggota</th>
                            <th class="px-4 py-3">Divisi</th>
                            <th class="px-4 py-3">Waktu Check-In</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/70">
                        @forelse ($attendances as $att)
                            <tr>
                                <td class="px-4 py-3 font-mono text-[#FFFFFF]">{{ $att->member->nim }}</td>
                                <td class="px-4 py-3 font-semibold text-[#FFFFFF]">{{ $att->member->full_name }}</td>
                                <td class="px-4 py-3 text-[#C6A343]">{{ $att->member->division?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-[#FFFFFF]">
                                    {{ $att->checked_in_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase {{ $att->status === 'present' ? 'bg-[#C6A343]/15 text-[#C6A343]' : 'bg-[#C6A343]/15 text-[#C6A343]' }}">
                                        {{ $att->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-[#A1A1AA]">
                                    Belum ada anggota yang melakukan check-in pada kegiatan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
