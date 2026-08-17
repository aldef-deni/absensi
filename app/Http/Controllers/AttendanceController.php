<?php

namespace App\Http\Controllers;

use App\Exceptions\AttendanceCheckException;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service)
    {
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
            'latitude_in' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_in' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        try {
            $attendance = $this->service->checkIn(
                $request->user(),
                $request->note,
                $request->float('latitude_in'),
                $request->float('longitude_in'),
                $request->boolean('face_verified'),
            );
        } catch (AttendanceCheckException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $attendance) {
            return back()->with('error', 'Kamu sudah melakukan check-in hari ini.');
        }

        $parts = [];

        if ($request->boolean('face_verified')) {
            $parts[] = 'wajah terverifikasi';
        }

        if ($attendance->distance_in !== null) {
            $parts[] = round($attendance->distance_in).' m dari kantor';
        }

        $message = $attendance->status === Attendance::STATUS_LATE
            ? 'Check-in berhasil (telat '.$attendance->late_minutes.' menit'.($parts ? ', '.implode(', ', $parts) : '').').'
            : 'Check-in berhasil. Selamat bekerja!'.($parts ? ' ('.implode(', ', $parts).')' : '');

        return back()->with('success', $message);
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $request->validate([
            'latitude_out' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_out' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        try {
            $attendance = $this->service->checkOut(
                $request->user(),
                $request->float('latitude_out'),
                $request->float('longitude_out'),
                $request->boolean('face_verified'),
            );
        } catch (AttendanceCheckException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $attendance) {
            return back()->with('error', 'Tidak ada check-in aktif untuk di-check-out.');
        }

        $hours = round($attendance->work_minutes / 60, 1);

        return back()->with('success', 'Check-out berhasil. Total jam kerja hari ini: '.$hours.' jam.');
    }

    /**
     * Feed real-time untuk polling dashboard admin.
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isEmployee()) {
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', Carbon::today()->toDateString())
                ->first();

            return response()->json([
                'attendance' => $attendance ? [
                    'check_in' => $attendance->check_in?->format('H:i'),
                    'check_out' => $attendance->check_out?->format('H:i'),
                    'status' => $attendance->status,
                    'late_minutes' => $attendance->late_minutes,
                    'face_verified' => $attendance->face_verified,
                    'distance_in' => $attendance->distance_in,
                ] : null,
            ]);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['role' => 'super_admin']);
        }

        $today = Carbon::today();

        $employees = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();

        $todayAttendances = Attendance::query()
            ->whereDate('date', $today->toDateString())
            ->get()
            ->keyBy('user_id');

        $present = $todayAttendances->where('status', Attendance::STATUS_PRESENT)->count();
        $late = $todayAttendances->where('status', Attendance::STATUS_LATE)->count();

        $onLeave = Leave::query()
            ->where('status', Leave::STATUS_APPROVED)
            ->where('start_date', '<=', $today->toDateString())
            ->where('end_date', '>=', $today->toDateString())
            ->count();

        $rows = $employees->map(function (User $employee) use ($todayAttendances) {
            $a = $todayAttendances->get($employee->id);

            return [
                'name' => $employee->name,
                'position' => $employee->position,
                'check_in' => $a?->check_in?->format('H:i'),
                'check_out' => $a?->check_out?->format('H:i'),
                'status' => $a?->status,
                'late_minutes' => $a?->late_minutes,
                'face_verified' => (bool) $a?->face_verified,
                'distance_in' => $a?->distance_in,
                'note' => $a?->note,
            ];
        });

        return response()->json([
            'stats' => [
                'total_employees' => $employees->count(),
                'present' => $present,
                'late' => $late,
                'not_yet' => max(0, $employees->count() - $todayAttendances->count()),
                'on_leave' => $onLeave,
            ],
            'rows' => $rows,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        $query = Attendance::query()->with('user');

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } elseif ($company) {
            $query->where('company_id', $company->id);
        }

        if ($request->filled('user_id') && $user->isAdmin()) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $attendances = $query->orderByDesc('date')->orderByDesc('check_in')->paginate(15)->withQueryString();

        $employees = $user->isAdmin() && $company
            ? User::query()
                ->where('role', User::ROLE_EMPLOYEE)
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get()
            : collect();

        return view('attendance.index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'companies' => $user->companyOptions($company),
            'companyId' => $company?->id,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        $query = Attendance::query()->with('user');

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } elseif ($company) {
            $query->where('company_id', $company->id);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $attendances = $query->orderByDesc('date')->get();

        $filename = 'absensi-'.Carbon::today()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($attendances) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Nama', 'NIP', 'Check In', 'Check Out', 'Status', 'Telat (menit)', 'Jam Kerja', 'Wajah', 'Jarak Kantor (m)', 'Catatan']);

            foreach ($attendances as $a) {
                fputcsv($out, [
                    $a->date?->format('d/m/Y'),
                    $a->user?->name,
                    $a->user?->employee_code,
                    $a->check_in?->format('H:i:s'),
                    $a->check_out?->format('H:i:s'),
                    $a->status === Attendance::STATUS_LATE ? 'Telat' : 'Hadir',
                    $a->late_minutes,
                    $a->work_minutes !== null ? number_format($a->work_minutes / 60, 1).' jam' : '',
                    $a->face_verified ? 'Ya' : 'Tidak',
                    $a->distance_in !== null ? round($a->distance_in) : '',
                    $a->note,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
