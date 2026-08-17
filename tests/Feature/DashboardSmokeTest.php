<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = \App\Models\User::where('email', 'super@admin.test')->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/companies')->assertOk();
    }

    public function test_admin_dashboard_renders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = \App\Models\User::where('email', 'admin@nusantara.id')->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/attendance')->assertOk();
        $this->actingAs($user)->get('/leaves')->assertOk();
        $this->actingAs($user)->get('/employees')->assertOk();
        $this->actingAs($user)->get('/shifts')->assertOk();
        $this->actingAs($user)->get('/reports')->assertOk();
    }

    public function test_employee_dashboard_renders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = \App\Models\User::where('email', 'budi@nusantara.id')->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/attendance')->assertOk();
        $this->actingAs($user)->get('/leaves')->assertOk();
    }
}
