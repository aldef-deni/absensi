/**
 * FaceAuth — helper biometrik wajah berbasis face-api.js.
 *
 * Bergantung pada library global `faceapi` yang dimuat via <script>
 * dari /vendor/face-api/face-api.min.js, plus model di /vendor/face-api/models.
 */
window.FaceAuth = (() => {
    const MODEL_URL = '/vendor/face-api/models';
    let modelsLoaded = false;

    async function ensureModels() {
        if (modelsLoaded) {
            return;
        }

        if (typeof faceapi === 'undefined') {
            throw new Error('Library face-api.js tidak termuat. Muat ulang halaman.');
        }

        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        modelsLoaded = true;
    }

    async function startCamera(videoEl) {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
            audio: false,
        });

        videoEl.srcObject = stream;
        await videoEl.play();

        return stream;
    }

    function stopCamera(stream) {
        stream?.getTracks().forEach((track) => track.stop());
    }

    async function captureDescriptor(videoEl) {
        await ensureModels();

        const detection = await faceapi
            .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 416 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            throw new Error('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas dan pencahayaan cukup.');
        }

        return Array.from(detection.descriptor);
    }

    /**
     * Ambil beberapa sampel lalu rata-ratakan (lebih stabil daripada satu frame).
     */
    async function captureAveragedDescriptor(videoEl, samples = 3) {
        const descriptors = [];

        for (let i = 0; i < samples; i++) {
            try {
                descriptors.push(await captureDescriptor(videoEl));
            } catch (e) {
                // Lewati frame yang gagal; frame berikutnya tetap dicoba.
            }

            if (i < samples - 1) {
                await new Promise((resolve) => setTimeout(resolve, 250));
            }
        }

        if (descriptors.length === 0) {
            throw new Error('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas dan pencahayaan cukup.');
        }

        const n = descriptors[0].length;
        const sum = new Array(n).fill(0);

        descriptors.forEach((d) => d.forEach((value, i) => {
            sum[i] += value;
        }));

        const mean = sum.map((value) => value / descriptors.length);
        const norm = Math.sqrt(mean.reduce((acc, value) => acc + value * value, 0)) || 1;

        return mean.map((value) => value / norm);
    }

    return {
        ensureModels,
        startCamera,
        stopCamera,
        captureDescriptor,
        captureAveragedDescriptor,
    };
})();
