<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-lg font-bold text-[#FFFFFF]">Contribution Tracker</h2>
            <p class="text-xs text-[#A1A1AA]">Catatan pekerjaan nyata, output teknis/non-teknis, serta verifikasi pengurus KUKANG EV.</p>
        </div>

        @can('create', App\Models\Contribution::class)
            <button wire:click="openCreateModal" type="button"
                class="rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] px-4 py-2 text-xs font-bold text-[#060606]">
                + Kirim Laporan Kontribusi
            </button>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 gap-3 rounded-2xl border border-zinc-800 bg-[#0F0F12]/60 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari deskripsi, output, atau NIM..."
            class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">

        <select wire:model.live="filterCategory" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Kategori</option>
            @foreach (App\Models\Contribution::CATEGORIES as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Status</option>
            @foreach (App\Models\Contribution::STATUSES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>

        @if ($isAdmin)
            <select wire:model.live="filterDivision" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
                <option value="">Semua Divisi</option>
                @foreach ($divisions as $div)
                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <!-- List Kontribusi -->
    <div class="space-y-4">
        @forelse ($contributions as $item)
            <div class="rounded-2xl border border-zinc-800/90 bg-[#0F0F12]/85 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-[#A80D0D]/15 px-2.5 py-0.5 text-[11px] font-semibold text-[#C6A343]">
                                {{ $item->category }}
                            </span>
                            <span class="text-xs text-[#A1A1AA]">• {{ $item->event->title }}</span>
                        </div>
                        <h3 class="mt-2 font-display text-sm font-bold text-[#FFFFFF]">
                            {{ $item->member->full_name }}
                            <span class="font-mono text-xs font-normal text-[#A1A1AA]">({{ $item->member->nim }} — {{ $item->member->division?->name ?? 'Umum' }})</span>
                        </h3>
                    </div>

                    <div class="flex items-center gap-2">
                        @php
                            $badgeClass = match ($item->status) {
                                'approved' => 'bg-[#C6A343]/15 text-[#C6A343]',
                                'rejected' => 'bg-red-500/15 text-red-400',
                                'revision_required' => 'bg-[#C6A343]/15 text-[#C6A343]',
                                default => 'bg-zinc-900 text-[#A1A1AA]',
                            };
                        @endphp
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase {{ $badgeClass }}">
                            {{ App\Models\Contribution::STATUSES[$item->status] ?? $item->status }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 border-t border-zinc-800/80 pt-4 text-xs md:grid-cols-3">
                    <div>
                        <p class="font-semibold text-[#A1A1AA]">Deskripsi Pekerjaan</p>
                        <p class="mt-1 text-[#FFFFFF]">{{ $item->work_description }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-[#A1A1AA]">Hasil / Output Pekerjaan</p>
                        <p class="mt-1 text-[#FFFFFF]">{{ $item->work_output }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-[#A1A1AA]">Kendala & Lampiran</p>
                        <p class="mt-1 text-[#FFFFFF]">{{ $item->obstacles ?: 'Tidak ada kendala.' }}</p>
                        @if ($item->documentations->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($item->documentations as $doc)
                                    <a href="{{ route('documentation.download', $doc) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-700 bg-[#060606] px-2 py-1 text-[11px] text-[#C6A343] hover:border-[#C6A343]">
                                        📎 {{ \Illuminate\Support\Str::limit($doc->original_filename, 22) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if ($item->reviewer_notes)
                    <div class="mt-3 rounded-xl border border-[#C6A343]/30 bg-[#C6A343]/10 p-3 text-xs text-[#C6A343]">
                        <strong>Catatan Evaluator ({{ $item->verifier?->name ?? 'Pengurus' }}):</strong> {{ $item->reviewer_notes }}
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between border-t border-zinc-800/80 pt-3 text-[11px] text-[#A1A1AA]">
                    <span>Dikirim: {{ $item->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
                    <div class="space-x-3">
                        @can('update', $item)
                            <button wire:click="openEditModal('{{ $item->id }}')" class="font-semibold text-[#C6A343] hover:underline">
                                Edit / Revisi
                            </button>
                        @endcan
                        @can('verify', $item)
                            <button wire:click="openVerifyModal('{{ $item->id }}')" class="font-semibold text-[#C6A343] hover:underline">
                                Verifikasi Pengurus
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/60 p-8 text-center text-sm text-[#A1A1AA]">
                Belum ada catatan kontribusi yang ditemukan.
            </div>
        @endforelse
    </div>

    {{ $contributions->links() }}

    <!-- Modal Submit / Edit Kontribusi -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6">
                <div class="mb-4 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">
                        {{ $editingContributionId ? 'Perbarui Laporan Kontribusi' : 'Form Laporan Kontribusi Anggota' }}
                    </h3>
                    <button wire:click="$set('showFormModal', false)" class="text-[#A1A1AA]">&times;</button>
                </div>

                @if ($currentMember)
                    <div class="mb-4 grid grid-cols-2 gap-2 rounded-xl border border-zinc-800 bg-[#060606] p-3 text-xs sm:grid-cols-4">
                        <div><span class="block text-[10px] text-[#A1A1AA]">Nama</span><strong class="text-[#FFFFFF]">{{ $currentMember->full_name }}</strong></div>
                        <div><span class="block text-[10px] text-[#A1A1AA]">NIM</span><strong class="font-mono text-[#FFFFFF]">{{ $currentMember->nim }}</strong></div>
                        <div><span class="block text-[10px] text-[#A1A1AA]">Divisi</span><strong class="text-[#C6A343]">{{ $currentMember->division?->name ?? '-' }}</strong></div>
                        <div><span class="block text-[10px] text-[#A1A1AA]">Jabatan</span><strong class="text-[#FFFFFF]">{{ $currentMember->position?->name ?? 'Anggota' }}</strong></div>
                    </div>
                @endif

                <form wire:submit="saveContribution" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Kegiatan Terkait *</label>
                            <select wire:model="event_id" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="">-- Pilih Kegiatan --</option>
                                @foreach ($events as $ev)
                                    <option value="{{ $ev->id }}">{{ $ev->title }} ({{ $ev->event_date->format('d/m/Y') }})</option>
                                @endforeach
                            </select>
                            @error('event_id') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Kategori Kontribusi *</label>
                            <select wire:model="category" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                @foreach (App\Models\Contribution::CATEGORIES as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Deskripsi Pekerjaan *</label>
                        <textarea wire:model="work_description" rows="3" placeholder="Jelaskan aktivitas teknis atau non-teknis yang dikerjakan..."
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                        @error('work_description') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Hasil / Output Pekerjaan *</label>
                        <textarea wire:model="work_output" rows="2" placeholder="Contoh: Skematik PCB BMS selesai diuji, proposal sponsor terkirim ke 5 perusahaan..."
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                        @error('work_output') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Kendala (Opsional)</label>
                        <textarea wire:model="obstacles" rows="2" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Lampiran Dokumentasi (JPG, PNG, WEBP, MP4, PDF — maks 50MB/file)</label>
                        <input wire:model="attachments" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf" class="w-full text-[#A1A1AA]">
                        @error('attachments.*') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showFormModal', false)" class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA]">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606]">Kirim Kontribusi</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Verifikasi Pengurus -->
    @if ($showVerifyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6">
                <h3 class="font-display text-base font-bold text-[#FFFFFF] mb-4">Evaluasi & Verifikasi Kontribusi</h3>
                <form wire:submit="submitVerification" class="space-y-4 text-xs">
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Keputusan Verifikasi *</label>
                        <select wire:model="verifyStatus" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            <option value="approved">Approved (Disetujui)</option>
                            <option value="revision_required">Revision Required (Perlu Revisi)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Catatan Pengurus (Wajib jika Revisi/Ditolak)</label>
                        <textarea wire:model="reviewer_notes" rows="3" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                        @error('reviewer_notes') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showVerifyModal', false)" class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA]">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606]">Simpan Keputusan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
