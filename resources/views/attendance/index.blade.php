<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Riwayat Absensi') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Filter Riwayat</h3>
                        <x-company-switcher :companies="$companies" :companyId="$companyId" />
                    </div>

                    <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        @if ($employees->isNotEmpty())
                            <div>
                                <x-input-label for="user_id" :value="__('Karyawan')" />
                                <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(request('user_id') == $employee->id)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">Semua</option>
                                <option value="present" @selected(request('status') === 'present')>Hadir</option>
                                <option value="late" @selected(request('status') === 'late')>Telat</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="from" :value="__('Dari Tanggal')" />
                            <x-text-input id="from" class="mt-1 block w-full" type="date" name="from" :value="request('from')" />
                        </div>

                        <div>
                            <x-input-label for="to" :value="__('Sampai Tanggal')" />
                            <x-text-input id="to" class="mt-1 block w-full" type="date" name="to" :value="request('to')" />
                        </div>

                        <div class="lg:col-span-4 flex gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Filter</button>
                            <a href="{{ route('attendance.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">Reset</a>
                            <a href="{{ route('attendance.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">Export CSV</a>
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
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Nama</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Check In</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Check Out</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jam Kerja</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Wajah</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Lokasi</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($attendances as $a)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-800">{{ $a->date->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium text-gray-800">{{ $a->user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $a->user->employee_code ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $a->check_in?->format('H:i') ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $a->check_out?->format('H:i') ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $a->work_minutes ? number_format($a->work_minutes / 60, 1).' jam' : '—' }}</td>
                                        <td class="px-4 py-2">
                                            @if ($a->status === 'late')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Telat {{ $a->late_minutes }} mnt</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Hadir</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($a->face_verified)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">✓ Wajah</span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $a->distance_in !== null ? round($a->distance_in).' m' : '—' }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ $a->note ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
