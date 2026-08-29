<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ALDEF Absensi') }} — Absensi karyawan dengan GPS & verifikasi wajah</title>
        <meta name="description" content="Platform absensi karyawan multi-perusahaan: check-in dengan lock lokasi GPS dan verifikasi wajah, izin & cuti terpusat, laporan bulanan siap ekspor. Tersedia aplikasi Android.">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=public-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>

    <body class="font-sans antialiased bg-white text-vuexy-black">

        {{-- ==================================================== Header --}}
        <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                        class="h-9 w-auto object-contain sm:h-10" />
                </a>

                <nav class="hidden items-center gap-8 md:flex">
                    <a href="#fitur" class="text-sm font-medium text-gray-500 transition-colors hover:text-primary-600">Fitur</a>
                    <a href="#cara-kerja" class="text-sm font-medium text-gray-500 transition-colors hover:text-primary-600">Cara Kerja</a>
                    <a href="#aplikasi" class="text-sm font-medium text-gray-500 transition-colors hover:text-primary-600">Aplikasi Android</a>
                </nav>

                <div class="flex items-center gap-1 sm:gap-3">
                    <a href="{{ route('login') }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition-colors hover:text-primary-600">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-primary-glow transition-all hover:-translate-y-px hover:bg-primary-600">
                        Daftar Gratis
                    </a>
                </div>
            </div>
        </header>

        <main>
            {{-- ==================================================== Hero --}}
            <section class="relative overflow-hidden">
                <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-primary-50/70 via-white to-white"></div>
                <div class="pointer-events-none absolute -right-40 -top-40 -z-10 h-[32rem] w-[32rem] rounded-full bg-primary-200/40 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-56 top-40 -z-10 h-[28rem] w-[28rem] rounded-full bg-violet-200/30 blur-3xl"></div>

                <div class="mx-auto max-w-6xl px-5 pb-16 pt-16 sm:px-8 lg:pb-24 lg:pt-24">
                    <div class="grid items-center gap-16 lg:grid-cols-[1.05fr_.95fr]">

                        {{-- Kiri: pesan utama --}}
                        <div class="text-center lg:text-left">
                            <span class="inline-flex items-center gap-2 rounded-full border border-primary-100 bg-white/70 px-3.5 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                                </span>
                                Platform absensi multi-perusahaan
                            </span>

                            <h1 class="mt-6 text-[2.6rem] font-extrabold leading-[1.08] tracking-tight text-vuexy-black sm:text-6xl">
                                Absensi karyawan<br class="hidden sm:block" />
                                yang <span class="bg-gradient-to-r from-primary-500 to-violet-500 bg-clip-text text-transparent">tidak bisa dititipkan</span>
                            </h1>

                            <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-gray-500 lg:mx-0">
                                Kehadiran dikunci oleh <strong class="font-semibold text-gray-700">lokasi GPS</strong> dan
                                <strong class="font-semibold text-gray-700">wajah</strong> karyawan yang bersangkutan.
                                Izin, cuti, dan rekap bulanan berjalan di satu tempat — siap diekspor untuk penggajian.
                            </p>

                            <div class="mt-9 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-center lg:justify-start">
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-3.5 font-semibold text-white shadow-primary-glow transition-all hover:-translate-y-0.5 hover:bg-primary-600">
                                    Daftarkan perusahaan
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                                    </svg>
                                </a>
                                <a href="#aplikasi"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3.5 font-semibold text-gray-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary-200 hover:text-primary-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" />
                                    </svg>
                                    Unduh aplikasi Android
                                </a>
                            </div>

                            @if($demoEmail)
                                <p class="mt-5 text-sm text-gray-400">
                                    Ingin coba dulu? Masuk dengan akun demo
                                    <a href="#demo" class="font-semibold text-primary-600 underline decoration-primary-200 underline-offset-4 transition-colors hover:decoration-primary-500">tanpa perlu mendaftar</a>.
                                </p>
                            @endif
                        </div>

                        {{-- Kanan: tiruan tampilan aplikasi. Sengaja bukan tangkapan
                             layar - selalu tajam di layar mana pun, dan jamnya ikut
                             berdetak sungguhan. --}}
                        <div class="relative mx-auto w-full max-w-[19rem]">
                            <div class="pointer-events-none absolute -inset-8 -z-10 rounded-[3rem] bg-gradient-to-tr from-primary-500/20 via-violet-400/10 to-transparent blur-2xl"></div>

                            <div class="rounded-[2.6rem] border border-gray-900/10 bg-vuexy-black p-2.5 shadow-2xl">
                                <div class="overflow-hidden rounded-[2.1rem] bg-[#f8f7fd]">

                                    {{-- Status bar --}}
                                    <div class="flex items-center justify-between px-6 pb-1 pt-3 text-[11px] font-semibold text-gray-500">
                                        <span data-jam-status>--:--</span>
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 18.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM4.2 9.3a11 11 0 0115.6 0l-1.8 1.8a8.5 8.5 0 00-12 0L4.2 9.3zm3.5 3.6a6 6 0 018.6 0l-1.8 1.8a3.5 3.5 0 00-5 0l-1.8-1.8z"/></svg>
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10h3v9H4zm6.5-4h3v13h-3zM17 2h3v17h-3z"/></svg>
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M15.7 4H14V2h-4v2H8.3C7.6 4 7 4.6 7 5.3v15.4c0 .7.6 1.3 1.3 1.3h7.4c.7 0 1.3-.6 1.3-1.3V5.3c0-.7-.6-1.3-1.3-1.3z"/></svg>
                                        </span>
                                    </div>

                                    {{-- Kartu jam --}}
                                    <div class="px-4 pt-3">
                                        <div class="rounded-2xl bg-gradient-to-br from-primary-500 to-violet-500 p-5 shadow-primary-glow">
                                            <div class="flex items-start justify-between">
                                                <p class="text-[11px] font-medium text-white/80">{{ config('demo.company', 'Aldef Tech Demo') }}</p>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-0.5 text-[9px] font-bold tracking-wide text-primary-600">
                                                    <span class="h-1 w-1 rounded-full bg-primary-500"></span> LIVE
                                                </span>
                                            </div>
                                            <p class="mt-2 font-mono text-[2.1rem] font-bold leading-none tracking-tight text-white" data-jam>00:00:00</p>
                                            <p class="mt-2 text-[11px] text-white/75">{{ now()->translatedFormat('l, d F Y') }}</p>
                                        </div>
                                    </div>

                                    {{-- Status lokasi --}}
                                    <div class="flex items-center gap-2 px-6 pt-4 text-[11px] font-medium text-gray-500">
                                        <svg class="h-3.5 w-3.5 text-vuexy-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Dalam radius kantor · 24 m
                                    </div>

                                    {{-- Tombol check in --}}
                                    <div class="px-4 pt-3">
                                        <div class="rounded-2xl bg-gradient-to-r from-primary-500 via-violet-500 to-fuchsia-500 py-4 text-center text-sm font-bold tracking-wide text-white shadow-lg">
                                            CHECK IN ▸
                                        </div>
                                    </div>

                                    {{-- Ringkasan hari ini --}}
                                    <div class="grid grid-cols-3 gap-2 px-4 pt-4">
                                        @foreach([['Masuk', '08:02', 'text-vuexy-success'], ['Shift', '08:00', 'text-gray-700'], ['Wajah', 'Terverifikasi', 'text-primary-600']] as [$label, $nilai, $warna])
                                            <div class="rounded-xl border border-gray-100 bg-white p-2.5 text-center shadow-sm">
                                                <p class="text-[9px] uppercase tracking-wide text-gray-400">{{ $label }}</p>
                                                <p class="mt-0.5 text-[11px] font-bold {{ $warna }}">{{ $nilai }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Bottom nav --}}
                                    <div class="mt-5 flex items-center justify-around rounded-t-3xl bg-white px-3 pb-6 pt-3 shadow-[0_-2px_12px_rgba(47,43,61,.06)]">
                                        @foreach([['Absen', true], ['Riwayat', false], ['Izin', false], ['Profil', false]] as [$menu, $aktif])
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="flex h-6 w-10 items-center justify-center rounded-full {{ $aktif ? 'bg-primary-100' : '' }}">
                                                    <span class="h-2 w-2 rounded-full {{ $aktif ? 'bg-primary-500' : 'bg-gray-300' }}"></span>
                                                </span>
                                                <span class="text-[9px] font-semibold {{ $aktif ? 'text-primary-600' : 'text-gray-400' }}">{{ $menu }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tiga jaminan singkat --}}
                    <div class="mt-16 grid gap-px overflow-hidden rounded-2xl border border-gray-100 bg-gray-100 sm:grid-cols-3">
                        @foreach([
                            ['Lock lokasi GPS', 'Absen ditolak di luar radius kantor'],
                            ['Biometrik wajah', 'Yang disimpan vektor wajah, bukan foto'],
                            ['Multi-perusahaan', 'Data tiap perusahaan terpisah penuh'],
                        ] as [$judul, $isi])
                            <div class="bg-white px-6 py-5">
                                <p class="flex items-center gap-2 text-sm font-semibold text-vuexy-black">
                                    <svg class="h-4 w-4 shrink-0 text-vuexy-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $judul }}
                                </p>
                                <p class="mt-1 pl-6 text-sm text-gray-500">{{ $isi }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ==================================================== Fitur --}}
            <section id="fitur" class="mx-auto max-w-6xl scroll-mt-20 px-5 pb-20 pt-12 sm:px-8 lg:pb-28 lg:pt-16">
                <div class="max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-widest text-primary-600">Fitur</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-vuexy-black sm:text-4xl">
                        Yang dibutuhkan HR, tanpa yang tidak.
                    </h2>
                    <p class="mt-4 text-lg text-gray-500">
                        Dibangun untuk dipakai setiap pagi oleh karyawan, dan setiap akhir bulan oleh bagian penggajian.
                    </p>
                </div>

                {{-- Kelas warna ditulis utuh, bukan disusun dari potongan: Tailwind
                     memindai berkas ini sebagai teks dan tidak akan menemukan
                     kelas yang baru terbentuk saat Blade dirender. --}}
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        ['Lock lokasi GPS', 'Titik kantor dan radiusnya kamu tentukan sendiri. Jaraknya dihitung di server — absen dari luar radius langsung ditolak.', 'bg-primary-50 text-primary-600', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['Verifikasi wajah', 'Wajah dicocokkan sebelum kehadiran tercatat. Yang tersimpan hanya vektor biometrik — fotonya tidak pernah disimpan.', 'bg-violet-50 text-violet-600', 'M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Shift & toleransi telat', 'Atur jam masuk, jam pulang, dan berapa menit keterlambatan yang masih dimaafkan. Status hadir atau telat ditentukan otomatis.', 'bg-amber-50 text-amber-600', 'M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Izin & cuti', 'Pengajuan dari HP, persetujuan dari dashboard. Hari yang disetujui tidak lagi terhitung sebagai absen.', 'bg-emerald-50 text-emerald-600', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['Laporan siap ekspor', 'Rekap hadir, telat, izin, dan jam kerja per bulan. Unduh sebagai CSV untuk langsung dipakai proses penggajian.', 'bg-sky-50 text-sky-600', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14'],
                        ['Multi-perusahaan', 'Satu instalasi melayani banyak perusahaan. Admin hanya melihat karyawannya sendiri, tidak pernah data tetangga.', 'bg-rose-50 text-rose-600', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-16 0H3m6-4h6M9 13h6M9 9h6'],
                    ] as [$judul, $isi, $warna, $path])
                        <div class="group rounded-2xl border border-gray-100 bg-white p-7 shadow-card transition-all duration-200 hover:-translate-y-1 hover:border-primary-100 hover:shadow-card-hover">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $warna }} transition-transform duration-200 group-hover:scale-110">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                </svg>
                            </div>
                            <h3 class="mt-5 text-base font-bold text-vuexy-black">{{ $judul }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $isi }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ==================================================== Cara kerja --}}
            <section id="cara-kerja" class="scroll-mt-20 border-y border-gray-100 bg-vuexy-body">
                <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8 lg:py-24">
                    <div class="max-w-2xl">
                        <p class="text-sm font-bold uppercase tracking-widest text-primary-600">Cara kerja</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-vuexy-black sm:text-4xl">
                            Siap dipakai sore ini juga.
                        </h2>
                    </div>

                    <div class="mt-12 grid gap-8 md:grid-cols-3">
                        @foreach([
                            ['Daftarkan perusahaan', 'Buat akun admin, isi titik kantor, radius, dan shift. Nyalakan lock lokasi atau verifikasi wajah sesuai kebutuhan.'],
                            ['Tambahkan karyawan', 'Masukkan karyawan beserta jabatannya. Mereka masuk lewat web atau aplikasi Android dengan akunnya masing-masing.'],
                            ['Pantau & ekspor', 'Dashboard memperlihatkan siapa saja yang sudah absen hari ini. Akhir bulan, rekapnya diunduh sebagai CSV.'],
                        ] as $i => [$judul, $isi])
                            <div class="relative">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-lg font-extrabold text-primary-600 shadow-card ring-1 ring-primary-100">
                                    {{ $i + 1 }}
                                </div>
                                @if($i < 2)
                                    <div class="absolute left-14 top-5 hidden h-px w-[calc(100%-3rem)] bg-gradient-to-r from-primary-200 to-transparent md:block"></div>
                                @endif
                                <h3 class="mt-5 text-base font-bold text-vuexy-black">{{ $judul }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $isi }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ==================================================== Aplikasi Android & akun demo --}}
            <section id="aplikasi" class="relative scroll-mt-20 overflow-hidden bg-vuexy-black">
                <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-40 -right-24 h-96 w-96 rounded-full bg-violet-500/20 blur-3xl"></div>

                <div class="relative mx-auto max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
                    <div class="max-w-2xl">
                        <p class="text-sm font-bold uppercase tracking-widest text-primary-300">Aplikasi Android</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                            Absen dari HP, lengkap dengan kamera dan GPS-nya.
                        </h2>
                        <p class="mt-4 text-lg text-white/60">
                            Aplikasi native untuk karyawan: check-in/out, riwayat bulanan, pengajuan izin, dan verifikasi wajah — terhubung ke server yang sama dengan versi web.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-[1.15fr_.85fr]">

                        {{-- Kartu unduh APK --}}
                        <div class="rounded-3xl border border-white/10 bg-white/[.06] p-7 backdrop-blur sm:p-9">
                            <div class="flex items-start gap-5">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-violet-500 shadow-primary-glow">
                                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xl font-bold text-white">{{ config('app.name') }} untuk Android</h3>
                                    <p class="mt-1 text-sm text-white/50">Berkas APK — pasang langsung, tanpa Play Store</p>
                                </div>
                            </div>

                            @if($apk)
                                <dl class="mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-4">
                                    @foreach([
                                        ['Versi', $apk['versi'] ? 'v'.$apk['versi'] : '—'],
                                        ['Ukuran', $apk['ukuran']],
                                        ['Android', '8.0+'],
                                        ['Diperbarui', $apk['diperbarui']->translatedFormat('d M Y')],
                                    ] as [$label, $nilai])
                                        <div class="bg-vuexy-black/60 px-4 py-3.5">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-white/40">{{ $label }}</dt>
                                            <dd class="mt-1 text-sm font-bold text-white">{{ $nilai }}</dd>
                                        </div>
                                    @endforeach
                                </dl>

                                <a href="{{ $apk['url'] }}" download
                                    class="mt-7 flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-primary-500 to-violet-500 px-6 py-4 text-base font-bold text-white shadow-primary-glow transition-all hover:-translate-y-0.5 hover:brightness-110">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" />
                                    </svg>
                                    Unduh APK ({{ $apk['ukuran'] }})
                                </a>

                                <ol class="mt-6 space-y-2.5 text-sm text-white/55">
                                    @foreach([
                                        'Buka berkas APK yang sudah terunduh di HP.',
                                        'Saat diminta, izinkan pemasangan dari sumber tidak dikenal.',
                                        'Buka aplikasinya — alamat server sudah terisi, tinggal masuk.',
                                    ] as $i => $langkah)
                                        <li class="flex gap-3">
                                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/10 text-[11px] font-bold text-white/70">{{ $i + 1 }}</span>
                                            <span>{{ $langkah }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <div class="mt-7 rounded-2xl border border-dashed border-white/20 bg-white/[.03] px-6 py-8 text-center">
                                    <p class="text-sm font-semibold text-white/80">APK sedang disiapkan</p>
                                    <p class="mx-auto mt-2 max-w-sm text-sm text-white/45">
                                        Berkas pemasangannya belum tersedia di server. Sementara itu, seluruh fiturnya bisa dipakai lewat versi web — di HP maupun komputer.
                                    </p>
                                    <a href="{{ route('login') }}"
                                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/20">
                                        Buka versi web
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kartu akun demo --}}
                        <div id="demo" class="scroll-mt-24 rounded-3xl border border-primary-400/30 bg-gradient-to-b from-primary-500/15 to-white/[.04] p-7 backdrop-blur sm:p-9">
                            @if($demoEmail)
                                <span class="inline-flex items-center rounded-full bg-primary-500/20 px-3 py-1 text-xs font-bold text-primary-200">
                                    Gratis dicoba
                                </span>
                                <h3 class="mt-4 text-xl font-bold text-white">Pakai akun demo</h3>
                                <p class="mt-2 text-sm leading-relaxed text-white/55">
                                    Akun yang sama berlaku di <strong class="font-semibold text-white/80">aplikasi Android</strong> maupun
                                    <strong class="font-semibold text-white/80">versi web</strong>. Perannya admin, jadi dashboard, karyawan, shift, dan laporan bisa dijelajahi sepenuhnya.
                                </p>

                                <div class="mt-6 space-y-3">
                                    @foreach([['Email', $demoEmail], ['Kata sandi', $demoPassword]] as [$label, $nilai])
                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-vuexy-black/50 px-4 py-3">
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/40">{{ $label }}</p>
                                                <p class="truncate font-mono text-sm font-semibold text-white">{{ $nilai }}</p>
                                            </div>
                                            <button type="button" data-salin="{{ $nilai }}"
                                                class="shrink-0 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80 transition-colors hover:bg-white/20">
                                                Salin
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ route('login') }}"
                                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3.5 font-bold text-vuexy-black transition-all hover:-translate-y-0.5 hover:bg-gray-100">
                                    Coba demo di web
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                                    </svg>
                                </a>

                                <p class="mt-5 flex gap-2.5 text-xs leading-relaxed text-white/40">
                                    <svg class="mt-px h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Akun demo dipakai bersama banyak orang. Isinya dipulihkan otomatis setiap {{ $demoResetJam }} jam, jadi silakan ubah apa saja.</span>
                                </p>
                            @else
                                <h3 class="text-xl font-bold text-white">Punya akun perusahaan?</h3>
                                <p class="mt-2 text-sm leading-relaxed text-white/55">
                                    Masuk dengan akun yang diberikan admin perusahaanmu — di aplikasi Android maupun versi web.
                                </p>
                                <a href="{{ route('login') }}"
                                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3.5 font-bold text-vuexy-black transition-all hover:-translate-y-0.5 hover:bg-gray-100">
                                    Masuk ke akun
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- ==================================================== Ajakan penutup --}}
            <section class="mx-auto max-w-6xl px-5 py-20 sm:px-8 lg:py-24">
                <div class="relative overflow-hidden rounded-3xl border border-primary-100 bg-gradient-to-br from-primary-50 via-white to-violet-50 px-8 py-14 text-center sm:px-16">
                    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary-200/40 blur-3xl"></div>
                    <h2 class="relative text-3xl font-extrabold tracking-tight text-vuexy-black sm:text-4xl">
                        Mulai catat kehadiran dengan benar.
                    </h2>
                    <p class="relative mx-auto mt-4 max-w-xl text-lg text-gray-500">
                        Buat akun perusahaan gratis, tambahkan karyawan, dan absen pertama bisa tercatat hari ini juga.
                    </p>
                    <div class="relative mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-primary-500 px-7 py-3.5 font-semibold text-white shadow-primary-glow transition-all hover:-translate-y-0.5 hover:bg-primary-600">
                            Daftar gratis
                        </a>
                        <a href="#aplikasi"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-7 py-3.5 font-semibold text-gray-700 transition-all hover:-translate-y-0.5 hover:border-primary-200 hover:text-primary-600">
                            Unduh aplikasi Android
                        </a>
                    </div>
                </div>
            </section>
        </main>

        {{-- ==================================================== Footer --}}
        <footer class="border-t border-gray-100 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-5 px-5 py-8 sm:flex-row sm:px-8">
                <div class="flex flex-col items-center gap-3 sm:flex-row sm:gap-5">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto object-contain" />
                    <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
                </div>
                <nav class="flex items-center gap-6">
                    <a href="#fitur" class="text-sm text-gray-400 transition-colors hover:text-primary-600">Fitur</a>
                    <a href="#aplikasi" class="text-sm text-gray-400 transition-colors hover:text-primary-600">Aplikasi</a>
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 transition-colors hover:text-primary-600">Masuk</a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold text-primary-600 transition-colors hover:text-primary-700">Daftar</a>
                </nav>
            </div>
        </footer>

        <script>
            // Jam pada tiruan aplikasi berdetak sungguhan - lebih hidup daripada
            // tangkapan layar, dan tidak pernah basi.
            (function () {
                const jam = document.querySelector('[data-jam]');
                const jamStatus = document.querySelector('[data-jam-status]');
                if (!jam) return;

                const dua = (n) => String(n).padStart(2, '0');

                function detak() {
                    const t = new Date();
                    jam.textContent = dua(t.getHours()) + ':' + dua(t.getMinutes()) + ':' + dua(t.getSeconds());
                    if (jamStatus) {
                        jamStatus.textContent = dua(t.getHours()) + ':' + dua(t.getMinutes());
                    }
                }

                detak();
                setInterval(detak, 1000);
            })();

            // Tombol salin pada kartu akun demo.
            document.querySelectorAll('[data-salin]').forEach(function (tombol) {
                tombol.addEventListener('click', async function () {
                    const semula = tombol.textContent;
                    try {
                        await navigator.clipboard.writeText(tombol.dataset.salin);
                        tombol.textContent = 'Tersalin';
                    } catch (e) {
                        tombol.textContent = 'Gagal';
                    }
                    setTimeout(function () { tombol.textContent = semula; }, 1600);
                });
            });
        </script>
    </body>
</html>
