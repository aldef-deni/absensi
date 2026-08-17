<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Shift Kerja') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Tambah Shift</h3>
                        <x-company-switcher :companies="$companies" :companyId="$companyId" />
                    </div>

                    <form method="POST" action="{{ route('shifts.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nama Shift')" />
                            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" placeholder="Contoh: Reguler" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start_time" :value="__('Jam Masuk')" />
                            <x-text-input id="start_time" class="mt-1 block w-full" type="time" name="start_time" value="08:00" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_time" :value="__('Jam Pulang')" />
                            <x-text-input id="end_time" class="mt-1 block w-full" type="time" name="end_time" value="17:00" required />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grace_minutes" :value="__('Toleransi (menit)')" />
                            <x-text-input id="grace_minutes" class="mt-1 block w-full" type="number" name="grace_minutes" value="0" min="0" max="240" />
                            <x-input-error :messages="$errors->get('grace_minutes')" class="mt-2" />
                        </div>

                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Shift</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Nama</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jam Masuk</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jam Pulang</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Toleransi</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($shifts as $shift)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $shift->name }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $shift->start_time }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $shift->end_time }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $shift->grace_minutes }} menit</td>
                                        <td class="px-4 py-2">
                                            @if ($shift->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('shifts.toggle', $shift) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-100">
                                                        {{ $shift->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('Hapus shift ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 text-xs font-medium rounded-md hover:bg-rose-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada shift. Buat shift pertama agar status telat bisa dihitung otomatis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
