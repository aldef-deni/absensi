# ALDEF Absensi — Run Doc

Laravel 12 app (Blade + Tailwind). Server aplikasi adalah `php artisan serve`; aset
sudah di-build sehingga Vite dev server TIDAK wajib jalan.

## Reproduksi artefak (fresh checkout)

Semua artefak sudah ada di workspace ini (workspace = main checkout), tidak perlu
menyalin dari mana pun. Yang dibutuhkan agar app jalan:

1. `.env` — sudah ada; koneksi DB: MySQL/MariaDB `absensi_saas` di `127.0.0.1:3306`
   (root tanpa password, kredensial XAMPP lokal). Kalau belum ada: `cp .env.example .env`,
   lalu `php artisan key:generate` dan sesuaikan `DB_*`.
2. Dependensi PHP: `composer install`.
3. Dependensi Node + build aset: `npm install && npm run build` (menghasilkan `public/build/manifest.json`).
4. DB tersedia & termigrasi: MySQL harus berjalan di port 3306 (`C:\xampp\mysql\data\my.ini`
   adalah config yang benar di mesin ini), lalu `php artisan migrate --seed`.
5. Aset biometrik lokal: `public/vendor/face-api/` (face-api.min.js + model) — sudah ada;
   jangan dihapus, wajib untuk fitur verifikasi wajah.

## Menjalankan server

Server aplikasi (PHP built-in server):

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

- URL: http://127.0.0.1:8000
- Port default 8000; jika sibuk, ganti port dan sesuaikan `APP_URL` di `.env`.
- Detach (Windows, biar hidup setelah sesi berakhir):
  ```
  powershell -NoProfile -Command "(Start-Process -FilePath 'C:\php83\php.exe' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=8000' -WorkingDirectory 'C:\Users\ade zulham\Downloads\absensi' -WindowStyle Hidden -PassThru).Id"
  ```

Opsional (dev aset dengan hot-reload, tidak wajib karena `public/build` sudah ada):
`npm run dev` (Vite).

Akun demo: `admin@nusantara.id` / `password` (admin), `super@admin.test` / `password`
(super admin), `budi@nusantara.id` / `password` (karyawan).
