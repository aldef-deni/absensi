<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        return $this->employeeDashboard($user);
    }

    private function superAdminDashboard(): View
    {
        $companies = Company::query()
            ->withCount(['employees', 'attendances'])
            ->orderBy('name')
            ->paginate(10);

        return view('dashboard', [
            'roleView' => 'superadmin',
            'companies' => $companies,
            'totalCompanies' => Company::count(),
            'totalUsers' => User::count(),
        ]);
    }

    private function adminDashboard(User $user): View
    {
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
        $checkedIn = $todayAttendances->count();
        $notYet = max(0, $employees->count() - $checkedIn);

        $todayApprovedLeaves = Leave::query()
            ->where('status', Leave::STATUS_APPROVED)
            ->where('start_date', '<=', $today->toDateString())
            ->where('end_date', '>=', $today->toDateString())
            ->get();

        $recent = Attendance::query()
            ->with('user')
            ->whereDate('date', $today->toDateString())
            ->orderByDesc('check_in')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'roleView' => 'admin',
            'totalEmployees' => $employees->count(),
            'present' => $present,
            'late' => $late,
            'notYet' => $notYet,
            'onLeave' => $todayApprovedLeaves->count(),
            'pendingLeaves' => Leave::query()
                ->where('status', Leave::STATUS_PENDING)
                ->count(),
            'recent' => $recent,
        ]);
    }

    private function employeeDashboard(User $user): View
    {
        $today = Carbon::today();

        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->first();

        $history = Attendance::query()
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'roleView' => 'employee',
            'company' => $user->company,
            'absensiConfig' => [
                'locationLock' => (bool) ($user->company?->use_location_lock),
                'faceBiometric' => (bool) ($user->company?->use_face_biometric),
                'radius' => (int) ($user->company?->radius_meters ?? 0),
            ],
            'todayAttendance' => $todayAttendance,
            'history' => $history,
            'pendingLeaves' => Leave::query()
                ->where('user_id', $user->id)
                ->where('status', Leave::STATUS_PENDING)
                ->count(),
            'monthCount' => Attendance::query()
                ->where('user_id', $user->id)
                ->whereBetween('date', [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()])
                ->count(),
        ]);
    }
}
