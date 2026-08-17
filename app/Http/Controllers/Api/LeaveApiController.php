<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Leave::query()->with('user');

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } else {
            $company = $user->companyContext($request->integer('company_id'));

            if ($company) {
                $query->where('company_id', $company->id);
            }
        }

        $items = $query->orderByDesc('created_at')->limit(50)->get()->map(
            fn (Leave $leave) => $this->payload($leave)
        );

        return response()->json(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cuti_tahunan,izin,sakit'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $leave = Leave::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => Leave::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Pengajuan izin/cuti berhasil dikirim.',
            'leave' => $this->payload($leave->load('user')),
        ], 201);
    }

    private function payload(Leave $leave): array
    {
        return [
            'id' => $leave->id,
            'type' => $leave->type,
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'days' => $leave->days(),
            'reason' => $leave->reason,
            'status' => $leave->status,
            'user_name' => $leave->user?->name,
            'created_at' => $leave->created_at?->toIso8601String(),
        ];
    }
}
