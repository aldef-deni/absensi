<?php

namespace App\Services;

use App\Exceptions\AttendanceCheckException;
use App\Models\Attendance;
use App\Models\FaceTemplate;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Shift aktif milik perusahaan.
     */
    public function activeShift(int $companyId): ?Shift
    {
        return Shift::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * Jarak dalam meter antara dua koordinat (rumus Haversine).
     */
    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Catat check-in hari ini dengan validasi lokasi (lock) & verifikasi wajah (biometrik).
     * Mengembalikan null jika sudah pernah check-in hari ini.
     *
     * @throws AttendanceCheckException
     */
    public function checkIn(
        User $user,
        ?string $note = null,
        ?float $latitude = null,
        ?float $longitude = null,
        bool $faceVerified = false,
    ): ?Attendance {
        $now = Carbon::now();

        $existing = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if ($existing && $existing->check_in) {
            return null;
        }

        $company = $user->company;
        $distanceIn = null;

        // --- Lock lokasi: wajib di dalam radius kantor ---
        if ($company?->use_location_lock) {
            if ($latitude === null || $longitude === null || $company->latitude === null || $company->longitude === null) {
                throw AttendanceCheckException::locationRequired();
            }

            $distanceIn = $this->distanceMeters(
                $latitude,
                $longitude,
                (float) $company->latitude,
                (float) $company->longitude,
            );

            if ($distanceIn > (int) $company->radius_meters) {
                throw AttendanceCheckException::locationOutOfRange($distanceIn, (int) $company->radius_meters);
            }
        }

        // --- Biometrik wajah: wajib cocok bila template sudah terdaftar ---
        if ($company?->use_face_biometric) {
            $hasTemplate = FaceTemplate::query()->where('user_id', $user->id)->exists();

            if ($hasTemplate && ! $faceVerified) {
                throw AttendanceCheckException::faceVerificationRequired();
            }
        }

        $attendance = $existing ?? new Attendance();
        $attendance->company_id = $user->company_id;
        $attendance->user_id = $user->id;
        $attendance->date = $now->toDateString();
        $attendance->check_in = $now;
        $attendance->note = $note;
        $attendance->face_verified = $faceVerified;
        $attendance->distance_in = $distanceIn;

        if ($latitude !== null && $longitude !== null) {
            $attendance->location_in = round($latitude, 6).','.round($longitude, 6);
        }

        $shift = $this->activeShift($user->company_id);
        if ($shift) {
            $scheduled = Carbon::today()->setTimeFromTimeString($shift->start_time);
            $allowedUntil = $scheduled->copy()->addMinutes($shift->grace_minutes);

            if ($now->gt($allowedUntil)) {
                $attendance->status = Attendance::STATUS_LATE;
                $attendance->late_minutes = (int) $allowedUntil->diffInMinutes($now);
            }
        }

        $attendance->save();

        return $attendance;
    }

    /**
     * Catat check-out. Lokasi wajib valid bila lock lokasi aktif.
     * Mengembalikan null jika tidak ada check-in aktif.
     *
     * @throws AttendanceCheckException
     */
    public function checkOut(
        User $user,
        ?float $latitude = null,
        ?float $longitude = null,
        bool $faceVerified = false,
    ): ?Attendance {
        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->latest()
            ->first();

        if (! $attendance) {
            return null;
        }

        $company = $user->company;

        if ($company?->use_location_lock) {
            if ($latitude === null || $longitude === null || $company->latitude === null || $company->longitude === null) {
                throw AttendanceCheckException::locationRequired();
            }

            $distanceOut = $this->distanceMeters(
                $latitude,
                $longitude,
                (float) $company->latitude,
                (float) $company->longitude,
            );

            if ($distanceOut > (int) $company->radius_meters) {
                throw AttendanceCheckException::locationOutOfRange($distanceOut, (int) $company->radius_meters);
            }

            $attendance->distance_out = $distanceOut;
            $attendance->location_out = round($latitude, 6).','.round($longitude, 6);
        }

        $attendance->face_verified = $attendance->face_verified || $faceVerified;
        $attendance->check_out = Carbon::now();

        if ($attendance->check_out->gt($attendance->check_in)) {
            $attendance->work_minutes = (int) $attendance->check_in->diffInMinutes($attendance->check_out);
        }

        $attendance->save();

        return $attendance;
    }

    /**
     * Rekap bulanan per karyawan: hadir, telat, absen, total jam kerja.
     */
    public function monthlyReport(int $companyId, string $month): array
    {
        [$year, $monthNum] = array_map('intval', explode('-', $month));
        $period = Carbon::create($year, $monthNum, 1);
        $start = $period->copy()->startOfMonth();
        $end = $period->copy()->endOfMonth();

        $workdays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) {
                $workdays++;
            }
        }

        $employees = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('company_id', $companyId)
            ->with(['attendances' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            }])
            ->orderBy('name')
            ->get();

        $rows = $employees->map(function (User $employee) use ($workdays) {
            $attendances = $employee->attendances;

            $present = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
            $late = $attendances->where('status', Attendance::STATUS_LATE)->count();
            $totalMinutes = (int) $attendances->sum('work_minutes');

            return [
                'employee' => $employee,
                'present' => $present,
                'late' => $late,
                'absent' => max(0, $workdays - $attendances->count()),
                'workdays' => $workdays,
                'work_hours' => round($totalMinutes / 60, 1),
            ];
        });

        return [
            'period' => $period,
            'rows' => $rows,
        ];
    }
}
