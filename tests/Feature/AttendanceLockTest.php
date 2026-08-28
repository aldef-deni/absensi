<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\FaceTemplate;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\FaceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLockTest extends TestCase
{
    use RefreshDatabase;

    private function office(): array
    {
        // Sudirman, Jakarta
        return ['lat' => -6.2088, 'lng' => 106.8456];
    }

    private function farAway(): array
    {
        // Surabaya (~680 km)
        return ['lat' => -7.2575, 'lng' => 112.7521];
    }

    public function test_location_lock_rejects_check_in_outside_radius(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('slug', 'pt-nusantara-digital')->firstOrFail();
        $company->update([
            'latitude' => $this->office()['lat'],
            'longitude' => $this->office()['lng'],
            'radius_meters' => 500,
            'use_location_lock' => true,
        ]);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();

        $response = $this->actingAs($user)->post('/attendance/check-in', [
            'latitude_in' => $this->farAway()['lat'],
            'longitude_in' => $this->farAway()['lng'],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('attendances', ['user_id' => $user->id, 'date' => now()->toDateString()]);
    }

    public function test_location_lock_accepts_check_in_inside_radius(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('slug', 'pt-nusantara-digital')->firstOrFail();
        $company->update([
            'latitude' => $this->office()['lat'],
            'longitude' => $this->office()['lng'],
            'radius_meters' => 500,
            'use_location_lock' => true,
        ]);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();

        $this->actingAs($user)->post('/attendance/check-in', [
            'latitude_in' => $this->office()['lat'] + 0.001, // ~111 m dari kantor
            'longitude_in' => $this->office()['lng'],
        ]);

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now())->firstOrFail();
        $this->assertNotNull($attendance->check_in);
        $this->assertNotNull($attendance->distance_in);
        $this->assertLessThan(500, $attendance->distance_in);
    }

    public function test_face_biometric_requires_verification_when_template_exists(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('slug', 'pt-nusantara-digital')->firstOrFail();
        $company->update(['use_face_biometric' => true]);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();

        // Daftarkan template wajah dummy (vektor 128 dimensi).
        FaceTemplate::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'descriptor' => array_fill(0, 128, 0.01),
        ]);

        // Tanpa verifikasi wajah -> ditolak.
        $this->actingAs($user)->post('/attendance/check-in')
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('attendances', ['user_id' => $user->id, 'date' => now()->toDateString()]);

        // Dengan verifikasi wajah -> diterima.
        $this->actingAs($user)->post('/attendance/check-in', [
            'face_verified' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', ['user_id' => $user->id, 'face_verified' => 1]);
    }

    public function test_face_biometric_menolak_check_in_bila_wajah_belum_terdaftar(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('slug', 'pt-nusantara-digital')->firstOrFail();
        $company->update(['use_face_biometric' => true]);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();

        // Sejak biometrik wajah diwajibkan, wajah yang belum terdaftar tidak
        // boleh lolos. Sebelumnya check-in tetap diterima dengan flag wajah
        // false - dan itu membuat penguncian biometriknya bisa dilewati
        // sekadar dengan tidak pernah mendaftarkan wajah.
        $this->actingAs($user)->post('/attendance/check-in')
            ->assertSessionHas('error');

        // Dibatasi ke hari ini: seeder sudah mengisi riwayat absensi
        // sebelumnya untuk pengguna yang sama.
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_face_verify_endpoint_compares_descriptors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'maya@nusantara.id')->firstOrFail();
        $template = array_fill(0, 128, 0.5);

        FaceTemplate::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'descriptor' => $template,
        ]);

        // Vektor identik -> cocok.
        $this->actingAs($user)->postJson('/face/verify', ['descriptor' => $template])
            ->assertOk()
            ->assertJson(['verified' => true]);

        // Vektor sangat berbeda -> tidak cocok.
        $different = array_map(fn ($i) => ($i % 2 === 0 ? 1.0 : -1.0), range(0, 127));
        $this->actingAs($user)->postJson('/face/verify', ['descriptor' => $different])
            ->assertOk()
            ->assertJson(['verified' => false]);
    }

    public function test_attendance_today_feed_returns_stats_for_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@nusantara.id')->firstOrFail();

        $this->actingAs($admin)->getJson('/attendance/today')
            ->assertOk()
            ->assertJsonStructure(['stats' => ['total_employees', 'present', 'late', 'not_yet', 'on_leave'], 'rows']);
    }

    public function test_distance_calculation(): void
    {
        $service = new AttendanceService;
        $office = $this->office();

        // Titik yang sama -> 0 meter.
        $this->assertEquals(0.0, $service->distanceMeters($office['lat'], $office['lng'], $office['lat'], $office['lng']));

        // ~1 derajat lintang ~= 111 km.
        $distance = $service->distanceMeters($office['lat'], $office['lng'], $office['lat'] + 1, $office['lng']);
        $this->assertEqualsWithDelta(111_000, $distance, 5_000);
    }

    public function test_face_service_cosine_distance(): void
    {
        $service = new FaceService;

        $this->assertLessThan(0.01, $service->cosineDistance(array_fill(0, 128, 0.5), array_fill(0, 128, 0.5)));

        $opposite = array_fill(0, 128, 0.5);
        $flipped = array_map(fn ($v) => -$v, $opposite);
        $this->assertGreaterThan(1.5, $service->cosineDistance($opposite, $flipped));
    }
}
