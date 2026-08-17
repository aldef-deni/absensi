<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AttendanceCheckException;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function __construct(private readonly AttendanceService $service)
    {
    }

    /**
     * Status absensi user hari ini + pengaturan perusahaan (untuk UI mobile).
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();

        return response()->json([
            'date' => Carbon::today()->toDateString(),
            'server_time' => Carbon::now()->toIso8601String(),
            'company' => $company ? [
                'name' => $company->name,
                'latitude' => $company->latitude,
                'longitude' => $company->longitude,
                'radius_meters' => $company->radius_meters,
                'use_location_lock' => (bool) $company->use_location_lock,
                'use_face_biometric' => (bool) $company->use_face_biometric,
            ] : null,
            'attendance' => $attendance ? $this->attendancePayload($attendance) : null,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'face_verified' => ['sometimes', 'boolean'],
        ]);

        try {
            $attendance = $this->service->checkIn(
                $request->user(),
                $request->input('note'),
                $request->float('latitude'),
                $request->float('longitude'),
                $request->boolean('face_verified'),
            );
        } catch (AttendanceCheckException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (! $attendance) {
            return response()->json(['message' => 'Kamu sudah melakukan check-in hari ini.'], 422);
        }

        $message = $attendance->status === Attendance::STATUS_LATE
            ? 'Check-in berhasil (telat '.$attendance->late_minutes.' menit).'
            : 'Check-in berhasil. Selamat bekerja!';

        return response()->json([
            'message' => $message,
            'attendance' => $this->attendancePayload($attendance),
        ], 201);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'face_verified' => ['sometimes', 'boolean'],
        ]);

        try {
            $attendance = $this->service->checkOut(
                $request->user(),
                $request->float('latitude'),
                $request->float('longitude'),
                $request->boolean('face_verified'),
            );
        } catch (AttendanceCheckException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (! $attendance) {
            return response()->json(['message' => 'Tidak ada check-in aktif untuk di-check-out.'], 422);
        }

        $hours = round($attendance->work_minutes / 60, 1);

        return response()->json([
            'message' => 'Check-out berhasil. Total jam kerja hari ini: '.$hours.' jam.',
            'attendance' => $this->attendancePayload($attendance),
        ]);
    }

    /**
     * Riwayat absensi user (bulan berjalan bila month tidak dikirim).
     */
    public function history(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = array_map('intval', explode('-', $month));
        $start = Carbon::create($year, $monthNum, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $items = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->get()
            ->map(fn (Attendance $a) => $this->attendancePayload($a));

        return response()->json([
            'month' => $month,
            'items' => $items,
        ]);
    }

    private function attendancePayload(Attendance $a): array
    {
        return [
            'id' => $a->id,
            'date' => $a->date?->toDateString(),
            'check_in' => $a->check_in?->format('H:i'),
            'check_out' => $a->check_out?->format('H:i'),
            'status' => $a->status,
            'late_minutes' => $a->late_minutes,
            'work_minutes' => $a->work_minutes,
            'face_verified' => (bool) $a->face_verified,
            'distance_in' => $a->distance_in !== null ? round($a->distance_in) : null,
            'note' => $a->note,
        ];
    }
}
