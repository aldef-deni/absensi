<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Leave;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_company(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/login', [
            'email' => 'budi@nusantara.id',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'role', 'company' => ['id', 'name', 'radius_meters']]]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/login', [
            'email' => 'budi@nusantara.id',
            'password' => 'salah',
        ])->assertStatus(422);
    }

    public function test_today_returns_attendance_and_company_settings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'budi@nusantara.id')->firstOrFail();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/attendance/today')
            ->assertOk()
            ->assertJsonStructure(['date', 'company' => ['name', 'latitude', 'use_location_lock'], 'attendance']);
    }

    public function test_check_in_and_out_flow(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('slug', 'pt-nusantara-digital')->firstOrFail();
        $company->update([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 500,
            'use_location_lock' => true,
        ]);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();
        $token = $user->createToken('test')->plainTextToken;

        // Check-in dari luar radius -> ditolak.
        $this->withToken($token)->postJson('/api/attendance/check-in', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
        ])->assertStatus(422);

        // Check-in dari dalam radius -> diterima.
        $this->withToken($token)->postJson('/api/attendance/check-in', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'face_verified' => true,
        ])->assertStatus(201)
            ->assertJsonPath('attendance.face_verified', true);

        $this->assertDatabaseHas('attendances', ['user_id' => $user->id, 'date' => now()->toDateString()]);

        // Check-out.
        $this->withToken($token)->postJson('/api/attendance/check-out', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ])->assertOk()
            ->assertJsonPath('attendance.check_out', now()->format('H:i'));

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now())->firstOrFail();
        $this->assertNotNull($attendance->check_out);
    }

    public function test_history_returns_records_for_month(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'budi@nusantara.id')->firstOrFail();
        $token = $user->createToken('test')->plainTextToken;

        $month = now()->format('Y-m');

        $this->withToken($token)->getJson('/api/attendance/history?month='.$month)
            ->assertOk()
            ->assertJsonStructure(['month', 'items']);
    }

    public function test_leaves_list_and_create(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'budi@nusantara.id')->firstOrFail();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/leaves')
            ->assertOk()
            ->assertJsonStructure(['items']);

        $this->withToken($token)->postJson('/api/leaves', [
            'type' => 'izin',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Acara keluarga',
        ])->assertStatus(201)
            ->assertJsonPath('leave.status', Leave::STATUS_PENDING);

        $this->assertDatabaseHas('leaves', ['user_id' => $user->id, 'reason' => 'Acara keluarga']);
    }

    public function test_protected_routes_require_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
        $this->getJson('/api/attendance/today')->assertStatus(401);
    }
}
