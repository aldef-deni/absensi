<?php

namespace App\Http\Controllers;

use App\Services\MobileApkService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private readonly MobileApkService $apk)
    {
    }

    /**
     * Halaman depan. Pengguna yang sudah masuk tidak perlu melihat halaman
     * pemasaran - langsung ke dashboard.
     */
    public function index(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return view('welcome', [
            'apk' => $this->apk->terbaru(),
            'demoEmail' => config('demo.email') ?: null,
            'demoPassword' => config('demo.password'),
            'demoResetJam' => (int) config('demo.reset_after_hours'),
        ]);
    }
}
