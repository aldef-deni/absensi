<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Izin / Cuti') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Ajukan Izin / Cuti</h3>
                        <x-company-switcher :companies="$companies" :companyId="$companyId" />
                    </div>

                    <form method="POST" action="{{ route('leaves.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        @csrf

                        <div>
                            <x-input-label for="type" :value="__('Jenis')" />
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="cuti_tahunan">Cuti Tahunan</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                            <x-text-input id="start_date" class="mt-1 block w-full" type="date" name="start_date" :value="old('start_date', now()->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
                            <x-text-input id="end_date" class="mt-1 block w-full" type="date" name="end_date" :value="old('end_date', now()->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Alasan')" />
                            <x-text-input id="reason" class="mt-1 block w-full" type="text" name="reason" :value="old('reason')" placeholder="Contoh: keperluan keluarga" required />
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="lg:col-span-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Kirim Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Pengajuan</h3>

                        <form method="GET" action="{{ route('leaves.index') }}">
                            <select name="status" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                            </select>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Nama</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Jenis</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Durasi</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Alasan</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($leaves as $leave)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $leave->user->name }}</td>
                                        <td class="px-4 py-2 text-gray-600">
                                            @if ($leave->type === 'sakit')
                                                Sakit
                                            @elseif ($leave->type === 'cuti_tahunan')
                                                Cuti Tahunan
                                            @else
                                                Izin
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">
                                            {{ $leave->start_date->translatedFormat('d M Y') }}
                                            @if ($leave->start_date->ne($leave->end_date))
                                                – {{ $leave->end_date->translatedFormat('d M Y') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $leave->days() }} hari</td>
                                        <td class="px-4 py-2 text-gray-600 max-w-xs truncate">{{ $leave->reason }}</td>
                                        <td class="px-4 py-2">
                                            @if ($leave->status === 'approved')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Disetujui</span>
                                            @elseif ($leave->status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Ditolak</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($leave->status === 'pending' && Auth::user()->isAdmin())
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('leaves.approve', $leave) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-700">Setuju</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('leaves.reject', $leave) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-rose-600 text-white text-xs font-medium rounded-md hover:bg-rose-700">Tolak</button>
                                                    </form>
                                                </div>
                                            @elseif ($leave->status === 'pending' && $leave->user_id === Auth::id())
                                                <form method="POST" action="{{ route('leaves.cancel', $leave) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-300">Batalkan</button>
                                                </form>
                                            @elseif ($leave->approved_at)
                                                <span class="text-xs text-gray-400">oleh {{ $leave->approver?->name ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada pengajuan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $leaves->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
