<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kelola Perusahaan') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Perusahaan</h3>

                    <form method="POST" action="{{ route('companies.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nama Perusahaan')" />
                            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('No. HP')" />
                            <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" />
                        </div>

                        <div class="sm:col-span-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Perusahaan</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Kontak</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Karyawan</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Total Absensi</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($companies as $company)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $company->name }}</td>
                                        <td class="px-4 py-2 text-gray-600">
                                            {{ $company->email ?? '—' }}
                                            @if ($company->phone)
                                                <div class="text-xs text-gray-400">{{ $company->phone }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-center text-gray-600">{{ $company->employees_count }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600">{{ $company->attendances_count }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $company->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $company->status === 'active' ? 'Aktif' : 'Ditangguhkan' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <form method="POST" action="{{ route('companies.toggle', $company) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1 {{ $company->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} text-xs font-medium rounded-md">
                                                    {{ $company->status === 'active' ? 'Tangguhkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada perusahaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $companies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
