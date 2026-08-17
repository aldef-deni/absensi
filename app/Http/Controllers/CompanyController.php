<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $companies = Company::query()
            ->withCount(['employees', 'attendances'])
            ->orderBy('name')
            ->paginate(15);

        return view('companies.index', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        Company::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        return back()->with('success', 'Perusahaan berhasil dibuat.');
    }

    public function toggleStatus(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $company->update([
            'status' => $company->status === Company::STATUS_ACTIVE
                ? Company::STATUS_SUSPENDED
                : Company::STATUS_ACTIVE,
        ]);

        return back()->with('success', 'Status perusahaan diperbarui.');
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        if (! $request->user()->isSuperAdmin()) {
            abort(403);
        }
    }
}
