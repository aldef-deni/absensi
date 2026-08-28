<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaceApiController extends Controller
{
    /**
     * Status pendaftaran wajah user (untuk aplikasi mobile).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'registered' => $user->faceRegistered(),
            'enrolled_at' => $user->face_registered_at?->toIso8601String(),
        ]);
    }

    /**
     * Daftarkan wajah untuk biometrik. Hanya boleh SEKALI —
     * untuk mendaftar ulang, superadmin harus reset dari dashboard admin.
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->faceRegistered()) {
            return response()->json([
                'message' => 'Wajah sudah terdaftar. Untuk mendaftar ulang, hubungi superadmin untuk reset.',
            ], 422);
        }

        $user->forceFill(['face_registered_at' => now()])->save();

        return response()->json([
            'registered' => true,
            'enrolled_at' => $user->fresh()->face_registered_at?->toIso8601String(),
            'message' => 'Wajah berhasil didaftarkan.',
        ], 201);
    }
}
