<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow-sm p-5">
        <div class="text-3xl font-bold text-gray-800">{{ $totalCompanies }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Perusahaan</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <div class="text-3xl font-bold text-gray-800">{{ $totalUsers }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Pengguna</div>
    </div>
</div>

<div class="app-card overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Perusahaan</h3>
            <a href="{{ route('companies.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Kelola semua →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Perusahaan</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Karyawan</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Total Absensi</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($companies as $company)
                        <tr>
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-800">{{ $company->name }}</div>
                                <div class="text-xs text-gray-400">{{ $company->email ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $company->employees_count }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $company->attendances_count }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $company->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $company->status === 'active' ? 'Aktif' : 'Ditangguhkan' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada perusahaan terdaftar.</td>
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
