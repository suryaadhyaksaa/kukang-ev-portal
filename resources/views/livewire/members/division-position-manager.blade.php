<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Form Tambah/Edit Divisi -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6">
            <h2 class="font-display text-base font-bold text-[#FFFFFF] mb-4">
                {{ $editingDivisionId ? 'Edit Divisi' : 'Tambah Divisi Baru' }}
            </h2>
            <form wire:submit="saveDivision" class="space-y-3 text-xs">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Kode Divisi *</label>
                        <input wire:model="code" type="text" placeholder="POWERTRAIN"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 uppercase text-[#FFFFFF]">
                        @error('code') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-[#A1A1AA]">Nama Divisi *</label>
                        <input wire:model="name" type="text" placeholder="Powertrain & Drivetrain"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        @error('name') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[#A1A1AA]">Deskripsi</label>
                    <textarea wire:model="description" rows="2"
                        class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]"></textarea>
                </div>
                <button type="submit" class="rounded-xl bg-[#C6A343] px-4 py-2 font-bold text-[#060606]">
                    {{ $editingDivisionId ? 'Perbarui Divisi' : 'Simpan Divisi' }}
                </button>
            </form>
        </div>

        <!-- Form Tambah Jabatan pada Divisi -->
        <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-6">
            <h2 class="font-display text-base font-bold text-[#FFFFFF] mb-4">Tambah Jabatan Divisi</h2>
            <form wire:submit="addPosition" class="space-y-3 text-xs">
                <div>
                    <label class="mb-1 block text-[#A1A1AA]">Pilih Divisi *</label>
                    <select wire:model="selectedDivisionId" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->name }} ({{ $div->code }})</option>
                        @endforeach
                    </select>
                    @error('selectedDivisionId') <span class="text-red-400">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="mb-1 block text-[#A1A1AA]">Nama Jabatan *</label>
                        <input wire:model="positionName" type="text" placeholder="Lead Battery Engineer"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        @error('positionName') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Level Hierarki</label>
                        <input wire:model="hierarchyLevel" type="number" min="1" max="99"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                    </div>
                </div>
                <button type="submit" class="rounded-xl bg-[#A80D0D] px-4 py-2 font-bold text-white">
                    + Tambah Jabatan
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Divisi & Jabatan -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach ($divisions as $division)
            <div class="rounded-2xl border border-zinc-800 bg-[#0F0F12]/80 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="font-mono text-[11px] font-semibold text-[#C6A343]">{{ $division->code }}</span>
                        <h3 class="font-display text-base font-bold text-[#FFFFFF]">{{ $division->name }}</h3>
                    </div>
                    <button wire:click="editDivision('{{ $division->id }}')" class="text-xs text-[#C6A343] hover:underline">Edit</button>
                </div>
                <p class="mt-1 text-xs text-[#A1A1AA]">{{ $division->description }}</p>
                <p class="mt-3 text-[11px] font-semibold text-[#C6A343]">{{ $division->members_count }} Anggota Terdaftar</p>

                <div class="mt-4 border-t border-zinc-800 pt-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-[#A1A1AA]">Daftar Jabatan</p>
                    <ul class="space-y-1.5 text-xs">
                        @forelse ($division->positions as $pos)
                            <li class="flex justify-between rounded-lg bg-[#060606]/70 px-2.5 py-1.5 text-[#FFFFFF]">
                                <span>{{ $pos->name }}</span>
                                <span class="text-[11px] text-[#A1A1AA]">Lv.{{ $pos->hierarchy_level }}</span>
                            </li>
                        @empty
                            <li class="text-[#A1A1AA]">Belum ada jabatan khusus.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</div>
