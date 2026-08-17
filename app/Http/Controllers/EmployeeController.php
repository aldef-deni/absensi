<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));

        $query = User::query()->where('role', User::ROLE_EMPLOYEE);

        if ($company) {
            $query->where('company_id', $company->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $employees = $query->withCount('attendances')->orderBy('name')->paginate(15)->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'companies' => $user->companyOptions($company),
            'companyId' => $company?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $company) {
            return back()->with('error', 'Belum ada perusahaan yang bisa dikelola.');
        }

        User::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_EMPLOYEE,
            'employee_code' => $validated['employee_code'] ?: 'EMP-'.Str::upper(Str::random(6)),
            'position' => $validated['position'],
            'phone' => $validated['phone'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEmployee($request, $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'employee_code' => $validated['employee_code'],
            'position' => $validated['position'],
            'phone' => $validated['phone'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return back()->with('success', 'Data karyawan diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEmployee($request, $user);

        $user->delete();

        return back()->with('success', 'Karyawan dihapus.');
    }

    private function authorizeEmployee(Request $request, User $user): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        if ($user->role !== User::ROLE_EMPLOYEE) {
            abort(404);
        }

        $company = $request->user()->companyContext($request->integer('company_id'));

        if ($user->company_id !== $company?->id) {
            abort(403);
        }
    }
}
