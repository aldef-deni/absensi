<?php

namespace App\Http\Controllers;

use App\Models\FaceTemplate;
use App\Services\FaceService;
use Illuminate\Http\JsonResponse;
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
