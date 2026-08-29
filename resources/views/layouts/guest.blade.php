<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Aldef Tech Absensi') }}</title>

        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png?v=2">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=public-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center px-4 py-12 sm:py-14 overflow-hidden">

            <!-- Latar gradasi premium -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#1b1633] via-[#3b2a6b] to-[#150f2c]"></div>

            <!-- Blob cahaya -->
            <div class="absolute -top-48 -right-32 h-[560px] w-[560px] rounded-full bg-primary-600/40 blur-3xl"></div>
            <div class="absolute -bottom-48 -left-32 h-[560px] w-[560px] rounded-full bg-violet-500/30 blur-3xl"></div>
            <div class="absolute top-1/4 left-1/3 h-72 w-72 rounded-full bg-fuchsia-500/20 blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/4 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

            <!-- Pola titik halus -->
            <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(rgba(255,255,255,0.35) 1px, transparent 1px); background-size: 28px 28px;"></div>

            <!-- Vignette agar fokus ke kartu -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.45)_100%)]"></div>

            <div class="relative w-full sm:max-w-md">
                <!-- Logo besar -->
                <div class="flex justify-center mb-9">
                    <a href="{{ route('home') }}" class="group block">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                            class="h-24 sm:h-28 w-auto object-contain drop-shadow-[0_12px_35px_rgba(115,103,240,0.5)] transition-transform duration-300 group-hover:scale-105" />
                    </a>
                </div>

                <!-- Kartu kaca melayang (timbul) dengan border gradien -->
                <div class="rounded-[1.75rem] p-[1.5px] bg-gradient-to-br from-white/45 via-white/15 to-white/5 shadow-[0_30px_70px_-18px_rgba(0,0,0,0.65)]">
                    <div class="rounded-[1.65rem] bg-white/[0.97] backdrop-blur-xl px-7 sm:px-9 py-8 shadow-[inset_0_1px_0_rgba(255,255,255,0.9)]">
                        {{ $slot }}
                    </div>
                </div>

                <p class="mt-8 text-center text-xs text-white/50 tracking-wide">
                    &copy; {{ date('Y') }} {{ config('app.name') }} — Absensi digital multi-perusahaan
                </p>
            </div>
        </div>
    </body>
</html>
