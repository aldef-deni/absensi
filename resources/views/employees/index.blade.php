<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Karyawan') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <form method="GET" action="{{ route('employees.index') }}" class="flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIP..."
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-indigo-700">Cari</button>
                            </form>
                            <x-company-switcher :companies="$companies" :companyId="$companyId" />
                        </div>

                        <button type="button" x-data @click="$dispatch('open-modal', 'add-employee')"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-emerald-700">
                            + Tambah Karyawan
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Nama</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">NIP</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jabatan</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Email</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Total Absensi</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($employees as $employee)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $employee->name }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $employee->employee_code ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $employee->position ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $employee->email }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $employee->attendances_count }}x</td>
                                        <td class="px-4 py-2">
                                            @if ($employee->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex gap-2">
                                                <button type="button" x-data @click="$dispatch('open-modal', 'edit-employee-{{ $employee->id }}')"
                                                    class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-100">Edit</button>
                                                <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Hapus karyawan ini? Data absensinya ikut terhapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 text-xs font-medium rounded-md hover:bg-rose-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada karyawan. Tambahkan yang pertama!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Tambah Karyawan -->
    <x-modal name="add-employee" :show="false" focusable>
        <form method="POST" action="{{ route('employees.store') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Karyawan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="name" :value="__('Nama Lengkap')" />
                    <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" required />
                </div>
                <div>
                    <x-input-label for="employee_code" :value="__('NIP (opsional)')" />
                    <x-text-input id="employee_code" class="mt-1 block w-full" type="text" name="employee_code" />
                </div>
                <div>
                    <x-input-label for="position" :value="__('Jabatan')" />
                    <x-text-input id="position" class="mt-1 block w-full" type="text" name="position" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" required />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('No. HP')" />
                    <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="password" :value="__('Password Awal')" />
                    <x-text-input id="password" class="mt-1 block w-full" type="text" name="password" required placeholder="min. 6 karakter" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </form>
    </x-modal>

    @foreach ($employees as $employee)
        <x-modal name="edit-employee-{{ $employee->id }}" :show="false" focusable>
            <form method="POST" action="{{ route('employees.update', $employee) }}" class="p-6">
                @csrf
                @method('PATCH')

                <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit {{ $employee->name }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" value="{{ $employee->name }}" required />
                    </div>
                    <div>
                        <x-input-label for="employee_code" :value="__('NIP')" />
                        <x-text-input id="employee_code" class="mt-1 block w-full" type="text" name="employee_code" value="{{ $employee->employee_code }}" />
                    </div>
                    <div>
                        <x-input-label for="position" :value="__('Jabatan')" />
                        <x-text-input id="position" class="mt-1 block w-full" type="text" name="position" value="{{ $employee->position }}" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('No. HP')" />
                        <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" value="{{ $employee->phone }}" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Password Baru (opsional)')" />
                        <x-text-input id="password" class="mt-1 block w-full" type="text" name="password" placeholder="kosongkan jika tidak diubah" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" @checked($employee->is_active) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            Karyawan aktif
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-app-layout>
