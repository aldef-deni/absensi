<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pengaturan Absensi') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
                        <h3 class="text-lg font-semibold text-gray-800">Lokasi Kantor & Lock Lokasi</h3>
                        <x-company-switcher :companies="$companies" :companyId="$companyId" />
                    </div>
                    <p class="text-sm text-gray-500 mb-4">
                        Saat aktif, karyawan hanya bisa check-in/check-out jika berada di dalam radius kantor.
                        Koordinat diambil dari browser karyawan (GPS), lalu dihitung jaraknya dari titik kantor ini.
                    </p>

                    @if (! $company)
                        <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            Belum ada perusahaan yang bisa dikelola.
                        </div>
                    @else
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="latitude" :value="__('Latitude Kantor')" />
                                <x-text-input id="latitude" class="mt-1 block w-full" type="number" step="any" name="latitude" :value="old('latitude', $company->latitude)" placeholder="-6.2088" />
                                <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="longitude" :value="__('Longitude Kantor')" />
                                <x-text-input id="longitude" class="mt-1 block w-full" type="number" step="any" name="longitude" :value="old('longitude', $company->longitude)" placeholder="106.8456" />
                                <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="radius_meters" :value="__('Radius (meter)')" />
                                <x-text-input id="radius_meters" class="mt-1 block w-full" type="number" name="radius_meters" min="10" max="20000" :value="old('radius_meters', $company->radius_meters)" />
                                <x-input-error :messages="$errors->get('radius_meters')" class="mt-2" />
                            </div>

                            <div class="flex items-end">
                                <button type="button" id="use-my-location" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                    📍 Ambil Lokasi Saya
                                </button>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <label class="flex items-center gap-3 text-sm text-gray-700">
                                <input type="checkbox" name="use_location_lock" value="1" @checked($company->use_location_lock) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <span>
                                    <strong>Aktifkan lock lokasi</strong>
                                    <span class="block text-xs text-gray-400">Check-in/out ditolak jika di luar radius.</span>
                                </span>
                            </label>

                            <label class="flex items-center gap-3 text-sm text-gray-700">
                                <input type="checkbox" name="use_face_biometric" value="1" @checked($company->use_face_biometric) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <span>
                                    <strong>Aktifkan verifikasi wajah (biometrik)</strong>
                                    <span class="block text-xs text-gray-400">Check-in wajib memverifikasi wajah via kamera bila karyawan sudah mendaftarkan wajahnya.</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Simpan Pengaturan</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            <div class="app-card overflow-hidden p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Cara kerja</h3>
                <ol class="list-decimal list-inside text-sm text-gray-600 space-y-1">
                    <li>Aktifkan fitur yang diinginkan, lalu klik <strong>Ambil Lokasi Saya</strong> untuk mengisi koordinat kantor dari posisimu saat ini (atau isi manual).</li>
                    <li>Atur radius — misal 500 m — sebagai batas area absen.</li>
                    <li>Untuk biometrik, karyawan mendaftarkan wajahnya satu kali lewat menu <strong>Wajah</strong> (kamera browser).</li>
                    <li>Check-in berikutnya otomatis mengambil lokasi GPS & memverifikasi wajah sebelum dicatat.</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('use-my-location');

            button.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert('Browser tidak mendukung geolokasi.');
                    return;
                }

                button.disabled = true;
                button.textContent = 'Mengambil lokasi…';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
                        document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
                        button.textContent = '📍 Ambil Lokasi Saya';
                        button.disabled = false;
                    },
                    () => {
                        alert('Tidak bisa mendapatkan lokasi. Izinkan akses lokasi di browser.');
                        button.textContent = '📍 Ambil Lokasi Saya';
                        button.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000 },
                );
            });
        });
    </script>
</x-app-layout>
