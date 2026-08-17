<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'super@admin.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        $this->seedNusantara();
        $this->seedMajuBersama();
    }

    private function seedNusantara(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'pt-nusantara-digital'],
            [
                'name' => 'PT Nusantara Digital',
                'email' => 'hello@nusantara.id',
                'phone' => '021-555-0100',
                'address' => 'Jl. Jend. Sudirman No. 88, Jakarta Selatan',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 500,
                'use_location_lock' => false,
                'use_face_biometric' => false,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@nusantara.id'],
            [
                'company_id' => $company->id,
                'name' => 'Rina Maulida',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'position' => 'HR Manager',
                'employee_code' => 'ADM-001',
                'email_verified_at' => now(),
            ]
        );

        $employeesData = [
            ['budi@nusantara.id', 'Budi Santoso', 'EMP-001', 'Software Engineer', '0812-1000-0001'],
            ['sari@nusantara.id', 'Sari Wulandari', 'EMP-002', 'UI/UX Designer', '0812-1000-0002'],
            ['agus@nusantara.id', 'Agus Prasetyo', 'EMP-003', 'QA Engineer', '0812-1000-0003'],
            ['dewi@nusantara.id', 'Dewi Lestari', 'EMP-004', 'HRD', '0812-1000-0004'],
            ['rudi@nusantara.id', 'Rudi Hartono', 'EMP-005', 'Marketing', '0812-1000-0005'],
            ['maya@nusantara.id', 'Maya Putri', 'EMP-006', 'Product Manager', '0812-1000-0006'],
        ];

        $employees = [];
        foreach ($employeesData as [$email, $name, $code, $position, $phone]) {
            $employees[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'company_id' => $company->id,
                    'name' => $name,
                    'password' => 'password',
                    'role' => User::ROLE_EMPLOYEE,
                    'employee_code' => $code,
                    'position' => $position,
                    'phone' => $phone,
                    'email_verified_at' => now(),
                ]
            );
        }

        $shift = Shift::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Reguler'],
            ['start_time' => '08:00', 'end_time' => '17:00', 'grace_minutes' => 15, 'is_active' => true]
        );

        // Absensi 15 hari kerja terakhir (deterministik per karyawan + tanggal).
        $today = Carbon::today();
        $days = [];
        $cursor = $today->copy()->subDay();
        while (count($days) < 15) {
            if ($cursor->isWeekday()) {
                $days[] = $cursor->copy();
            }
            $cursor->subDay();
        }

        foreach ($employees as $employee) {
            foreach ($days as $date) {
                $seed = crc32($employee->email.$date->toDateString());
                $roll = $seed % 100;

                if ($roll < 10) {
                    continue; // tidak masuk (absen)
                }

                $isLate = $roll >= 75;
                $minute = $isLate ? 20 + ($seed % 36) : $seed % 10;
                $checkIn = $date->copy()->setTime(8, $minute, 0);
                $allowedUntil = $date->copy()->setTime(8, 0, 0)->addMinutes($shift->grace_minutes);

                $status = $checkIn->gt($allowedUntil) ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;
                $lateMinutes = $status === Attendance::STATUS_LATE ? (int) $allowedUntil->diffInMinutes($checkIn) : 0;

                $checkOut = $date->copy()->setTime(17, 30 + ($seed % 30), 0);
                $workMinutes = (int) $checkIn->diffInMinutes($checkOut);

                Attendance::firstOrCreate(
                    ['user_id' => $employee->id, 'date' => $date->toDateString()],
                    [
                        'company_id' => $company->id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                        'late_minutes' => $lateMinutes,
                        'work_minutes' => $workMinutes,
                    ]
                );
            }
        }

        // Absensi hari ini: 4 dari 6 karyawan sudah absen, 1 di antaranya telat.
        $todayPattern = [5, 45, 8, 20]; // menit check-in pagi ini (i=1 telat)
        foreach ($employees as $i => $employee) {
            if ($i >= 4) {
                continue;
            }

            $checkIn = $today->copy()->setTime(8, $todayPattern[$i], 0);
            $allowedUntil = $today->copy()->setTime(8, 0, 0)->addMinutes($shift->grace_minutes);
            $status = $checkIn->gt($allowedUntil) ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;
            $lateMinutes = $status === Attendance::STATUS_LATE ? (int) $allowedUntil->diffInMinutes($checkIn) : 0;

            Attendance::firstOrCreate(
                ['user_id' => $employee->id, 'date' => $today->toDateString()],
                [
                    'company_id' => $company->id,
                    'check_in' => $checkIn,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'note' => $i === 1 ? 'Macet di tol' : null,
                ]
            );
        }

        // Contoh pengajuan izin/cuti.
        Leave::firstOrCreate(
            ['user_id' => $employees[3]->id, 'start_date' => $today->copy()->addWeek()->startOfWeek()->toDateString()],
            [
                'company_id' => $company->id,
                'type' => Leave::TYPE_CUTI,
                'end_date' => $today->copy()->addWeek()->startOfWeek()->addDays(2)->toDateString(),
                'reason' => 'Cuti tahunan bersama keluarga',
                'status' => Leave::STATUS_PENDING,
            ]
        );

        Leave::firstOrCreate(
            ['user_id' => $employees[0]->id, 'start_date' => $today->toDateString()],
            [
                'company_id' => $company->id,
                'type' => Leave::TYPE_SAKIT,
                'end_date' => $today->toDateString(),
                'reason' => 'Demam, izin tidak masuk kerja',
                'status' => Leave::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]
        );

        Leave::firstOrCreate(
            ['user_id' => $employees[2]->id, 'start_date' => $today->copy()->subDay()->toDateString()],
            [
                'company_id' => $company->id,
                'type' => Leave::TYPE_IZIN,
                'end_date' => $today->copy()->subDay()->toDateString(),
                'reason' => 'Keperluan keluarga',
                'status' => Leave::STATUS_REJECTED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]
        );
    }

    private function seedMajuBersama(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'pt-maju-bersama'],
            [
                'name' => 'PT Maju Bersama',
                'email' => 'halo@majubersama.co.id',
                'phone' => '021-555-0200',
                'address' => 'Jl. Gatot Subroto No. 21, Jakarta Pusat',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@majubersama.co.id'],
            [
                'company_id' => $company->id,
                'name' => 'Dedi Kurniawan',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'position' => 'Direktur',
                'employee_code' => 'ADM-001',
                'email_verified_at' => now(),
            ]
        );

        $employees = [
            User::firstOrCreate(['email' => 'rina@majubersama.co.id'], [
                'company_id' => $company->id,
                'name' => 'Rina Fitriani',
                'password' => 'password',
                'role' => User::ROLE_EMPLOYEE,
                'employee_code' => 'EMP-001',
                'position' => 'Sales Executive',
                'phone' => '0813-2000-0001',
                'email_verified_at' => now(),
            ]),
            User::firstOrCreate(['email' => 'tono@majubersama.co.id'], [
                'company_id' => $company->id,
                'name' => 'Tono Wijaya',
                'password' => 'password',
                'role' => User::ROLE_EMPLOYEE,
                'employee_code' => 'EMP-002',
                'position' => 'Admin Gudang',
                'phone' => '0813-2000-0002',
                'email_verified_at' => now(),
            ]),
        ];

        Shift::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Shift Pagi'],
            ['start_time' => '07:30', 'end_time' => '16:30', 'grace_minutes' => 10, 'is_active' => true]
        );

        $now = Carbon::now();
        foreach ($employees as $i => $employee) {
            Attendance::firstOrCreate(
                ['user_id' => $employee->id, 'date' => $now->toDateString()],
                [
                    'company_id' => $company->id,
                    'check_in' => $now->copy()->subMinutes($i === 0 ? 45 : 75),
                    'status' => Attendance::STATUS_PRESENT,
                ]
            );
        }
    }
}
