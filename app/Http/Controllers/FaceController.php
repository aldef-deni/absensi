<?php

namespace App\Http\Controllers;

use App\Models\FaceTemplate;
use App\Models\User;
use App\Services\FaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaceController extends Controller
{
    public function __construct(private readonly FaceService $faceService)
    {
    }

    /**
     * Halaman pendaftaran wajah (enrollment) untuk user yang login.
     */
    public function enroll(Request $request): View
    {
        $template = FaceTemplate::query()->where('user_id', $request->user()->id)->first();

        return view('face.enroll', [
            'template' => $template,
        ]);
    }

    /**
     * Halaman admin: daftar karyawan yang wajahnya sudah terverifikasi,
     * lengkap dengan tombol reset (khusus superadmin).
     */
    public function index(Request $request): View
    {
        $company = $request->user()->companyContext();

        $users = User::query()
            ->with('faceTemplate')
            ->when($company, fn ($q) => $q->where('company_id', $company->id))
            ->where(function ($q) {
                $q->whereNotNull('face_registered_at')
                    ->orWhereHas('faceTemplate');
            })
            ->orderBy('name')
            ->get();

        return view('face.index', [
            'users' => $users,
            'company' => $company,
        ]);
    }

    /**
     * Reset wajah karyawan (hapus data biometrik). Hanya superadmin.
     */
    public function reset(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya superadmin yang bisa reset wajah.');

        FaceTemplate::query()->where('user_id', $user->id)->delete();
        $user->forceFill(['face_registered_at' => null])->save();

        return back()->with('status', "Wajah {$user->name} berhasil di-reset. Karyawan wajib mendaftarkan wajah lagi.");
    }

    /**
     * Cek apakah user sudah mendaftarkan wajah (untuk JS).
     */
    public function template(Request $request): JsonResponse
    {
        $template = FaceTemplate::query()->where('user_id', $request->user()->id)->first();

        return response()->json([
            'exists' => (bool) $template,
            'enrolled_at' => $template?->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Simpan / perbarui template wajah (vektor 128 dimensi dari browser).
     * Registrasi dicatat sekali — reset hanya bisa dilakukan superadmin.
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
        ]);

        FaceTemplate::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'company_id' => $request->user()->companyContext()?->id ?? $request->user()->company_id,
                'descriptor' => array_map('floatval', $validated['descriptor']),
            ]
        );

        $request->user()->forceFill(['face_registered_at' => now()])->save();

        return response()->json(['success' => true]);
    }

    /**
     * Bandingkan vektor wajah yang ditangkap kamera dengan template tersimpan.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
        ]);

        $template = FaceTemplate::query()->where('user_id', $request->user()->id)->first();

        if (! $template) {
            return response()->json(['verified' => false, 'reason' => 'no_template']);
        }

        $distance = $this->faceService->cosineDistance($template->descriptor, $validated['descriptor']);

        return response()->json([
            'verified' => $distance <= FaceService::MATCH_DISTANCE,
            'distance' => round($distance, 4),
        ]);
    }
}
