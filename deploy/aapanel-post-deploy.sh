#!/bin/bash
#
# Dijalankan aaPanel Git Manager setelah kode ditarik.
# Tempel isinya ke: Site → Git Manager → tab Script.
#
# Menarik kodenya sudah diurus aaPanel. Berkas ini mengerjakan yang tidak
# diketahui aaPanel: dependensi, migrasi, cache, dan kepemilikan berkas.
#
set -uo pipefail

APP_DIR="/www/wwwroot/absensi.aldeftech.com"

# Jalur lengkap, bukan nama program. aaPanel menjalankan skrip ini dengan PATH
# terbatas, dan `php` polos di server ini menunjuk /usr/bin/php - PHP sistem,
# bukan PHP 8.4 yang dipakai situsnya.
PHP="/www/server/php/84/bin/php"
COMPOSER="/home/aldeftech/bin/composer"
MYSQLDUMP="/usr/bin/mysqldump"

BACKUP_DIR="/www/backup/absensi-deploy"
BACKUP_KEEP=14

WEB_USER="www"
WEB_GROUP="www"

cd "$APP_DIR" || exit 1

echo "=== Post-deploy ALDEF Absensi — $(date '+%Y-%m-%d %H:%M:%S') ==="
echo "Kode di commit: $(git -c safe.directory='*' rev-parse --short HEAD)"

# ------------------------------------------------- Cadangkan database dulu
# Diambil sebelum migrasi. Migrasi yang gagal separuh jalan tidak bisa
# dibatalkan begitu saja, dan rollback kode di aaPanel tidak menyentuh database.
ambil_env() {
    local nilai
    nilai=$(grep -E "^$1=" .env 2>/dev/null | tail -n1)
    nilai="${nilai#*=}"
    nilai="${nilai%\"}"; nilai="${nilai#\"}"
    nilai="${nilai%\'}"; nilai="${nilai#\'}"
    printf '%s' "$nilai"
}

DB=$(ambil_env DB_DATABASE)
DB_USER=$(ambil_env DB_USERNAME)
DB_PASS=$(ambil_env DB_PASSWORD)
DB_HOST=$(ambil_env DB_HOST); DB_HOST="${DB_HOST:-127.0.0.1}"

if [ -n "$DB" ] && [ -x "$MYSQLDUMP" ]; then
    mkdir -p "$BACKUP_DIR"
    BERKAS="${BACKUP_DIR}/${DB}-$(date '+%Y%m%d-%H%M%S').sql.gz"

    if MYSQL_PWD="$DB_PASS" "$MYSQLDUMP" --single-transaction --quick \
        --no-tablespaces -h "$DB_HOST" -u "$DB_USER" "$DB" | gzip > "$BERKAS"; then
        echo "Database dicadangkan: $BERKAS"
        ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | tail -n "+$((BACKUP_KEEP + 1))" | xargs -r rm -f
    else
        echo "GAGAL mencadangkan database. Migrasi dibatalkan."
        rm -f "$BERKAS"
        exit 1
    fi
else
    echo "PERINGATAN: database tidak dicadangkan (mysqldump atau DB_DATABASE tidak ada)."
fi

# ------------------------------------------------------------- Pemeliharaan
# Situs dimatikan hanya selama migrasi dan pembangunan cache. trap menjamin
# dinyalakan lagi meski skrip ini berhenti di tengah jalan.
nyalakan_lagi() {
    "$PHP" artisan up >/dev/null 2>&1 || rm -f storage/framework/down
}
trap nyalakan_lagi EXIT

"$PHP" artisan down --retry=30 >/dev/null 2>&1

# ------------------------------------------------------------- Dependensi
if [ -x "$COMPOSER" ]; then
    # COMPOSER_HOME wajib disebut: skrip ini berjalan sebagai root, dan tanpa
    # ini composer mencari cache di direktori home yang mungkin tidak ada.
    # Composer dipanggil LEWAT $PHP, bukan langsung. Laravel memakai "@php" di
    # composer.json, dan composer menerjemahkannya menjadi PHP yang sedang
    # menjalankan dirinya. Dipanggil langsung, "@php" menunjuk /usr/local/bin/php
    # yang tidak ada di server ini - akibatnya package:discover dilewati diam-diam
    # dan bootstrap/cache/packages.php tidak pernah diperbarui.
    COMPOSER_HOME=/root/.composer COMPOSER_ALLOW_SUPERUSER=1 \
        "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress \
        || { echo "GAGAL composer install."; exit 1; }
else
    echo "PERINGATAN: composer tidak ditemukan di $COMPOSER, dilewati."
fi

# ------------------------------------------------------------------ Aset
# public/build tidak ikut di git, jadi CSS dan JS harus dibangun di server.
# Tanpa langkah ini perubahan tampilan tidak akan pernah terlihat, dan
# gejalanya menyesatkan: halaman terbuka tetapi tampak tanpa gaya.
if [ -f package.json ]; then
    NPM=$(command -v npm 2>/dev/null || ls -d /www/server/nodejs/*/bin/npm 2>/dev/null | sort -rV | head -1)

    if [ -n "${NPM:-}" ] && [ -x "$NPM" ]; then
        # npm ci hanya saat node_modules belum ada; menjalankannya tiap deploy
        # membuang satu sampai dua menit tanpa alasan.
        if [ ! -d node_modules ]; then
            echo "node_modules belum ada, memasang dependensi frontend."
            "$NPM" ci --no-audit --no-fund || { echo "GAGAL npm ci."; exit 1; }
        fi

        "$NPM" run build || { echo "GAGAL membangun aset frontend."; exit 1; }
        echo "Aset frontend dibangun."
    else
        echo "PERINGATAN: npm tidak ditemukan. Aset TIDAK dibangun ulang."
        echo "Perubahan tampilan tidak akan terlihat sampai public/build diperbarui."
    fi
fi

# ---------------------------------------------------------------- Migrasi
"$PHP" artisan migrate --force || { echo "GAGAL migrasi database."; exit 1; }

# ------------------------------------------------------------------ Cache
"$PHP" artisan config:clear >/dev/null 2>&1
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:clear >/dev/null 2>&1
"$PHP" artisan view:cache

# ------------------------------------------------------------ Kepemilikan
# Hanya folder yang memang perlu ditulis web server. Sengaja tidak seluruh
# folder situs: aaPanel menaruh .user.ini yang dikunci (chattr +i) di akarnya,
# dan chown -R ke situ selalu berakhir "Operation not permitted".
chown -R "${WEB_USER}:${WEB_GROUP}" storage bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

nyalakan_lagi
trap - EXIT

echo "=== Selesai. Sekarang di $(git -c safe.directory='*' rev-parse --short HEAD) ==="
