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
    <body class="font-sans text-gray-800 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.navigation')

            <!-- Overlay untuk sidebar (mobile) -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"></div>

            <!-- Konten utama -->
            <div class="lg:pl-64">
                <!-- Topbar -->
                <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-100 shadow-navbar">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <!-- Hamburger (mobile) -->
                            <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <h1 class="text-lg font-semibold text-gray-800 truncate">
                                {{ config('app.name') }}
                            </h1>
                        </div>

                        <div class="flex items-center gap-3">
                            @if (Auth::user()->company)
                                <span class="hidden md:inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                    {{ Auth::user()->company->name }}
                                </span>
                            @endif

                            <!-- Avatar dropdown -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center gap-2 py-1.5 px-2 rounded-full hover:bg-gray-100 transition-colors focus:outline-none">
                                        <img src="{{ Auth::user()->avatarUrl() }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-primary-500/30" alt="Foto profil" />
                                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                        <svg class="hidden sm:block fill-current h-4 w-4 text-gray-400" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <div class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                    </div>

                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg px-4 py-3">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-lg px-4 py-3">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <!-- Page Heading -->
                @isset($header)
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Page Content -->
                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
