<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Wajah Terverifikasi') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="app-card overflow-hidden">
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Terverifikasi</p>
                        <p class="mt-1 text-3xl font-bold text-gray-800">{{ $users->count() }}</p>
                    </div>
                </div>
                <div class="app-card overflow-hidden">
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Via Aplikasi Mobile</p>
                        <p class="mt-1 text-3xl font-bold text-indigo-600">{{ $users->filter(fn ($u) => $u->face_registered_at && ! $u->faceTemplate)->count() }}</p>
                    </div>
                </div>
                <div class="app-card overflow-hidden">
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Via Web (face-api)</p>
                        <p class="mt-1 text-3xl font-bold text-violet-600">{{ $users->filter(fn ($u) => (bool) $u->faceTemplate)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Daftar Wajah Karyawan</h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                Karyawan yang sudah mendaftarkan wajah untuk biometrik.
                                @if (! auth()->user()->isSuperAdmin())
                                    Hanya <strong>superadmin</strong> yang bisa mereset wajah.
                                @endif
                            </p>
                        </div>
                        @if ($company)
                            <x-company-switcher :companies="auth()->user()->companyOptions($company)" :companyId="$company->id" />
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Karyawan</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">NIP</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Sumber</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Terdaftar</th>
                                    @if (auth()->user()->isSuperAdmin())
                                        <th class="px-4 py-2 text-left font-medium text-gray-500">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                                                    class="h-9 w-9 rounded-full object-cover bg-gray-100" />
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $user->employee_code ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($user->faceTemplate)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700">Web</span>
                                            @endif
                                            @if ($user->face_registered_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Mobile</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ ($user->face_registered_at ?? $user->faceTemplate?->updated_at)?->translatedFormat('d M Y, H:i') }}
                                        </td>
                                        @if (auth()->user()->isSuperAdmin())
                                            <td class="px-4 py-3">
                                                <form method="POST" action="{{ route('face.reset', $user) }}"
                                                    onsubmit="return confirm('Reset wajah {{ $user->name }}? Data biometrik akan dihapus dan karyawan wajib mendaftarkan wajah lagi.')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 text-xs font-semibold rounded-md hover:bg-rose-100 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                        Reset Wajah
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 5 : 4 }}" class="px-4 py-10 text-center text-gray-400">
                                            Belum ada karyawan yang mendaftarkan wajah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-5 py-4 text-sm text-indigo-900">
                <strong>Catatan:</strong> pendaftaran wajah hanya bisa dilakukan <strong>sekali</strong> per karyawan.
                Jika karyawan perlu mendaftar ulang (misal ganti penampilan atau perangkat baru), hanya
                <strong>superadmin</strong> yang bisa mereset wajah dari halaman ini.
            </div>
        </div>
    </div>
</x-app-layout>
