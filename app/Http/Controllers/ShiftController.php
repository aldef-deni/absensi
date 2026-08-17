<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        $shifts = Shift::query()
            ->when($company, fn ($q) => $q->where('company_id', $company->id))
            ->orderBy('start_time')
            ->get();

        return view('shifts.index', [
            'shifts' => $shifts,
            'companies' => $user->companyOptions($company),
            'companyId' => $company?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
        ]);

        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $company) {
            return back()->with('error', 'Belum ada perusahaan yang bisa dikelola.');
        }

        Shift::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_minutes' => $validated['grace_minutes'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    public function toggle(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizeShift($request, $shift);

        $shift->update(['is_active' => ! $shift->is_active]);

        return back()->with('success', $shift->is_active ? 'Shift diaktifkan.' : 'Shift dinonaktifkan.');
    }

    public function destroy(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizeShift($request, $shift);

        $shift->delete();

        return back()->with('success', 'Shift dihapus.');
    }

    private function authorizeShift(Request $request, Shift $shift): void
    {
        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $request->user()->isAdmin() || $shift->company_id !== $company?->id) {
            abort(403);
        }
    }
}
