<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Pemulihan akun super admin dari server.
 *
 * Peran super admin tidak bisa diberikan lewat pendaftaran maupun antarmuka
 * mana pun - itu memang disengaja. Konsekuensinya, sekali akun itu hilang atau
 * turun peran, tidak ada jalan masuk lagi lewat web. Perintah ini jalan
 * keluarnya, dan hanya bisa dijalankan oleh yang punya akses ke server.
 */
class SuperAdminAccount extends Command
{
    protected $signature = 'absensi:superadmin
                            {--email= : Email untuk masuk}
                            {--password= : Password baru. Dikosongkan berarti ditanyakan.}
                            {--name= : Nama tampilan, hanya dipakai saat membuat akun baru}';

    protected $description = 'Lihat, pulihkan, atau buat akun super admin';

    public function handle(): int
    {
        $email = trim((string) $this->option('email'));

        if ($email === '') {
            $this->daftarkan();

            return self::SUCCESS;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Format email tidak sah.');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: $this->secret('Password baru'));

        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        $user = User::withoutGlobalScopes()->where('email', $email)->first();
        $baru = $user === null;

        if ($baru) {
            $user = new User([
                'name' => trim((string) ($this->option('name') ?: 'Super Admin')),
                'email' => $email,
            ]);
        }

        $user->password = Hash::make($password);
        $user->role = User::ROLE_SUPER_ADMIN;
        $user->is_active = true;

        // Super admin berdiri di atas seluruh perusahaan, jadi tidak boleh
        // terikat salah satunya - keterikatan itu justru mempersempit apa yang
        // terlihat olehnya lewat global scope perusahaan.
        $user->company_id = null;

        $user->save();

        $this->newLine();
        $this->info($baru
            ? 'Akun super admin dibuat.'
            : 'Akun ' . $email . ' dipulihkan sebagai super admin.');
        $this->line('Masuk memakai email: ' . $email);
        $this->newLine();

        $this->daftarkan();

        return self::SUCCESS;
    }

    private function daftarkan(): void
    {
        $daftar = User::withoutGlobalScopes()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->orderBy('id')
            ->get();

        $this->line('<comment>Akun Super Admin</comment>');
        $this->line(str_repeat('-', 60));

        if ($daftar->isEmpty()) {
            $this->error('Tidak ada satu pun akun super admin.');
            $this->line('Buat sekarang:');
            $this->line('  php artisan absensi:superadmin --email=nama@domain.com');
            $this->newLine();

            return;
        }

        foreach ($daftar as $u) {
            $this->line(sprintf(
                'id=%-4s %-34s aktif=%s',
                $u->id,
                $u->email,
                $u->is_active ? 'ya' : 'TIDAK'
            ));

            if (! $u->is_active) {
                $this->warn('  Akun nonaktif - tidak bisa masuk sampai diaktifkan.');
            }
        }

        $this->newLine();
        $this->line('Ganti password:');
        $this->line('  php artisan absensi:superadmin --email=nama@domain.com');
        $this->newLine();
    }
}
