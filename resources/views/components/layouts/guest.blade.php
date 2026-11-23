<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Autentikasi' }} — KUKANG EV ITERA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#060606] text-[#FFFFFF] antialiased selection:bg-[#C6A343] selection:text-[#060606]">
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-10">
        <!-- Ambient Glow Background -->
        <div class="pointer-events-none absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-[#C6A343]/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 right-10 h-80 w-80 rounded-full bg-[#A80D0D]/10 blur-3xl"></div>

        <!-- Brand Header -->
        <div class="relative z-10 mb-6 flex flex-col items-center text-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#C6A343] to-[#C6A343] text-[#060606] shadow-lg shadow-[#C6A343]/25">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="font-display text-xl font-bold tracking-wider text-[#FFFFFF]">KUKANG EV ITERA</h1>
            <p class="mt-0.5 text-xs tracking-wide text-[#A1A1AA]">Attendance &amp; Contribution Tracker</p>
        </div>

        <!-- Auth Card -->
        <div class="relative z-10 w-full max-w-md rounded-2xl border border-zinc-800/90 bg-[#0F0F12]/90 p-6 shadow-2xl backdrop-blur-md sm:p-8">
            {{ $slot }}
        </div>

        <p class="relative z-10 mt-6 text-center text-[11px] text-[#A1A1AA]">
            Tim Mobil Listrik KUKANG EV • Institut Teknologi Sumatera
        </p>
    </div>
</body>
</html>
