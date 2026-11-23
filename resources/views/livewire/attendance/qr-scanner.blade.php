<div x-data="kukangQrScanner()" class="mx-auto max-w-2xl space-y-6">
    @if ($successMessage)
        <div class="rounded-2xl border border-[#C6A343]/50 bg-[#C6A343]/15 p-4 text-sm font-medium text-[#C6A343]">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-2xl border border-red-500/50 bg-red-500/15 p-4 text-sm text-red-400">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Layar Konfirmasi Identitas & Kegiatan Sebelum Check-In -->
    @if ($showConfirmation)
        <div class="rounded-2xl border border-[#C6A343]/50 bg-[#0F0F12] p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h2 class="font-display text-base font-bold text-[#C6A343]">Konfirmasi Identitas & Kegiatan</h2>
                <span class="font-mono text-xs text-[#A1A1AA]">{{ $previewData['event_code'] ?? '' }}</span>
            </div>

            <dl class="space-y-2.5 text-xs">
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Nama Anggota</dt>
                    <dd class="font-bold text-[#FFFFFF]">{{ $previewData['full_name'] ?? '' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">NIM</dt>
                    <dd class="font-mono text-[#FFFFFF]">{{ $previewData['nim'] ?? '' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Divisi / Jabatan</dt>
                    <dd class="text-[#FFFFFF]">{{ $previewData['division'] ?? '' }} — {{ $previewData['position'] ?? '' }}</dd>
                </div>
                <div class="border-t border-zinc-800 pt-2 flex justify-between">
                    <dt class="text-[#A1A1AA]">Kegiatan</dt>
                    <dd class="font-bold text-[#C6A343]">{{ $previewData['event_title'] ?? '' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[#A1A1AA]">Tanggal & Lokasi</dt>
                    <dd class="text-[#FFFFFF]">{{ $previewData['event_date'] ?? '' }} • {{ $previewData['location'] ?? '' }}</dd>
                </div>
            </dl>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button wire:click="cancelConfirmation" type="button"
                    class="rounded-xl border border-zinc-700 py-2.5 text-xs font-semibold text-[#A1A1AA]">
                    Batal
                </button>
                <button wire:click="confirmCheckIn" type="button"
                    class="rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] py-2.5 text-xs font-bold text-[#060606]">
                    CHECK IN SEKARANG
                </button>
            </div>
        </div>
    @else
        <!-- Kamera Scanner QR -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/90 p-6 space-y-5">
            <div>
                <h2 class="font-display text-lg font-bold text-[#FFFFFF]">Pemindai QR Code Absensi</h2>
                <p class="text-xs text-[#A1A1AA]">Arahkan kamera smartphone ke QR Code yang ditampilkan pengurus di lokasi kegiatan.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-[#060606]">
                <div id="qr-reader" class="w-full min-h-[260px]"></div>
            </div>

            <template x-if="cameraError">
                <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 p-3 text-xs text-[#C6A343]" x-text="cameraError"></div>
            </template>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="startScanner()" :disabled="scanning"
                    class="rounded-xl bg-[#C6A343] py-2.5 text-xs font-bold text-[#060606] disabled:opacity-40">
                    Start Scan Kamera
                </button>
                <button type="button" @click="stopScanner()" :disabled="!scanning"
                    class="rounded-xl border border-zinc-700 bg-[#060606] py-2.5 text-xs font-semibold text-[#FFFFFF] disabled:opacity-40">
                    Stop Scan
                </button>
            </div>

            <!-- Input Kode Manual Cadangan -->
            <form @submit.prevent="$wire.verifyScannedToken(manualInput, 'manual_code')" class="border-t border-zinc-800 pt-4 space-y-2">
                <label class="block text-xs font-medium text-[#A1A1AA]">Atau Masukkan Kode Manual 8-Digit</label>
                <div class="flex gap-2">
                    <input x-model="manualInput" type="text" placeholder="Contoh: A9F3K2L8"
                        class="flex-1 rounded-xl border border-zinc-700 bg-[#060606] px-3.5 py-2 font-mono text-xs uppercase text-[#FFFFFF]">
                    <button type="submit" class="rounded-xl bg-[#A80D0D] px-4 py-2 text-xs font-bold text-white">
                        Verifikasi
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Riwayat Kehadiran Pribadi Terbaru -->
    <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/80 p-5">
        <h3 class="font-display text-sm font-bold text-[#FFFFFF] mb-3">Riwayat Check-In Terbaru Anda</h3>
        <div class="space-y-2 text-xs">
            @forelse ($recentAttendances as $item)
                <div class="flex items-center justify-between rounded-xl bg-[#060606]/70 px-3.5 py-2.5">
                    <div>
                        <p class="font-semibold text-[#FFFFFF]">{{ $item->event->title }}</p>
                        <p class="text-[11px] text-[#A1A1AA]">{{ $item->checked_in_at->timezone('Asia/Jakarta')->format('d M Y • H:i:s') }} WIB</p>
                    </div>
                    <span class="rounded-full bg-[#C6A343]/15 px-2.5 py-0.5 text-[11px] font-semibold uppercase text-[#C6A343]">
                        {{ $item->status }}
                    </span>
                </div>
            @empty
                <p class="text-[#A1A1AA]">Anda belum memiliki riwayat kehadiran.</p>
            @endforelse
        </div>
    </div>
</div>
