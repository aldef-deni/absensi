# ALDEF Absensi (Laravel 12)

Platform SaaS absensi multi-perusahaan (multi-tenant) berbasis **Laravel 12** + Blade/Tailwind, dengan tampilan **terinspirasi tema Vuexy** (warna utama `#7367f0`, font Public Sans, layout sidebar) dan logo brand ALDEF.

Logo diambil dari `aldef-logo-2026.png` → disalin & dioptimasi ke `public/images/logo.png`.

## Fitur

- **Multi-perusahaan (SaaS)** — setiap user terikat ke satu perusahaan; data di-scope otomatis per tenant
- **3 peran**: `super_admin` (kelola platform), `admin` (kelola perusahaan), `employee` (absensi)
- **Company switcher super admin** — super admin bisa memilih perusahaan yang dikelola (dropdown di halaman Laporan, Karyawan, Shift, Pengaturan, Absensi, dan Izin/Cuti); pilihan tersimpan di session
- **Absensi**: check-in / check-out, status tepat waktu / telat berdasarkan shift + toleransi, durasi jam kerja
- **Lock lokasi (GPS)** — admin set titik kantor + radius; check-in/out otomatis mengambil koordinat browser karyawan dan **ditolak jika di luar radius** (rumus Haversine)
- **Biometrik wajah (face recognition)** — karyawan mendaftarkan wajah sekali via kamera (face-api.js / TensorFlow.js); check-in **memverifikasi wajah** sebelum dicatat. Hanya vektor biometrik 128-dimensi yang disimpan, bukan foto
- **Dashboard real-time** — statistik & aktivitas absensi admin diperbarui otomatis tiap 15 detik (polling `/attendance/today`)
- **Shift kerja**: atur jam masuk/pulang dan toleransi keterlambatan
- **Izin / Cuti**: pengajuan (izin, sakit, cuti tahunan), persetujuan/ditolak oleh admin, pembatalan oleh pemilik
- **Laporan bulanan**: rekap hadir/telat/absen/jam kerja per karyawan + **export CSV** (termasuk status wajah & jarak)
- **Kelola karyawan**: tambah/edit/hapus, NIP, jabatan, status aktif
- **Foto profil**: upload foto di menu Profile (JPG/PNG/WebP, maks 2 MB); tanpa foto, ditampilkan avatar inisial otomatis — tampil di pojok kanan navigasi & menu mobile

## Akun Demo (password semua: `password`)

| Peran | Email |
|---|---|
| Super Admin | `super@admin.test` |
| Admin PT Nusantara Digital | `admin@nusantara.id` |
| Karyawan | `budi@nusantara.id`, `sari@nusantara.id`, `agus@nusantara.id`, `dewi@nusantara.id`, `rudi@nusantara.id`, `maya@nusantara.id` |
| Admin PT Maju Bersama | `admin@majubersama.co.id` |

## Cara Menjalankan

Persyaratan: PHP ≥ 8.2, Composer, MySQL/MariaDB, Node.js (untuk build aset). Kamera & GPS bekerja di `localhost` (secure context).

```bash
# 1. Pasang dependency
composer install
npm install && npm run build

# 2. Siapkan environment (copy .env, isi kredensial DB)
cp .env.example .env
php artisan key:generate

# 3. Migrasi + seed data demo
php artisan migrate --seed

# 4. Jalankan server
php artisan serve
```

Buka **http://127.0.0.1:8000**.

## Mengaktifkan Lock Lokasi & Biometrik Wajah

1. Login sebagai admin → menu **Pengaturan**.
2. Centang **Aktifkan lock lokasi**, klik **📍 Ambil Lokasi Saya** (mengisi koordinat dari posisimu saat ini), atur radius, simpan.
3. Centang **Aktifkan verifikasi wajah**, simpan.
4. Setiap karyawan membuka menu **Wajah** dan mendaftarkan wajahnya sekali (kamera browser).
5. Check-in berikutnya: lokasi GPS dicek dulu, lalu wajah diverifikasi. Diluar radius atau wajah tidak cocok → absen ditolak dengan pesan jelas.

Catatan: bila biometrik aktif tapi wajah karyawan belum didaftarkan, check-in tetap dicatat (tanpa verifikasi) hingga wajahnya didaftarkan.

## Teknis

- Library biometrik: `face-api.js` + model (`tiny_face_detector`, `face_landmark_68`, `face_recognition`) di `public/vendor/face-api/` — tersimpan lokal, tidak butuh internet saat runtime.
- `app/Services/AttendanceService.php` — logika absensi: Haversine (jarak), validasi radius, verifikasi wajah, rekap bulanan.
- `app/Services/FaceService.php` — perbandingan vektor wajah (cosine distance, ambang `0.55`).
- `app/Models/Concerns/BelongsToCompany.php` — trait multi-tenancy (global scope per perusahaan).
- `tests/Feature/AttendanceLockTest.php` — pengujian lock lokasi & biometrik.
- `tests/Feature/SuperAdminCompanyContextTest.php` — pengujian company switcher super admin (semua halaman manajemen aman dibuka tanpa crash).
- `phpunit.xml` memakai database terpisah (`absensi_saas_test`) agar tes tidak mengganggu data demo.
