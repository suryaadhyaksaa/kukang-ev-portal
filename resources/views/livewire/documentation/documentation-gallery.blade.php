<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-[#C6A343]/40 bg-[#C6A343]/10 px-4 py-3 text-sm text-[#C6A343]">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-[#0F0F12]/85 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-lg font-bold text-[#FFFFFF]">Galeri & Arsip Dokumentasi</h2>
            <p class="text-xs text-[#A1A1AA]">Penyimpanan terproteksi untuk foto riset, video uji coba EV, dan dokumen teknis PDF.</p>
        </div>

        @can('create', App\Models\Documentation::class)
            <button wire:click="$set('showUploadModal', true)" type="button"
                class="rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] px-4 py-2 text-xs font-bold text-[#060606]">
                + Upload Dokumentasi
            </button>
        @endcan
    </div>

    <!-- Filter Galeri -->
    <div class="grid grid-cols-1 gap-3 rounded-2xl border border-zinc-800 bg-[#0F0F12]/60 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <select wire:model.live="filterEvent" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Kegiatan</option>
            @foreach ($events as $ev)
                <option value="{{ $ev->id }}">{{ $ev->title }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterDivision" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Divisi</option>
            @foreach ($divisions as $div)
                <option value="{{ $div->id }}">{{ $div->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterMember" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Anggota</option>
            @foreach ($members as $m)
                <option value="{{ $m->id }}">{{ $m->nim }} — {{ $m->full_name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterType" class="rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2 text-xs text-[#FFFFFF]">
            <option value="">Semua Tipe File</option>
            <option value="image">Foto (JPG/PNG/WEBP)</option>
            <option value="video">Video (MP4)</option>
            <option value="pdf">Dokumen (PDF)</option>
        </select>
    </div>

    <!-- Grid Galeri -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($documentations as $doc)
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-800 bg-[#0F0F12]/85">
                <div class="relative flex h-44 items-center justify-center bg-[#060606] overflow-hidden">
                    @if ($doc->isImage())
                        <img src="{{ route('documentation.preview', $doc) }}" alt="{{ $doc->title }}" class="h-full w-full object-cover">
                    @elseif ($doc->isVideo())
                        <div class="text-center">
                            <span class="rounded-lg bg-[#A80D0D]/20 px-3 py-1 font-mono text-xs font-bold text-[#C6A343]">VIDEO MP4</span>
                            <p class="mt-2 text-[11px] text-[#A1A1AA]">{{ $doc->formattedSize() }}</p>
                        </div>
                    @else
                        <div class="text-center">
                            <span class="rounded-lg bg-[#C6A343]/20 px-3 py-1 font-mono text-xs font-bold text-[#C6A343]">DOKUMEN PDF</span>
                            <p class="mt-2 text-[11px] text-[#A1A1AA]">{{ $doc->formattedSize() }}</p>
                        </div>
                    @endif

                    @if ($doc->visibility === 'private_member')
                        <span class="absolute top-2.5 right-2.5 rounded-md bg-red-500/85 px-2 py-0.5 text-[10px] font-bold text-white">
                            PRIVAT
                        </span>
                    @endif
                </div>

                <div class="p-4 text-xs">
                    <h3 class="truncate font-display font-bold text-[#FFFFFF]">{{ $doc->title }}</h3>
                    <p class="mt-1 truncate text-[11px] text-[#C6A343]">{{ $doc->event->title }}</p>
                    <p class="mt-0.5 text-[11px] text-[#A1A1AA]">
                        Oleh: {{ $doc->uploader->full_name }} • {{ $doc->formattedSize() }}
                    </p>

                    <div class="mt-4 flex items-center justify-between border-t border-zinc-800 pt-3">
                        <button wire:click="openPreview('{{ $doc->id }}')" type="button" class="font-semibold text-[#C6A343] hover:underline">
                            Preview
                        </button>
                        <div class="space-x-3">
                            <a href="{{ route('documentation.download', $doc) }}" class="text-[#C6A343] hover:underline">Download</a>
                            @can('delete', $doc)
                                <button wire:click="deleteDoc('{{ $doc->id }}')" wire:confirm="Hapus file dokumentasi ini?" type="button" class="text-red-400 hover:underline">
                                    Hapus
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-zinc-800 bg-[#0F0F12]/60 p-8 text-center text-sm text-[#A1A1AA]">
                Belum ada file dokumentasi yang sesuai dengan filter.
            </div>
        @endforelse
    </div>

    {{ $documentations->links() }}

    <!-- Modal Upload Dokumentasi -->
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-800 bg-[#0F0F12] p-6">
                <div class="mb-4 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-display text-base font-bold text-[#FFFFFF]">Upload Dokumentasi Kegiatan</h3>
                    <button wire:click="$set('showUploadModal', false)" class="text-[#A1A1AA]">&times;</button>
                </div>

                <form wire:submit="uploadFiles" class="space-y-4 text-xs">
                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Judul Dokumentasi *</label>
                        <input wire:model="title" type="text" placeholder="Pengujian Motor BLDC & Controller Tahap 2"
                            class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                        @error('title') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Kegiatan *</label>
                        <select wire:model="event_id" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach ($events as $ev)
                                <option value="{{ $ev->id }}">{{ $ev->title }}</option>
                            @endforeach
                        </select>
                        @error('event_id') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Hak Akses File *</label>
                        <select wire:model="visibility" class="w-full rounded-xl border border-zinc-700 bg-[#060606] px-3 py-2 text-[#FFFFFF]">
                            <option value="internal_team">Internal Tim KUKANG EV (Seluruh Anggota Aktif)</option>
                            <option value="private_member">Privat (Hanya Saya & Pengurus/Admin)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[#A1A1AA]">Pilih File (JPG, PNG, WEBP, MP4, PDF — Maks 10 file @50MB) *</label>
                        <input wire:model="files" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf" class="w-full text-[#FFFFFF]">
                        @error('files') <span class="text-red-400">{{ $message }}</span> @enderror
                        @error('files.*') <span class="text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-zinc-800 pt-4">
                        <button type="button" wire:click="$set('showUploadModal', false)" class="rounded-xl border border-zinc-700 px-4 py-2 text-[#A1A1AA]">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#C6A343] px-5 py-2 font-bold text-[#060606]">Mulai Upload</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Preview Media / PDF -->
    @if ($previewDoc)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl rounded-2xl border border-zinc-800 bg-[#0F0F12] p-5">
                <div class="mb-3 flex items-center justify-between border-b border-zinc-800 pb-3">
                    <div>
                        <h3 class="font-display text-sm font-bold text-[#FFFFFF]">{{ $previewDoc->title }}</h3>
                        <p class="text-[11px] text-[#A1A1AA]">{{ $previewDoc->original_filename }} ({{ $previewDoc->formattedSize() }})</p>
                    </div>
                    <button wire:click="closePreview" class="rounded-lg bg-zinc-900 px-3 py-1 text-xs text-[#FFFFFF]">Tutup</button>
                </div>

                <div class="flex max-h-[72vh] items-center justify-center overflow-auto rounded-xl bg-[#060606] p-2">
                    @if ($previewDoc->isImage())
                        <img src="{{ route('documentation.preview', $previewDoc) }}" alt="{{ $previewDoc->title }}" class="max-h-[68vh] rounded-lg">
                    @elseif ($previewDoc->isVideo())
                        <video controls class="max-h-[68vh] w-full rounded-lg">
                            <source src="{{ route('documentation.preview', $previewDoc) }}" type="{{ $previewDoc->mime_type }}">
                        </video>
                    @else
                        <iframe src="{{ route('documentation.preview', $previewDoc) }}" class="h-[68vh] w-full rounded-lg"></iframe>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
