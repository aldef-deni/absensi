<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminCompanyContextTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'super@admin.test')->firstOrFail();
    }

    public function test_super_admin_can_open_all_management_pages(): void
    {
        $user = $this->superAdmin();

        foreach (['/reports', '/employees', '/shifts', '/settings', '/attendance', '/leaves'] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }

    public function test_super_admin_reports_defaults_to_first_company(): void
    {
        $user = $this->superAdmin();

        $firstCompany = Company::query()->orderBy('id')->first();
        $employee = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('company_id', $firstCompany->id)
            ->firstOrFail();

        $this->actingAs($user)->get('/reports')
            ->assertOk()
            ->assertSee($employee->name);
    }

    public function test_super_admin_can_switch_company_on_reports(): void
    {
        $user = $this->superAdmin();

        $firstCompany = Company::query()->orderBy('id')->first();
        $secondCompany = Company::query()->orderBy('id')->skip(1)->first();

        $employee1 = User::query()->where('role', User::ROLE_EMPLOYEE)->where('company_id', $firstCompany->id)->firstOrFail();
        $employee2 = User::query()->where('role', User::ROLE_EMPLOYEE)->where('company_id', $secondCompany->id)->firstOrFail();

        $this->actingAs($user)->get('/reports?company_id='.$secondCompany->id)
            ->assertOk()
            ->assertSee($employee2->name)
            ->assertDontSee($employee1->name);
    }

    public function test_admin_cannot_see_other_company_data_via_company_id_param(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@nusantara.id')->firstOrFail();
        $other = Company::where('slug', 'pt-maju-bersama')->firstOrFail();
        $otherEmployee = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('company_id', $other->id)
            ->firstOrFail();

        $this->actingAs($admin)->get('/employees?company_id='.$other->id)
            ->assertOk()
            ->assertDontSee($otherEmployee->name);
    }
}
