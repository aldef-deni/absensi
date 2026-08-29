# Deploy Otomatis — ALDEF Absensi

Server: `/www/wwwroot/absensi.aldeftech.com` (aaPanel, Ubuntu, GCP)

---

## Jalan 1 — aaPanel Git Manager (disarankan)

aaPanel sudah punya mesin deploy sendiri: menarik kode dari GitHub, menyimpan
riwayat, dan bisa rollback beberapa versi. Ditambah Webhook, deploy terjadi
seketika setiap push.

Yang tidak diketahui aaPanel adalah langkah-langkah Laravel sesudahnya. Itu
diisi lewat tab **Script**.

### a. Isi tab Script

Site → **Git Manager** → tab **Script** → isi dengan **satu baris**:

```
bash /www/wwwroot/absensi.aldeftech.com/deploy/aapanel-post-deploy.sh
```

Satu baris, bukan seluruh isi berkasnya. Dengan begitu setiap perbaikan pada
skripnya ikut ter-deploy sendiri, tanpa perlu ditempel ulang.

> Berkas itu baru ada di server setelah deploy pertama. Kalau Script dijalankan
> sebelum kode ditarik, akan muncul `No such file or directory` — tekan
> **Deploy latest** sekali lagi dan pesan itu hilang.

Yang dikerjakan skripnya, berurutan:

1. Cadangkan database (gagal mencadangkan → deploy dibatalkan)
2. Nyalakan mode pemeliharaan
3. `composer install --no-dev`
4. **Bangun aset frontend** (`npm run build`)
5. `php artisan migrate --force`
6. Bangun ulang cache config, route, view
7. Kembalikan kepemilikan berkas ke `www`
8. Matikan mode pemeliharaan

### b. Nyalakan Webhook

Git Manager → tab **Webhook** → salin URL-nya dengan tombol **Copy**.

Di GitHub: repo → **Settings → Webhooks → Add webhook** → tempel URL itu di
**Payload URL** → pilih *Just the push event* → **Add webhook**.

Kolom **Secret** dibiarkan kosong; aaPanel memverifikasi lewat `access_key`
yang sudah ada di dalam URL.

### c. Deploy pertama

Tekan **Deploy latest** di tab Repository, lalu baca log di tab **Deployments**.

---

## Aset frontend — beda dari proyek Drive

`public/build` **tidak ikut di git**, jadi CSS dan JS harus dibangun di server.
Tanpa itu perubahan tampilan tidak akan pernah terlihat, dan gejalanya
menyesatkan: halamannya terbuka tetapi tampak tanpa gaya sama sekali.

Skrip post-deploy sudah menanganinya, tetapi **butuh Node.js di server**.
Periksa sekali:

```bash
command -v npm || ls -d /www/server/nodejs/*/bin/npm
```

Kalau kosong, pasang lewat aaPanel → **App Store → Node.js version manager**.

Kalau npm memang tidak ada, skripnya tidak berhenti — ia mencetak peringatan
dan melanjutkan. Tampilan lama tetap dipakai sampai `public/build` diperbarui
dengan cara lain.

> `npm ci` hanya dijalankan saat `node_modules` belum ada. Kalau
> `package-lock.json` berubah, jalankan sekali secara manual:
>
> ```bash
> cd /www/wwwroot/absensi.aldeftech.com && npm ci && npm run build
> ```

---

## Akun demo

```
Email    : demo@aldeftech.com
Password : demo12345
Peran    : Admin di perusahaan "Aldef Tech Demo"
```

Perusahaan tersendiri, terpisah dari pelanggan. Akun demo berperan admin, dan
admin melihat seluruh karyawan di perusahaannya — menaruhnya di perusahaan asli
berarti membuka data absensi pelanggan kepada siapa pun yang mencoba demo.

Isinya dibangun ulang setiap 24 jam, dipicu saat ada yang masuk memakai alamat
demo. Pemeriksaannya berjalan **sebelum** autentikasi: kalau menunggu login
berhasil, pengunjung yang mengganti password akan mengunci semua orang dan
pemulihannya tidak akan pernah terpicu lagi.

Isi contohnya sengaja tidak seragam — ada yang telat, ada yang tidak hadir, ada
pengajuan cuti yang masih menunggu. Data yang rapi sempurna tidak
memperlihatkan gunanya laporan keterlambatan maupun rekap kehadiran.

Menyiapkan atau memulihkan segera:

```bash
cd /www/wwwroot/absensi.aldeftech.com
/www/server/php/84/bin/php artisan absensi:demo-reset --force
```

Tombol **Coba Demo** di layar masuk mengisikan kredensialnya ke kolom, bukan
langsung mengirim — pemakainya tetap melihat apa yang dipakai.

Untuk mematikan seluruh fitur demo, isi `.env` dengan `DEMO_EMAIL=` kosong.

---

## Memantau

```bash
# Log deploy (jalur cron)
tail -f /www/wwwlogs/absensi-deploy.log

# Cadangan database
ls -lt /www/backup/absensi-deploy/ | head
```

Cadangan disimpan 14 terakhir.

---

## Jalan 2 — cron sendiri

Untuk server tanpa aaPanel, atau bila Git Manager tidak dipakai:

```bash
install -m 755 /www/wwwroot/absensi.aldeftech.com/deploy/auto-deploy.sh \
        /usr/local/bin/absensi-deploy.sh
crontab -e
```

```
*/2 * * * * /usr/local/bin/absensi-deploy.sh
```

Skrip itu memeriksa GitHub tiap 2 menit dan hanya bekerja bila ada commit baru.
Kegagalan di langkah mana pun mengembalikan kode ke commit sebelumnya dan
menyalakan situs lagi.

**Database tidak dipulihkan otomatis.** Pemulihan sepihak akan membuang data
yang ditulis pengguna sejak cadangan diambil. Perintahnya dicetak di
`storage/deploy-gagal`, tetapi keputusannya di tangan manusia.

Pakai **salah satu** jalan saja, jangan keduanya.

---

## Mengunggah APK Android

Halaman depan menampilkan kartu unduhan APK bila — dan hanya bila — ada berkas
`*.apk` di `public/downloads/`. Berkas itu **tidak lewat git**: satu APK 25 MB
per rilis akan menetap di riwayat selamanya dan memperlambat tiap clone maupun
deploy. Jadi APK diunggah langsung ke server.

Setelah membangun APK di `absensi-mobile` (`./gradlew assembleRelease`):

```bash
# dari komputer, ganti <user>@<host> sesuai server
scp ALDEF-Absensi-v1.4.apk \
    <user>@<host>:/www/wwwroot/absensi.aldeftech.com/public/downloads/

# di server, samakan kepemilikannya dengan berkas lain
chown www:www /www/wwwroot/absensi.aldeftech.com/public/downloads/*.apk
chmod 644 /www/wwwroot/absensi.aldeftech.com/public/downloads/*.apk
```

Lewat aaPanel: **File** → masuk ke `public/downloads` → **Upload**.

Halaman depan membaca sendiri nama, ukuran, versi (dari nama berkas), dan
tanggalnya — tidak ada yang perlu diubah di kode saat rilis baru. Berkas APK
lama sebaiknya dihapus: yang ditampilkan adalah yang paling baru diunggah.

`auto-deploy.sh` memakai `git reset --hard` **tanpa** `git clean`, jadi APK yang
sudah diunggah selamat dari deploy berikutnya. Bila suatu saat pindah ke Git
Manager aaPanel, periksa sekali bahwa berkasnya masih ada sesudah deploy.
