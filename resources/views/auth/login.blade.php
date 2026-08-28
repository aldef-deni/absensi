<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Header -->
    <div class="text-center mb-7">
        <h2 class="text-2xl font-bold text-gray-900">Selamat Datang 👋</h2>
        <p class="text-sm text-gray-500 mt-1.5">Masuk untuk melanjutkan ke {{ config('app.name') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </span>
                <x-text-input id="email" class="block w-full pl-11" type="email" name="email"
                    :value="old('email')" required autofocus autocomplete="username" placeholder="nama@perusahaan.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <x-text-input id="password" class="block w-full pl-11 pr-12" type="password" name="password"
                    required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility()"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded-md border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                <span>Ingat saya</span>
            </label>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="mt-7 w-full inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-violet-600 text-white text-sm font-semibold shadow-primary-glow hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
            Masuk
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>

        @if($demoEmail)
            {{-- Kolomnya diisi, bukan langsung dikirim: pengunjung tetap melihat
                 kredensial yang dipakai dan bisa membatalkannya. --}}
            <button type="button" onclick="isiDemo()"
                class="mt-3 w-full inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl
                       border border-primary-200 text-primary-700 text-sm font-semibold
                       hover:bg-primary-50 transition-colors duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 3h6m-3 0v6m-4.5 3h9l1.5 9H6l1.5-9z" />
                </svg>
                Coba Demo
            </button>
            <p class="text-center text-xs text-gray-400 mt-2">
                Masuk tanpa mendaftar. Isinya dikembalikan seperti semula setiap 24 jam.
            </p>
        @endif

        <p class="text-center text-sm text-gray-500 mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700">
                Daftar perusahaan baru
            </a>
        </p>
    </form>

    <script>
        @if($demoEmail)
        function isiDemo() {
            document.getElementById('email').value = @json($demoEmail);
            document.getElementById('password').value = @json($demoPassword);
            document.querySelector('form button[type="submit"]').focus();
        }
        @endif

        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-guest-layout>
