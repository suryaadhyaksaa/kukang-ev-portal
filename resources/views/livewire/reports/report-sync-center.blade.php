<div class="space-y-6">
    @if (session()->has('status'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/15 px-4 py-3 text-xs font-medium text-[#DFBE63]">
            {{ session('status') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-[#A80D0D]/60 bg-[#A80D0D]/20 px-4 py-3 text-xs font-medium text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @if (! $isGraphConfigured)
        <div class="rounded-xl border border-[#A80D0D]/60 bg-[#A80D0D]/15 px-4 py-3 text-xs text-red-300">
            Konfigurasi Microsoft Graph API (<span class="font-mono">MS_GRAPH_TENANT_ID, CLIENT_ID, CLIENT_SECRET, DRIVE_ID, WORKBOOK_ITEM_ID</span>) belum diisi di <span class="font-mono">.env</span>.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Kartu Kiri: Export Excel Lokal -->
        <div class="ev-card flex flex-col justify-between rounded-2xl p-6">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-[#C6A343]">
                    MAATWEBSITE EXCEL 4.X
                </p>
                <h3 class="mt-1.5 font-display text-lg font-bold text-white">
                    Export Full Workbook (.XLSX)
                </h3>
                <p class="mt-1.5 text-xs leading-relaxed text-zinc-400">
                    Menghasilkan 6 worksheet terstruktur:
                    <span class="font-mono text-[#C6A343]">Ringkasan_Eksekutif, Master_Anggota, Daftar_Kegiatan, Log_Absensi, Log_Kontribusi, Rekap_Per_Divisi.</span>
                </p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-zinc-300">Dari Tanggal (Opsional)</label>
                        <input type="date" wire:model="dateFrom"
                            class="w-full rounded-xl border border-zinc-800 bg-[#060606] px-3.5 py-2.5 text-xs text-white focus:border-[#C6A343] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-zinc-300">Sampai Tanggal (Opsional)</label>
                        <input type="date" wire:model="dateTo"
                            class="w-full rounded-xl border border-zinc-800 bg-[#060606] px-3.5 py-2.5 text-xs text-white focus:border-[#C6A343] focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button type="button" wire:click="downloadReport" wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-gradient-to-r from-[#C6A343] to-[#DFBE63] px-4 py-2.5 text-xs font-bold text-[#060606] shadow-lg shadow-[#C6A343]/20 transition hover:brightness-110 disabled:opacity-50">
                    <span wire:loading.remove wire:target="downloadReport">Download Full Report (.XLSX)</span>
                    <span wire:loading wire:target="downloadReport">Menyiapkan Workbook...</span>
                </button>
            </div>
        </div>

        <!-- Kartu Kanan: Microsoft Graph Cloud Sync -->
        <div class="ev-card flex flex-col justify-between rounded-2xl p-6">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-red-400">
                        MICROSOFT GRAPH REST API
                    </p>
                    <span class="rounded-full border px-2.5 py-0.5 font-mono text-[9px] font-semibold uppercase {{ $isGraphConfigured ? 'border-[#C6A343]/40 bg-[#C6A343]/15 text-[#C6A343]' : 'border-[#A80D0D]/50 bg-[#A80D0D]/20 text-red-300' }}">
                        {{ $isGraphConfigured ? 'READY' : 'ENV REQUIRED' }}
                    </span>
                </div>

                <h3 class="mt-1.5 font-display text-lg font-bold text-white">
                    Sync ke Excel Online (OneDrive / SharePoint)
                </h3>
                <p class="mt-1.5 text-xs leading-relaxed text-zinc-400">
                    Mengirim data kehadiran dan kontribusi terbaru secara asinkron melalui Laravel Queue ke tabel
                    <span class="font-mono text-zinc-200">TblAbsensi</span> dan <span class="font-mono text-zinc-200">TblKontribusi</span> pada Microsoft Excel Online.
                </p>
            </div>

            <div class="mt-6">
                <button type="button" wire:click="triggerCloudSync" wire:loading.attr="disabled"
                    class="w-full rounded-xl border border-[#A80D0D]/70 bg-[#A80D0D]/25 px-4 py-2.5 text-xs font-semibold text-red-200 transition hover:bg-[#A80D0D]/40 hover:text-white disabled:opacity-50">
                    <span wire:loading.remove wire:target="triggerCloudSync">Jalankan Sinkronisasi Cloud Sekarang</span>
                    <span wire:loading wire:target="triggerCloudSync">Mengantrekan Sinkronisasi...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Log Sinkronisasi -->
    <div class="ev-card rounded-2xl p-6">
        <h3 class="font-display text-base font-bold text-white">Log Sinkronisasi Microsoft Graph</h3>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 font-mono text-[10px] uppercase tracking-wider text-zinc-400">
                        <th class="pb-3 pr-4">Waktu</th>
                        <th class="pb-3 pr-4">Tipe Sync</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Baris Tersinkron</th>
                        <th class="pb-3 pr-4">Pemicu</th>
                        <th class="pb-3">Catatan / Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/70 text-zinc-300">
                    @forelse ($syncLogs as $log)
                        <tr class="hover:bg-zinc-900/50">
                            <td class="py-3 pr-4 font-mono text-[11px]">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                            <td class="py-3 pr-4">{{ $log->sync_type }}</td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full px-2 py-0.5 font-mono text-[10px] uppercase {{ $log->status === 'success' ? 'bg-[#C6A343]/15 text-[#C6A343]' : 'bg-[#A80D0D]/25 text-red-300' }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 font-mono">{{ $log->rows_synced }}</td>
                            <td class="py-3 pr-4">{{ $log->triggeredBy?->name ?? 'System' }}</td>
                            <td class="py-3 text-zinc-400">{{ $log->error_message ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-500">
                                Belum ada riwayat sinkronisasi Microsoft Graph.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
