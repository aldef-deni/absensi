<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        return view('settings.index', [
            'company' => $company,
            'companies' => $user->companyOptions($company),
            'companyId' => $company?->id,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:20000'],
            'use_location_lock' => ['sometimes', 'boolean'],
            'use_face_biometric' => ['sometimes', 'boolean'],
        ]);

        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $company) {
            return back()->with('error', 'Belum ada perusahaan yang bisa dikelola.');
        }

        $company->update([
            'latitude' => $validated['latitude'] !== null ? $validated['latitude'] : null,
            'longitude' => $validated['longitude'] !== null ? $validated['longitude'] : null,
            'radius_meters' => (int) ($validated['radius_meters'] ?? 100),
            'use_location_lock' => $request->boolean('use_location_lock'),
            'use_face_biometric' => $request->boolean('use_face_biometric'),
        ]);

        return back()->with('success', 'Pengaturan absensi diperbarui.');
    }
}
