<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Daftarkan Wajah (Biometrik)') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card overflow-hidden">
                <div class="p-6">
                    @if ($template)
                        <div class="mb-4 inline-flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            ✓ Wajah sudah terdaftar sejak {{ $template->updated_at->translatedFormat('d M Y, H:i') }}.
                            Pendaftaran hanya sekali — hubungi superadmin jika perlu reset.
                        </div>
                    @else
                        <div class="mb-4 inline-flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            Wajahmu belum terdaftar. Daftarkan sekali agar check-in bisa diverifikasi.
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Pendaftaran Wajah</h3>

                    <video id="enroll-video" class="w-full rounded-lg bg-black" autoplay muted playsinline></video>

                    <p id="enroll-status" class="mt-3 text-sm text-gray-600 min-h-[1.25rem]"></p>

                    <div class="mt-4 flex gap-3">
                        <button type="button" id="enroll-capture" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-md hover:bg-emerald-700">
                            Ambil & Simpan Wajah
                        </button>
                        <button type="button" id="enroll-cancel" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Batal</button>
                    </div>

                    <div class="mt-6 text-sm text-gray-500">
                        <strong>Tips:</strong> hadap ke kamera dengan pencahayaan cukup, tanpa kacamata gelap atau penutup wajah.
                        Sistem hanya menyimpan <em>vektor biometrik</em> (angka), bukan foto wajahmu.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const video = document.getElementById('enroll-video');
            const statusEl = document.getElementById('enroll-status');
            const captureBtn = document.getElementById('enroll-capture');
            const cancelBtn = document.getElementById('enroll-cancel');

            let stream = null;
            let saving = false;

            const setStatus = (msg) => {
                statusEl.textContent = msg;
            };

            const start = async () => {
                setStatus('Menyiapkan kamera…');
                captureBtn.disabled = true;
                cancelBtn.disabled = true;

                try {
                    stream = await FaceAuth.startCamera(video);
                    setStatus('Kamera siap. Tekan "Ambil & Simpan Wajah".');
                } catch (e) {
                    setStatus('Kamera tidak bisa diakses. Periksa izin kamera di browser.');
                }

                captureBtn.disabled = false;
                cancelBtn.disabled = false;
            };

            captureBtn.addEventListener('click', async () => {
                if (saving) {
                    return;
                }

                saving = true;
                captureBtn.disabled = true;
                setStatus('Memindai wajah (3 sampel)…');

                try {
                    const descriptor = await FaceAuth.captureAveragedDescriptor(video, 3);

                    setStatus('Menyimpan template wajah…');

                    const response = await fetch('/face/template', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({ descriptor }),
                    });

                    if (!response.ok) {
                        throw new Error('Gagal menyimpan. Coba lagi.');
                    }

                    setStatus('✓ Wajah berhasil didaftarkan! Kamu bisa menutup halaman ini.');
                    cancelBtn.textContent = 'Selesai';
                } catch (err) {
                    setStatus(err.message || 'Gagal memindai wajah.');
                } finally {
                    saving = false;
                    captureBtn.disabled = false;
                }
            });

            cancelBtn.addEventListener('click', () => {
                if (stream) {
                    FaceAuth.stopCamera(stream);
                    stream = null;
                }
                video.srcObject = null;
                window.location.href = '/dashboard';
            });

            start();
        });
    </script>
</x-app-layout>
