<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Autentikasi' }} — KUKANG EV ITERA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#060606] text-white antialiased selection:bg-[#C6A343] selection:text-[#060606]">
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-10">
        <!-- Gold & Crimson Ambient Glow -->
        <div class="pointer-events-none absolute -top-32 left-1/3 h-96 w-96 -translate-x-1/2 rounded-full bg-[#C6A343]/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 right-1/4 h-96 w-96 rounded-full bg-[#A80D0D]/20 blur-3xl"></div>

        <!-- Brand Header -->
        <div class="relative z-10 mb-6 flex flex-col items-center text-center">
            <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#C6A343]/35 bg-[#0A0A0D] shadow-2xl shadow-black">
                <svg class="h-12 w-12" viewBox="0 0 100 100" fill="none">
                    <path d="M32 22C18 31 12 49 18 65C24 81 42 90 60 86V76C46 79 32 72 27 59C23 49 26 37 32 30V22Z" fill="#C6A343"/>
                    <path d="M27 62C30 48 37 40 45 40C49 40 51 44 48 48C45 52 38 53 35 66L27 62Z" fill="#C6A343"/>
                    <circle cx="43" cy="44" r="2.6" fill="#060606"/>
                    <path d="M40 16V26C56 22 72 30 77 45C81 56 78 68 70 76V85C85 75 91 56 85 39C79 22 60 12 40 16Z" fill="#A80D0D"/>
                    <path d="M75 64C72 49 65 40 57 40C53 40 51 44 54 48C57 52 64 53 67 68L75 64Z" fill="#A80D0D"/>
                    <circle cx="59" cy="44" r="2.6" fill="#060606"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold tracking-wider text-white">KUKANG EV ITERA</h1>
            <p class="mt-0.5 font-mono text-[11px] uppercase tracking-widest text-[#C6A343]">Attendance &amp; Contribution Tracker</p>
        </div>

        <!-- Auth Card -->
        <div class="ev-card relative z-10 w-full max-w-md rounded-2xl p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="relative z-10 mt-6 text-center text-[11px] text-zinc-500">
            Tim Mobil Listrik KUKANG EV • Institut Teknologi Sumatera
        </p>
    </div>
</body>
</html>
