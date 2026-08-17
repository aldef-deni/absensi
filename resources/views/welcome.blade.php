<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Absensi SaaS') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=public-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-vuexy-body text-gray-800">
        <!-- Header -->
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-gray-100 shadow-navbar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                        class="h-10 w-auto object-contain" />
                </a>
                <nav class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors px-3 py-2">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-primary-glow hover:bg-primary-600 transition-colors">Daftar Gratis</a>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main>
            <section class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-violet-50 -z-10"></div>
                <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary-200/40 blur-3xl -z-10"></div>
                <div class="absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-violet-200/40 blur-3xl -z-10"></div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 text-center">
                    <div class="max-w-2xl mx-auto">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold">
                            Platform SaaS Absensi Multi-Perusahaan
                        </span>

                        <h1 class="mt-5 text-4xl sm:text-5xl font-bold tracking-tight text-gray-900">
                            Kelola Absensi Karyawan
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-violet-600">dalam Satu Platform</span>
                        </h1>

                        <p class="mt-5 text-lg text-gray-500">
                            Check-in/out dengan <strong class="text-gray-700">lock lokasi GPS</strong> dan <strong class="text-gray-700">verifikasi wajah</strong>,
                            izin & cuti terpusat, dashboard real-time, dan laporan bulanan siap ekspor.
                        </p>

                        <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-primary-500 text-white font-semibold rounded-lg shadow-primary-glow hover:bg-primary-600 transition-colors">
                                Daftarkan Perusahaanmu
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                                Masuk ke Akun
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Fitur -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="app-card p-7 hover:shadow-card-hover transition-shadow">
                        <div class="h-12 w-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-semibold text-gray-900">Lock Lokasi GPS</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Check-in hanya bisa dari dalam radius kantor yang kamu tentukan. Jarak dihitung otomatis, di luar radius ditolak.
                        </p>
                    </div>

                    <div class="app-card p-7 hover:shadow-card-hover transition-shadow">
                        <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-semibold text-gray-900">Verifikasi Wajah</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Biometrik face recognition langsung di browser. Wajah diverifikasi sebelum absen tercatat — hanya vektor biometrik yang disimpan.
                        </p>
                    </div>

                    <div class="app-card p-7 hover:shadow-card-hover transition-shadow">
                        <div class="h-12 w-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-semibold text-gray-900">Laporan & Real-time</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Dashboard admin diperbarui otomatis, rekap hadir/telat/absen per bulan, dan ekspor CSV untuk penggajian.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-100 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-primary-600">Masuk</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-400 hover:text-primary-600">Daftar</a>
                </div>
            </div>
        </footer>
    </body>
</html>
