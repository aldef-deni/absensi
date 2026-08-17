<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun kamu dinonaktifkan.'], 403);
        }

        $token = $user->createToken('mobile', $this->abilitiesFor($user))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    private function abilitiesFor(User $user): array
    {
        return match ($user->role) {
            User::ROLE_SUPER_ADMIN => ['*'],
            User::ROLE_ADMIN => ['attendance:read', 'attendance:write', 'leaves:manage'],
            default => ['attendance:read', 'attendance:write', 'leaves:self'],
        };
    }

    private function userPayload(User $user): array
    {
        $company = $user->company;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'employee_code' => $user->employee_code,
            'position' => $user->position,
            'photo_url' => $user->avatarUrl(),
            'is_active' => $user->is_active,
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'latitude' => $company->latitude,
                'longitude' => $company->longitude,
                'radius_meters' => $company->radius_meters,
                'use_location_lock' => (bool) $company->use_location_lock,
                'use_face_biometric' => (bool) $company->use_face_biometric,
            ] : null,
        ];
    }
}
