<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2 app-card overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Absensi Hari Ini</h3>

            @php($now = now())
            <div class="text-center py-6">
                <div class="text-5xl font-bold text-gray-800 tabular-nums" id="clock">
                    {{ $now->format('H:i:s') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $now->translatedFormat('l, d F Y') }}
                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (! $todayAttendance || ! $todayAttendance->check_in)
                        <form method="POST" action="{{ route('attendance.check-in') }}" class="absensi-form" data-action="in">
                            @csrf
                            <input type="hidden" name="latitude_in" value="">
                            <input type="hidden" name="longitude_in" value="">
                            <input type="hidden" name="face_verified" value="0">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Check In
                            </button>
                        </form>
                    @elseif (! $todayAttendance->check_out)
                        <form method="POST" action="{{ route('attendance.check-out') }}" class="absensi-form" data-action="out">
                            @csrf
                            <input type="hidden" name="latitude_out" value="">
                            <input type="hidden" name="longitude_out" value="">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Check Out
                            </button>
                        </form>
                    @else
                        <div class="text-sm text-emerald-600 font-medium">
                            ✓ Hari ini sudah selesai. Sampai jumpa besok!
                        </div>
                    @endif
                </div>

                @if ($todayAttendance)
                    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-gray-500">Check In</div>
                            <div class="font-semibold text-gray-800">{{ $todayAttendance->check_in?->format('H:i:s') ?? '—' }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-gray-500">Check Out</div>
                            <div class="font-semibold text-gray-800">{{ $todayAttendance->check_out?->format('H:i:s') ?? '—' }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-gray-500">Status</div>
                            <div>
                                @if ($todayAttendance->status === 'late')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Telat {{ $todayAttendance->late_minutes }} menit</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Tepat waktu</span>
                                @endif
                            </div>
                        </div>
                        @if ($todayAttendance->face_verified)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-gray-500">Wajah</div>
                                <div class="font-semibold text-emerald-700">✓ Terverifikasi</div>
                            </div>
                        @endif
                        @if ($todayAttendance->distance_in !== null)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-gray-500">Jarak Kantor</div>
                                <div class="font-semibold text-gray-800">{{ round($todayAttendance->distance_in) }} m</div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-4 text-xs text-gray-400 space-y-1">
                    @if ($company?->use_location_lock)
                        <div>📍 Lock lokasi aktif — absen hanya bisa dari dalam radius {{ $company->radius_meters }} m kantor.</div>
                    @endif
                    @if ($company?->use_face_biometric)
                        <div>🪪 Verifikasi wajah aktif. Belum daftar wajah? <a href="{{ route('face.enroll') }}" class="text-indigo-600 hover:underline">Daftarkan di sini</a>.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="app-card overflow-hidden p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Ringkasan</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Absensi bulan ini</dt>
                    <dd class="font-semibold text-gray-800">{{ $monthCount }} hari</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Izin/cuti pending</dt>
                    <dd class="font-semibold text-gray-800">{{ $pendingLeaves }} pengajuan</dd>
                </div>
            </dl>
            <a href="{{ route('leaves.index') }}" class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">Ajukan izin/cuti →</a>
        </div>

        <div class="app-card overflow-hidden p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Biometrik</h3>
            <p class="text-sm text-gray-600 mb-3">Daftarkan wajahmu sekali; selanjutnya check-in memverifikasi wajah via kamera.</p>
            <a href="{{ route('face.enroll') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Kelola Wajah</a>
        </div>
    </div>
</div>

<div class="app-card overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Riwayat Terakhir</h3>
            <a href="{{ route('attendance.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Lihat semua →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Check In</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Check Out</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Jam Kerja</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($history as $a)
                        <tr>
                            <td class="px-4 py-2 text-gray-800">{{ $a->date->translatedFormat('d M Y') }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Overlay kamera verifikasi wajah -->
<div id="face-camera-overlay" class="hidden fixed inset-0 z-50 bg-gray-900/70 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Verifikasi Wajah</h3>
            <button type="button" id="face-camera-close" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-5">
            <video id="face-camera-video" class="w-full rounded-lg bg-black" autoplay muted playsinline></video>
            <p id="face-camera-status" class="mt-3 text-sm text-gray-600 min-h-[1.25rem]"></p>
            <div class="mt-4 flex gap-3">
                <button type="button" id="face-camera-verify" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-md hover:bg-emerald-700">Verifikasi</button>
                <button type="button" id="face-camera-cancel" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const config = @json($absensiConfig);

        const forms = document.querySelectorAll('form.absensi-form');
        if (!forms.length) {
            return;
        }

        const overlay = document.getElementById('face-camera-overlay');
        const video = document.getElementById('face-camera-video');
        const statusEl = document.getElementById('face-camera-status');
        const verifyBtn = document.getElementById('face-camera-verify');

        let pendingForm = null;
        let stream = null;

        const setStatus = (msg) => {
            statusEl.textContent = msg;
        };

        const getPosition = () => new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Browser tidak mendukung geolokasi.'));
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                () => reject(new Error('Tidak bisa mendapatkan lokasi. Izinkan akses lokasi di browser.')),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 },
            );
        });

        const setHidden = (form, name, value) => {
            const input = form.querySelector(`input[name="${name}"]`);
            if (input) {
                input.value = value;
            }
        };

        const openCamera = async (message) => {
            setStatus(message);
            overlay.classList.remove('hidden');
            try {
                stream = await FaceAuth.startCamera(video);
            } catch (e) {
                setStatus('Kamera tidak bisa diakses. Periksa izin kamera di browser.');
            }
        };

        const closeCamera = () => {
            if (stream) {
                FaceAuth.stopCamera(stream);
                stream = null;
            }
            video.srcObject = null;
            overlay.classList.add('hidden');
        };

        forms.forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const action = form.dataset.action;

                try {
                    if (config.locationLock) {
                        const pos = await getPosition();
                        setHidden(form, action === 'in' ? 'latitude_in' : 'latitude_out', pos.lat);
                        setHidden(form, action === 'in' ? 'longitude_in' : 'longitude_out', pos.lng);
                    }

                    if (action === 'in' && config.faceBiometric) {
                        const res = await fetch('/face/template', { headers: { Accept: 'application/json' } }).then((r) => r.json());

                        if (!res.exists) {
                            if (!confirm('Wajah kamu belum terdaftar. Daftarkan sekarang? (Tanpa daftar, absen tetap dicatat tanpa verifikasi wajah.)')) {
                                return;
                            }
                            window.location.href = '/face/enroll';
                            return;
                        }

                        pendingForm = form;
                        await openCamera('Posisikan wajah di dalam bingkai, lalu tekan Verifikasi.');
                        return;
                    }

                    form.submit();
                } catch (err) {
                    alert(err.message || 'Gagal memproses absensi.');
                }
            });
        });

        verifyBtn.addEventListener('click', async () => {
            verifyBtn.disabled = true;
            setStatus('Memindai wajah…');

            try {
                const descriptor = await FaceAuth.captureAveragedDescriptor(video, 2);

                const res = await fetch('/face/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ descriptor }),
                }).then((r) => r.json());

                if (res.verified) {
                    setStatus('✓ Wajah cocok. Mengirim absensi…');
                    setHidden(pendingForm, 'face_verified', '1');
                    closeCamera();
                    pendingForm.submit();
                } else {
                    setStatus('✗ Wajah tidak cocok (jarak ' + res.distance + '). Coba lagi dengan pencahayaan yang baik.');
                }
            } catch (err) {
                setStatus(err.message || 'Gagal memindai wajah.');
            } finally {
                verifyBtn.disabled = false;
            }
        });

        document.getElementById('face-camera-cancel').addEventListener('click', closeCamera);
        document.getElementById('face-camera-close').addEventListener('click', closeCamera);
    });
</script>

<script>
    setInterval(() => {
        const el = document.getElementById('clock');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }, 1000);
</script>
