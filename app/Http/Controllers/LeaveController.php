<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        $query = Leave::query()->with(['user', 'approver']);

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } elseif ($company) {
            $query->where('company_id', $company->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leaves = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('leaves.index', [
            'leaves' => $leaves,
            'companies' => $user->companyOptions($company),
            'companyId' => $company?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cuti_tahunan,izin,sakit'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $company) {
            return back()->with('error', 'Belum ada perusahaan yang bisa dikelola.');
        }

        Leave::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => Leave::STATUS_PENDING,
        ]);

        return back()->with('success', 'Pengajuan izin/cuti berhasil dikirim dan menunggu persetujuan.');
    }

    public function approve(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorizeLeave($request, $leave);

        $leave->update([
            'status' => Leave::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan '.$leave->user->name.' disetujui.');
    }

    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorizeLeave($request, $leave);

        $leave->update([
            'status' => Leave::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan '.$leave->user->name.' ditolak.');
    }

    /**
     * Batalkan pengajuan yang masih pending (hanya oleh pemiliknya).
     */
    public function cancel(Request $request, Leave $leave): RedirectResponse
    {
        if ($leave->user_id !== $request->user()->id || $leave->status !== Leave::STATUS_PENDING) {
            abort(403);
        }

        $leave->delete();

        return back()->with('success', 'Pengajuan dibatalkan.');
    }

    private function authorizeLeave(Request $request, Leave $leave): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $company = $request->user()->companyContext($request->integer('company_id'));

        if ($leave->company_id !== $company?->id) {
            abort(403);
        }

        if ($leave->status !== Leave::STATUS_PENDING) {
            abort(422, 'Pengajuan ini sudah diproses.');
        }
    }
}
