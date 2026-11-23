<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/15 px-4 py-3 text-xs font-medium text-[#DFBE63]">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-[#A80D0D]/60 bg-[#A80D0D]/20 px-4 py-3 text-xs font-medium text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Banner -->
    <div class="flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-lg font-bold text-[#FFFFFF]">Agenda &amp; Kegiatan KUKANG EV</h2>
            <p class="text-xs text-[#A1A1AA]">Kelola jadwal rapat, workshop teknis, uji kendaraan EV, dan sesi absensi QR.</p>
        </div>

        @can('create', App\Models\Event::class)
            <button wire:click="openCreateModal" type="button"
                class="shrink-0 rounded-xl bg-gradient-to-r from-[#C6A343] to-[#DFBE63] px-4 py-2 text-xs font-bold text-[#060606] transition hover:brightness-110">
                + Buat Kegiatan Baru
            </button>
        @endcan
    </div>

    <!-- Filter -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode atau judul kegiatan..."
            class="rounded-xl border border-zinc-700/80 bg-[#0F0F12] px-3.5 py-2 text-xs text-[#FFFFFF]">
        <select wire:model.live="filterStatus"
            class="rounded-xl border border-zinc-700/80 bg-[#0F0F12] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Status</option>
            <option value="upcoming">Upcoming</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <!-- Event Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse ($events as $event)
            <div wire:key="event-{{ $event->id }}"
                class="flex flex-col justify-between rounded-2xl border border-zinc-800/90 bg-[#0F0F12]/85 p-5 transition hover:border-zinc-700">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-[11px] font-semibold text-[#C6A343]">{{ $event->event_code }}</span>
                        <span class="rounded-full bg-zinc-900 px-2.5 py-0.5 text-[11px] font-semibold uppercase text-[#C6A343]">
                            {{ $event->status }}
                        </span>
                    </div>
                    <h3 class="mt-2 font-display text-base font-bold text-[#FFFFFF]">{{ $event->title }}</h3>
                    <p class="mt-1 line-clamp-2 text-xs text-[#A1A1AA]">{{ $event->description }}</p>

                    <div class="mt-4 space-y-1 text-xs text-[#A1A1AA]">
                        <p>Tanggal: <span class="text-[#FFFFFF]">{{ $event->event_date->translatedFormat('d M Y') }}
                                ({{ substr($event->start_time, 0, 5) }} - {{ substr($event->end_time, 0, 5) }} WIB)</span></p>
                        <p>Lokasi: <span class="text-[#FFFFFF]">{{ $event->location }}</span></p>
                        <p>Penyelenggara: <span class="text-[#C6A343]">{{ $event->division?->name ?? 'Seluruh Tim' }}</span></p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-2 border-t border-zinc-800 pt-3 text-xs">
                    <span class="whitespace-nowrap font-semibold text-[#C6A343]">
                        Hadir: {{ $event->attendances_count }} / {{ $event->participants_count }} Peserta
                    </span>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        @role('super_admin|admin')
                            <button type="button"
                                wire:click="deleteEvent('{{ $event->id }}')"
                                wire:confirm="Yakin ingin menghapus kegiatan '{{ $event->title }}'?"
                                class="font-medium text-red-400 transition hover:text-red-300">
                                Hapus
                            </button>
                        @endrole

                        @can('update', $event)
                            <button type="button" wire:click="openEditModal('{{ $event->id }}')"
                                class="text-[#A1A1AA] transition hover:text-[#FFFFFF]">Edit</button>
                        @endcan

                        <a href="{{ route('events.show', $event) }}"
                            class="font-semibold text-[#C6A343] hover:underline">
                            Detail &amp; QR &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-zinc-800 bg-[#0F0F12]/60 p-8 text-center text-sm text-[#A1A1AA]">
                Belum ada kegiatan terdaftar.
            </div>
        @endforelse
    </div>

    {{ $events->links() }}

    <!-- Modal Form Kegiatan -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6">
                <div class="mb-4 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">
                        {{ $editingEventId ? 'Edit Kegiatan' : 'Buat Kegiatan Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-[#A1A1AA] hover:text-white">&times;</button>
                </div>

                <form wire:submit="saveEvent" class="space-y-4 text-xs">
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Nama Kegiatan *</label>
                        <input wire:model="title" type="text"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        @error('title')
                            <span class="text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Deskripsi</label>
                        <textarea wire:model="description" rows="2"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Tanggal *</label>
                            <input wire:model="event_date" type="date"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Mulai (WIB) *</label>
                            <input wire:model="start_time" type="time"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Selesai (WIB) *</label>
                            <input wire:model="end_time" type="time"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Lokasi *</label>
                            <input wire:model="location" type="text"
                                placeholder="Workshop KUKANG EV / Gedung E ITERA"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Status *</label>
                            <select wire:model="status"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Divisi Penyelenggara</label>
                            <select wire:model="division_id"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="">Seluruh Tim (Umum)</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Penanggung Jawab (PIC)</label>
                            <select wire:model="pic_member_id"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="">-- Pilih PIC --</option>
                                @foreach ($activeMembers as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->nim }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Pembukaan Absensi (Opsional)</label>
                            <input wire:model="attendance_open_at" type="datetime-local"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Penutupan Absensi (Opsional)</label>
                            <input wire:model="attendance_close_at" type="datetime-local"
                                class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Target Peserta Kegiatan</label>
                        <select wire:model.live="participantMode"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            <option value="all_active">Seluruh Anggota Aktif</option>
                            <option value="division_only">Khusus Anggota Divisi Penyelenggara</option>
                            <option value="manual">Pilih Peserta Secara Manual</option>
                        </select>
                    </div>

                    @if ($participantMode === 'manual')
                        <div class="max-h-36 space-y-1.5 overflow-y-auto rounded-xl border border-zinc-800 bg-[#060606] p-3">
                            @foreach ($activeMembers as $m)
                                <label class="flex items-center gap-2 text-[#FFFFFF]">
                                    <input type="checkbox" wire:model="selectedMemberIds"
                                        value="{{ $m->id }}" class="rounded border-zinc-700 text-[#C6A343]">
                                    <span>{{ $m->nim }} — {{ $m->full_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA]">Batal</button>
                        <button type="submit"
                            class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606]">Simpan Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
