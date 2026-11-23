<x-layouts.guest title="Registrasi Anggota">
    <h2 class="font-display text-lg font-bold text-[#FFFFFF] mb-1">Registrasi Anggota Baru</h2>
    <p class="text-xs text-[#A1A1AA] mb-6">Lengkapi identitas mahasiswa ITERA untuk akses absensi & kontribusi.</p>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="nim" class="block text-xs font-medium text-[#A1A1AA] mb-1">NIM</label>
                <input id="nim" name="nim" type="text" value="{{ old('nim') }}" required
                    class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            </div>
            <div>
                <label for="cohort_year" class="block text-xs font-medium text-[#A1A1AA] mb-1">Angkatan</label>
                <input id="cohort_year" name="cohort_year" type="number" value="{{ old('cohort_year', date('Y')) }}" required
                    class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            </div>
        </div>

        <div>
            <label for="name" class="block text-xs font-medium text-[#A1A1AA] mb-1">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-[#A1A1AA] mb-1">Email ITERA / Aktif</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
        </div>

        <div>
            <label for="phone" class="block text-xs font-medium text-[#A1A1AA] mb-1">Nomor WhatsApp</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-xs font-medium text-[#A1A1AA] mb-1">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-[#A1A1AA] mb-1">Konfirmasi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3 py-2 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
            </div>
        </div>

        <button type="submit"
            class="w-full rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] px-4 py-2.5 text-sm font-semibold text-[#060606] transition hover:opacity-95">
            Daftar Sekarang
        </button>
    </form>
</x-layouts.guest>
