<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pemulihan akun demo ke keadaan semula.
 *
 * Demo dipakai orang asing yang boleh melakukan apa saja: menambah karyawan,
 * mengubah shift, menghapus data absensi, bahkan mengganti password akun demo
 * itu sendiri. Karena itu pemulihannya membangun ulang seluruh isi perusahaan
 * demo, bukan sekadar menghapus yang terlihat.
 */
class DemoResetService
{
    /** Nama pengaturan tempat waktu pemulihan terakhir disimpan. */
    private const KUNCI_WAKTU = 'demo_last_reset';

    public function aktif(): bool
    {
        return (string) config('demo.email') !== '';
    }

    /** Apakah alamat yang diketik di layar masuk itu akun demo? */
    public function cocokDenganDemo(?string $isian): bool
    {
        if (! $this->aktif() || ! $isian) {
            return false;
        }

        return strcasecmp(trim($isian), (string) config('demo.email')) === 0;
    }

    /**
     * Pulihkan bila sudah waktunya.
     *
     * Sengaja dipanggil SEBELUM autentikasi. Kalau menunggu login berhasil,
     * pengunjung yang mengganti password akun demo akan mengunci semua orang
     * termasuk dirinya sendiri, dan pemulihannya tidak akan pernah terpicu.
     */
    public function pulihkanBilaPerlu(): bool
    {
        if (! $this->aktif() || ! $this->sudahWaktunya()) {
            return false;
        }

        $this->pulihkan();

        return true;
    }

    private function sudahWaktunya(): bool
    {
        $terakhir = $this->ambilWaktu();

        if (! $terakhir) {
            return true;
        }

        try {
            return Carbon::parse($terakhir)
                ->addHours((int) config('demo.reset_after_hours'))
                ->isPast();
        } catch (\Throwable $e) {
            // Nilai rusak diperlakukan sebagai belum pernah dipulihkan.
            return true;
        }
    }

    /**
     * Bangun ulang perusahaan demo dari nol.
     */
    public function pulihkan(): void
    {
        DB::transaction(function () {
            $this->bersihkan();

            $company = $this->buatPerusahaan();
            $admin = $this->buatAdmin($company);
            $shifts = $this->buatShift($company);
            $karyawan = $this->buatKaryawan($company);

            $this->buatAbsensi($company, $karyawan);
            $this->buatCuti($company, $karyawan, $admin);
        });

        $this->simpanWaktu();
    }

    /**
     * Buang seluruh isi perusahaan demo.
     *
     * Pengguna dihapus lebih dulu dan secara eksplisit: company_id pada tabel
     * users memakai nullOnDelete, jadi menghapus perusahaannya saja akan
     * meninggalkan akun-akun menggantung tanpa perusahaan.
     */
    private function bersihkan(): void
    {
        $company = Company::where('name', (string) config('demo.company'))->first();

        if (! $company) {
            return;
        }

        // Absensi, cuti, dan template wajah ikut terhapus lewat cascade user_id.
        User::where('company_id', $company->id)->get()->each->delete();

        // Shift ikut terhapus lewat cascade company_id.
        $company->delete();
    }

    private function buatPerusahaan(): Company
    {
        return Company::create([
            'name' => (string) config('demo.company'),
            'slug' => 'aldef-tech-demo-' . Str::lower(Str::random(6)),
            'email' => (string) config('demo.email'),
            'phone' => '021-0000000',
            'address' => 'Jakarta',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function buatAdmin(Company $company): User
    {
        return User::create([
            'company_id' => $company->id,
            'name' => (string) config('demo.name'),
            'email' => (string) config('demo.email'),
            'password' => (string) config('demo.password'),
            'role' => User::ROLE_ADMIN,
            'employee_code' => 'DEMO-001',
            'position' => 'Administrator',
            'phone' => '081200000000',
            'is_active' => true,
        ]);
    }

    /** @return array<int, Shift> */
    private function buatShift(Company $company): array
    {
        return [
            Shift::create([
                'company_id' => $company->id,
                'name' => 'Shift Pagi',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_minutes' => 15,
                'is_active' => true,
            ]),
            Shift::create([
                'company_id' => $company->id,
                'name' => 'Shift Siang',
                'start_time' => '13:00:00',
                'end_time' => '21:00:00',
                'grace_minutes' => 10,
                'is_active' => true,
            ]),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function buatKaryawan(Company $company)
    {
        $daftar = [
            ['Budi Santoso', 'Staf Operasional'],
            ['Siti Rahayu', 'Staf Keuangan'],
            ['Andi Pratama', 'Teknisi'],
            ['Dewi Lestari', 'Customer Service'],
            ['Rizky Maulana', 'Kurir'],
            ['Nur Aisyah', 'Admin Gudang'],
        ];

        $jumlah = min((int) config('demo.employees'), count($daftar));

        return collect(array_slice($daftar, 0, $jumlah))
            ->values()
            ->map(fn (array $orang, int $i) => User::create([
                'company_id' => $company->id,
                'name' => $orang[0],
                'email' => 'karyawan' . ($i + 1) . '@demo.aldeftech.com',
                'password' => Str::random(32), // tidak dimaksudkan untuk dipakai masuk
                'role' => User::ROLE_EMPLOYEE,
                'employee_code' => sprintf('DEMO-%03d', $i + 2),
                'position' => $orang[1],
                'phone' => '0812' . str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]));
    }

    /**
     * Riwayat absensi beberapa hari ke belakang.
     *
     * Sengaja tidak seragam: ada yang telat, ada yang tidak hadir, ada yang
     * belum pulang. Data yang rapi sempurna tidak memperlihatkan gunanya
     * laporan keterlambatan maupun rekap kehadiran.
     */
    private function buatAbsensi(Company $company, $karyawan): void
    {
        $hari = (int) config('demo.attendance_days');
        $baris = [];

        for ($mundur = $hari; $mundur >= 0; $mundur--) {
            $tanggal = Carbon::today()->subDays($mundur);

            // Akhir pekan dilewati supaya rekapnya masuk akal.
            if ($tanggal->isWeekend()) {
                continue;
            }

            foreach ($karyawan as $i => $orang) {
                // Satu orang absen sesekali - kehadiran 100% menyembunyikan
                // gunanya laporan.
                if (($mundur + $i) % 11 === 0) {
                    continue;
                }

                $telat = ($mundur + $i) % 7 === 0;
                $menitTelat = $telat ? 5 + (($i * 7) % 40) : 0;

                $masuk = $tanggal->copy()->setTime(8, 0)->addMinutes($menitTelat);

                // Hari ini sebagian belum pulang - keadaan yang wajar terlihat
                // di dashboard pada jam kerja.
                $belumPulang = $mundur === 0 && $i % 2 === 0;
                $pulang = $belumPulang ? null : $tanggal->copy()->setTime(17, 0)->addMinutes(($i * 5) % 45);

                $baris[] = [
                    'company_id' => $company->id,
                    'user_id' => $orang->id,
                    'date' => $tanggal->toDateString(),
                    'check_in' => $masuk,
                    'check_out' => $pulang,
                    'status' => $telat ? 'late' : 'present',
                    'late_minutes' => $menitTelat,
                    'work_minutes' => $pulang ? $masuk->diffInMinutes($pulang) : null,
                    'location_in' => 'Kantor Pusat',
                    'location_out' => $pulang ? 'Kantor Pusat' : null,
                    'note' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Sekali sisip, bukan satu per satu: dua ratusan baris lewat Eloquent
        // membuat pemulihan terasa menggantung saat dipicu dari layar masuk.
        foreach (array_chunk($baris, 200) as $potongan) {
            Attendance::insert($potongan);
        }
    }

    private function buatCuti(Company $company, $karyawan, User $admin): void
    {
        if ($karyawan->isEmpty()) {
            return;
        }

        $contoh = [
            ['type' => 'cuti_tahunan', 'status' => 'approved', 'mulai' => -6, 'lama' => 2,
                'alasan' => 'Cuti tahunan, acara keluarga.'],
            ['type' => 'sakit', 'status' => 'approved', 'mulai' => -3, 'lama' => 1,
                'alasan' => 'Demam, ada surat dokter.'],
            ['type' => 'izin', 'status' => 'pending', 'mulai' => 2, 'lama' => 1,
                'alasan' => 'Mengurus dokumen kependudukan.'],
            ['type' => 'cuti_tahunan', 'status' => 'pending', 'mulai' => 5, 'lama' => 3,
                'alasan' => 'Rencana perjalanan keluarga.'],
        ];

        foreach ($contoh as $i => $c) {
            $orang = $karyawan[$i % $karyawan->count()];
            $mulai = Carbon::today()->addDays($c['mulai']);
            $disetujui = $c['status'] === 'approved';

            Leave::create([
                'company_id' => $company->id,
                'user_id' => $orang->id,
                'type' => $c['type'],
                'start_date' => $mulai->toDateString(),
                'end_date' => $mulai->copy()->addDays($c['lama'] - 1)->toDateString(),
                'reason' => $c['alasan'],
                'status' => $c['status'],
                'approved_by' => $disetujui ? $admin->id : null,
                'approved_at' => $disetujui ? now() : null,
            ]);
        }
    }

    // ------------------------------------------------- Penyimpanan waktu
    //
    // Proyek ini belum punya tabel pengaturan, jadi waktunya dititipkan ke
    // cache. Cache bisa hilang saat dibersihkan, dan dampaknya hanya satu
    // pemulihan tambahan - bukan kerusakan.

    private function ambilWaktu(): ?string
    {
        try {
            return cache()->get(self::KUNCI_WAKTU);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function simpanWaktu(): void
    {
        try {
            cache()->forever(self::KUNCI_WAKTU, now()->toIso8601String());
        } catch (\Throwable $e) {
            // Cache tidak tersedia: pemulihan tetap berhasil, hanya waktunya
            // tidak tercatat sehingga bisa terpicu lagi lebih cepat.
        }
    }
}
