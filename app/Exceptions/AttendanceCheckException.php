<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat check-in/check-out ditolak karena lokasi di luar radius,
 * lokasi tidak tersedia, atau verifikasi wajah gagal.
 */
class AttendanceCheckException extends RuntimeException
{
    public static function locationRequired(): self
    {
        return new self('Lokasi wajib diaktifkan untuk absen. Izinkan akses lokasi di browser.');
    }

    public static function locationOutOfRange(float $distanceMeters, int $radiusMeters): self
    {
        return new self(
            'Kamu berada di luar radius kantor ('.round($distanceMeters).' m dari kantor, batas '.$radiusMeters.' m).'
        );
    }

    public static function faceVerificationRequired(): self
    {
        return new self('Verifikasi wajah gagal. Wajah harus cocok dengan data yang terdaftar untuk check-in.');
    }

    public static function faceNotRegistered(): self
    {
        return new self('Wajah belum terdaftar. Daftarkan wajah terlebih dahulu melalui menu Wajah, atau hubungi superadmin.');
    }
}
