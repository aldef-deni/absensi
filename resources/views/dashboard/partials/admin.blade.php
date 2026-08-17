<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div class="stat-card">
        <div class="stat-icon bg-primary-50 text-primary-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-bold text-gray-800" id="stat-total">{{ $totalEmployees }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Karyawan Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-emerald-50 text-emerald-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-bold text-emerald-600" id="stat-present">{{ $present }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Hadir Tepat Waktu</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-amber-50 text-amber-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-bold text-amber-600" id="stat-late">{{ $late }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Telat Hari Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-gray-100 text-gray-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-bold text-gray-500" id="stat-notyet">{{ $notYet }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Belum Absen</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-violet-50 text-violet-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-bold text-indigo-600" id="stat-onleave">{{ $onLeave }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Sedang Izin/Cuti</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 app-card overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Aktivitas Absensi Hari Ini</h3>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs text-gray-400" id="live-updated">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        live
                    </span>
                    <a href="{{ route('attendance.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Lihat semua →</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Karyawan</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Check In</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Check Out</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Wajah</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Jarak</th>
                        </tr>
                    </thead>
                    <tbody id="today-rows" class="divide-y divide-gray-100">
                        @forelse ($recent as $a)
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="font-medium text-gray-800">{{ $a->user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $a->user->position ?? 'Karyawan' }}</div>
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $a->check_in?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $a->check_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    @if ($a->status === 'late')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Telat {{ $a->late_minutes }} mnt</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-600">
                                    @if ($a->face_verified)
                                        <span class="text-emerald-600 font-medium">✓</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $a->distance_in !== null ? round($a->distance_in).' m' : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada karyawan yang absen hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="app-card overflow-hidden p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Perlu Perhatian</h3>

        <a href="{{ route('leaves.index') }}?status=pending" class="flex items-center justify-between py-3 border-b border-gray-100">
            <div>
                <div class="font-medium text-gray-800">Pengajuan izin/cuti</div>
                <div class="text-xs text-gray-400">menunggu persetujuan</div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-rose-100 text-rose-700">{{ $pendingLeaves }}</span>
        </a>

        <div class="mt-4 space-y-3">
            <a href="{{ route('employees.index') }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Tambah Karyawan</a>
            <a href="{{ route('shifts.index') }}" class="block w-full text-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Atur Shift</a>
            <a href="{{ route('settings.index') }}" class="block w-full text-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Pengaturan Lokasi & Biometrik</a>
            <a href="{{ route('reports.index') }}" class="block w-full text-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Lihat Laporan</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('today-rows');
        if (!tbody) {
            return;
        }

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));

        const rowHtml = (r) => {
            const status = r.status === 'late'
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Telat ${escapeHtml(r.late_minutes)} mnt</span>`
                : (r.status ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Hadir</span>` : '—');

            const face = r.face_verified ? '<span class="text-emerald-600 font-medium">✓</span>' : '<span class="text-gray-300">—</span>';
            const distance = r.distance_in !== null ? `${Math.round(r.distance_in)} m` : '—';

            return `<tr>
                <td class="px-4 py-2">
                    <div class="font-medium text-gray-800">${escapeHtml(r.name)}</div>
                    <div class="text-xs text-gray-400">${escapeHtml(r.position ?? 'Karyawan')}</div>
                </td>
                <td class="px-4 py-2 text-gray-600">${escapeHtml(r.check_in ?? '—')}</td>
                <td class="px-4 py-2 text-gray-600">${escapeHtml(r.check_out ?? '—')}</td>
                <td class="px-4 py-2">${status}</td>
                <td class="px-4 py-2">${face}</td>
                <td class="px-4 py-2 text-gray-600">${distance}</td>
            </tr>`;
        };

        const refresh = async () => {
            try {
                const response = await fetch('/attendance/today', { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (!data.stats) {
                    return;
                }

                document.getElementById('stat-total').textContent = data.stats.total_employees;
                document.getElementById('stat-present').textContent = data.stats.present;
                document.getElementById('stat-late').textContent = data.stats.late;
                document.getElementById('stat-notyet').textContent = data.stats.not_yet;
                document.getElementById('stat-onleave').textContent = data.stats.on_leave;

                const updated = document.getElementById('live-updated');
                if (updated) {
                    updated.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> diperbarui ${escapeHtml(data.updated_at)}`;
                }

                if (data.rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada karyawan yang absen hari ini.</td></tr>';
                } else {
                    tbody.innerHTML = data.rows.map(rowHtml).join('');
                }
            } catch (e) {
                // Abaikan error polling; coba lagi pada interval berikutnya.
            }
        };

        refresh();
        setInterval(refresh, 15000);
    });
</script>
