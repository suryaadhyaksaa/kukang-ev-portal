<div class="space-y-6">
    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('success') }}
        </div>
    @endif
    @if (session('warning'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Action & Filter Header -->
    <div class="flex flex-col gap-4 rounded-2xl border border-zinc-800/90 bg-[#0F0F12]/80 p-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="font-display text-lg font-bold text-[#FFFFFF]">Direktori Anggota KUKANG EV ITERA</h2>
            <p class="text-xs text-[#A1A1AA]">Kelola data anggota aktif, penempatan divisi, jabatan, serta sinkronisasi Excel.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('divisions.index') }}"
                class="rounded-xl border border-zinc-700 bg-[#060606] px-3.5 py-2 text-xs font-semibold text-[#FFFFFF] transition hover:border-[#C6A343] hover:text-[#C6A343]">
                Kelola Divisi & Jabatan
            </a>

            <button wire:click="$set('showImportModal', true)" type="button"
                class="rounded-xl border border-zinc-700 bg-[#060606] px-3.5 py-2 text-xs font-semibold text-[#FFFFFF] transition hover:border-[#C6A343] hover:text-[#C6A343]">
                Import Excel
            </button>

            <button wire:click="exportExcel" type="button"
                class="rounded-xl border border-[#C6A343]/40 bg-[#A80D0D]/10 px-3.5 py-2 text-xs font-semibold text-[#C6A343] transition hover:bg-[#DC2626]/20">
                Export .XLSX
            </button>

            <button wire:click="openCreateModal" type="button"
                class="rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] px-4 py-2 text-xs font-bold text-[#060606] shadow-lg shadow-[#C6A343]/15 transition hover:opacity-95">
                + Tambah Anggota
            </button>
        </div>
    </div>

    <!-- Search & Multi-Filter Bar -->
    <div class="grid grid-cols-1 gap-3 rounded-2xl border border-zinc-800/80 bg-[#0F0F12]/60 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari NIM, nama, atau email..."
            class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF] placeholder-[#A1A1AA] focus:border-[#C6A343] focus:outline-none">

        <select wire:model.live="filterDivision"
            class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            <option value="">Semua Divisi</option>
            @foreach ($divisions as $div)
                <option value="{{ $div->id }}">{{ $div->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterCohort"
            class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            <option value="">Semua Angkatan</option>
            @foreach ($cohorts as $year)
                <option value="{{ $year }}">Angkatan {{ $year }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus"
            class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
            <option value="alumni">Alumni</option>
        </select>
    </div>

    <!-- Members Data Table -->
    <div class="overflow-hidden rounded-2xl border border-zinc-800/90 bg-[#0F0F12]/80">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-800 text-left text-xs">
                <thead class="bg-[#060606]/80 text-[11px] font-semibold uppercase tracking-wider text-[#A1A1AA]">
                    <tr>
                        <th class="px-5 py-3.5">Anggota</th>
                        <th class="px-5 py-3.5">NIM / Angkatan</th>
                        <th class="px-5 py-3.5">Divisi & Jabatan</th>
                        <th class="px-5 py-3.5">Role Sistem</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/70">
                    @forelse ($members as $member)
                        <tr class="transition hover:bg-zinc-900/30">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if ($member->avatar_path)
                                        <img src="{{ asset('storage/'.$member->avatar_path) }}" alt="{{ $member->full_name }}"
                                            class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-700">
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#C6A343]/15 font-display font-bold text-[#C6A343]">
                                            {{ strtoupper(substr($member->full_name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('members.show', $member) }}" class="font-semibold text-[#FFFFFF] hover:text-[#C6A343]">
                                            {{ $member->full_name }}
                                        </a>
                                        <p class="text-[11px] text-[#A1A1AA]">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono font-semibold text-[#FFFFFF]">{{ $member->nim }}</span>
                                <span class="block text-[11px] text-[#A1A1AA]">Angkatan {{ $member->cohort_year }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-[#C6A343]">{{ $member->division?->name ?? 'Belum Ditetapkan' }}</span>
                                <span class="block text-[11px] text-[#A1A1AA]">{{ $member->position?->name ?? 'Anggota' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-zinc-900 px-2 py-0.5 text-[11px] font-medium uppercase text-[#C6A343]">
                                    {{ $member->user?->getRoleNames()->first() ?? 'anggota' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($member->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C6A343]/15 px-2.5 py-0.5 text-[11px] font-medium text-[#C6A343]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#C6A343]"></span> Aktif
                                    </span>
                                @elseif ($member->status === 'alumni')
                                    <span class="inline-flex items-center rounded-full bg-[#A80D0D]/15 px-2.5 py-0.5 text-[11px] font-medium text-[#C6A343]">
                                        Alumni
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-500/15 px-2.5 py-0.5 text-[11px] font-medium text-red-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('members.show', $member) }}" class="text-[#A1A1AA] hover:text-[#FFFFFF]">Detail</a>
                                <button wire:click="openEditModal('{{ $member->id }}')" type="button" class="text-[#C6A343] hover:underline">Edit</button>
                                @if ($member->status === 'active' && $member->user_id !== auth()->id())
                                    <button wire:click="deactivate('{{ $member->id }}')"
                                        wire:confirm="Nonaktifkan anggota {{ $member->full_name }}? Akun tidak akan bisa login lagi."
                                        type="button" class="text-red-400 hover:underline">
                                        Nonaktifkan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-[#A1A1AA]">
                                Belum ada data anggota yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-800 px-5 py-3">
            {{ $members->links() }}
        </div>
    </div>

    <!-- Modal Create / Edit Anggota -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">
                        {{ $editingMemberId ? 'Edit Data Anggota' : 'Tambah Anggota Baru' }}
                    </h3>
                    <button wire:click="$set('showFormModal', false)" class="text-[#A1A1AA] hover:text-[#FFFFFF]">&times;</button>
                </div>

                <form wire:submit="saveMember" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">NIM *</label>
                            <input wire:model="nim" type="text" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            @error('nim') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Nama Lengkap *</label>
                            <input wire:model="full_name" type="text" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            @error('full_name') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Email *</label>
                            <input wire:model="email" type="email" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            @error('email') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Nomor HP / WhatsApp</label>
                            <input wire:model="phone" type="text" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            @error('phone') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Divisi</label>
                            <select wire:model.live="division_id" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Jabatan</label>
                            <select wire:model="position_id" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Angkatan *</label>
                            <input wire:model="cohort_year" type="number" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Tanggal Bergabung *</label>
                            <input wire:model="joined_at" type="date" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Status Keanggotaan *</label>
                            <select wire:model="status" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                                <option value="alumni">Alumni</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Role Akses *</label>
                            <select wire:model="role" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                                <option value="anggota">Anggota</option>
                                <option value="admin">Admin / Pengurus</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">
                                Password {{ $editingMemberId ? '(Kosongkan jika tetap)' : '(Default: KukangEV#<NIM>)' }}
                            </label>
                            <input wire:model="password" type="password" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            @error('password') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[#A1A1AA]">Foto Profil (JPG/PNG/WEBP, maks 2MB)</label>
                            <input wire:model="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="w-full text-[#A1A1AA]">
                            @error('avatar') <span class="text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showFormModal', false)"
                            class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA] hover:text-[#FFFFFF]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606] hover:opacity-90">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Import Excel -->
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">Import Master Data Anggota (.XLSX)</h3>
                    <button wire:click="$set('showImportModal', false)" class="text-[#A1A1AA] hover:text-[#FFFFFF]">&times;</button>
                </div>

                <p class="mb-4 text-xs text-[#A1A1AA]">
                    Gunakan header kolom: <code class="text-[#C6A343]">nim, nama_lengkap, email, nomor_hp, angkatan, kode_divisi, jabatan, tanggal_bergabung</code>.
                </p>

                @if (! empty($importErrors))
                    <div class="mb-4 max-h-36 overflow-y-auto rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($importErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="importExcel" class="space-y-4 text-xs">
                    <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv" class="w-full text-[#FFFFFF]">
                    @error('importFile') <span class="text-red-400">{{ $message }}</span> @enderror

                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showImportModal', false)"
                            class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA]">Tutup</button>
                        <button type="submit"
                            class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606]">Proses Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
