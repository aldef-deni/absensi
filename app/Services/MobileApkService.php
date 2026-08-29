<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Berkas APK Android yang tersedia untuk diunduh dari halaman depan.
 *
 * APK sengaja TIDAK ikut di git: satu berkas 25 MB per rilis akan menetap di
 * riwayat selamanya dan memperlambat tiap clone maupun deploy. Berkasnya
 * diunggah langsung ke `public/downloads/` di server, dan halaman depan
 * membaca apa yang benar-benar ada di sana - kalau belum diunggah, tombolnya
 * tampil sebagai "belum tersedia", bukan tautan yang mati.
 */
class MobileApkService
{
    /** Folder tempat APK diunggah, relatif terhadap `public/`. */
    private const FOLDER = 'downloads';

    /**
     * Rilis terbaru, atau null bila belum ada APK di server.
     *
     * @return array{nama:string,url:string,versi:?string,ukuran:string,diperbarui:Carbon}|null
     */
    public function terbaru(): ?array
    {
        $berkas = glob(public_path(self::FOLDER.'/*.apk')) ?: [];

        if ($berkas === []) {
            return null;
        }

        // Yang paling baru diunggah, bukan yang paling besar nomornya: nomor
        // versi ada di nama berkas dan nama berkas bisa apa saja.
        usort($berkas, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $terbaru = $berkas[0];
        $nama = basename($terbaru);

        return [
            'nama' => $nama,
            'url' => asset(self::FOLDER.'/'.rawurlencode($nama)),
            'versi' => $this->versiDariNama($nama),
            'ukuran' => $this->ukuranTerbaca((int) filesize($terbaru)),
            'diperbarui' => Carbon::createFromTimestamp(filemtime($terbaru)),
        ];
    }

    /** Ambil "1.4" dari "ALDEF-Absensi-v1.4.apk". */
    private function versiDariNama(string $nama): ?string
    {
        return preg_match('/v(\d+(?:\.\d+)*)/i', $nama, $cocok) ? $cocok[1] : null;
    }

    private function ukuranTerbaca(int $bytes): string
    {
        $mb = $bytes / 1048576;

        return $mb >= 10
            ? round($mb).' MB'
            : number_format($mb, 1, ',', '.').' MB';
    }
}
