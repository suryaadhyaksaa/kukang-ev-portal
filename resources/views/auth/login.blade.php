<x-layouts.guest title="Login">
    <h2 class="font-display text-lg font-bold text-[#FFFFFF] mb-1">Masuk ke Sistem</h2>
    <p class="text-xs text-[#A1A1AA] mb-6">Gunakan akun anggota atau pengurus KUKANG EV ITERA.</p>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-xs font-medium text-[#A1A1AA] mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2.5 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-[#A1A1AA] mb-1.5">Password</label>
            <input id="password" name="password" type="password" required
                class="w-full rounded-xl border border-zinc-700/80 bg-[#060606] px-3.5 py-2.5 text-sm text-[#FFFFFF] focus:border-[#C6A343] focus:outline-none">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="inline-flex items-center gap-2 text-[#A1A1AA]">
                <input type="checkbox" name="remember" class="rounded border-zinc-700 bg-[#060606] text-[#C6A343]">
                Ingat sesi saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-[#C6A343] hover:underline">Lupa password?</a>
            @endif
        </div>

        <button type="submit"
            class="w-full rounded-xl bg-gradient-to-r from-[#C6A343] to-[#C6A343] px-4 py-2.5 text-sm font-semibold text-[#060606] transition hover:opacity-95">
            Masuk
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-xs text-[#A1A1AA]">
            Belum punya akun anggota?
            <a href="{{ route('register') }}" class="font-semibold text-[#C6A343] hover:underline">Daftar Anggota</a>
        </p>
    @endif
</x-layouts.guest>
