<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\DemoResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Kredensial demo dikirim ke tampilan supaya tombolnya bisa mengisi
        // kolom. Aman ditampilkan: akun ini memang untuk dicoba siapa saja,
        // dan isinya dibangun ulang berkala.
        return view('auth.login', [
            'demoEmail' => config('demo.email') ?: null,
            'demoPassword' => config('demo.password'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, DemoResetService $demo): RedirectResponse
    {
        // Pemulihan demo dijalankan SEBELUM autentikasi. Kalau menunggu login
        // berhasil, pengunjung yang mengganti password akun demo akan mengunci
        // semua orang - pemulihannya tidak akan pernah terpicu lagi.
        if ($demo->cocokDenganDemo($request->input('email'))) {
            $demo->pulihkanBilaPerlu();
        }

        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
