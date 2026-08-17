<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Absensi') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Rekap {{ $period->translatedFormat('F Y') }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $period->translatedFormat('F Y') }} memiliki {{ $rows->first()['workdays'] ?? 0 }} hari kerja (Senin–Jumat).</p>
                            </div>
                            <x-company-switcher :companies="$companies" :companyId="$companyId" />
                        </div>

                        <div class="flex gap-3">
                            <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2 items-center">
                                <input type="month" name="month" value="{{ $period->format('Y-m') }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-indigo-700">Tampilkan</button>
                            </form>
                            <a href="{{ route('reports.export', ['month' => $period->format('Y-m'), 'company_id' => $companyId]) }}"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-emerald-700">
                                Export CSV
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Karyawan</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">NIP</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jabatan</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Hadir</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Telat</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Absen</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500">Jam Kerja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $row['employee']->name }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $row['employee']->employee_code ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $row['employee']->position ?? '—' }}</td>
                                        <td class="px-4 py-2 text-center text-emerald-700 font-semibold">{{ $row['present'] }}</td>
                                        <td class="px-4 py-2 text-center text-amber-700 font-semibold">{{ $row['late'] }}</td>
                                        <td class="px-4 py-2 text-center text-rose-700 font-semibold">{{ $row['absent'] }}</td>
                                        <td class="px-4 py-2 text-center text-gray-700 font-semibold">{{ $row['work_hours'] }} jam</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada karyawan.</td>
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
