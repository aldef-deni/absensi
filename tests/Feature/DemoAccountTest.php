<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use App\Services\DemoResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountTest extends TestCase
{
    use RefreshDatabase;

    private function demo(): DemoResetService
    {
        return app(DemoResetService::class);
    }

    private function akunDemo(): User
    {
        return User::where('email', config('demo.email'))->firstOrFail();
    }

    private function perusahaanDemo(): Company
    {
        return Company::where('name', config('demo.company'))->firstOrFail();
    }

    public function test_pemulihan_membangun_akun_beserta_isi_contohnya(): void
    {
        $this->demo()->pulihkan();

        $admin = $this->akunDemo();
        $company = $this->perusahaanDemo();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check(config('demo.password'), $admin->password));

        // Perusahaan tersendiri: akun demo berperan admin, dan admin melihat
        // seluruh karyawan di perusahaannya. Menaruhnya di perusahaan asli
        // berarti membuka data absensi pelanggan kepada siapa pun.
        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame('active', $company->status);

        // Dashboard yang kosong tidak menunjukkan apa pun tentang produknya.
        $this->assertSame(
            (int) config('demo.employees'),
            User::where('company_id', $company->id)->where('role', User::ROLE_EMPLOYEE)->count()
        );
        $this->assertGreaterThan(0, Shift::where('company_id', $company->id)->count());
        $this->assertGreaterThan(0, Attendance::where('company_id', $company->id)->count());
        $this->assertGreaterThan(0, Leave::where('company_id', $company->id)->count());
    }

    public function test_isi_contoh_tidak_seragam_supaya_laporan_ada_gunanya(): void
    {
        $this->demo()->pulihkan();
        $company = $this->perusahaanDemo();

        // Kehadiran 100% tepat waktu menyembunyikan gunanya laporan
        // keterlambatan; harus ada yang telat dan ada pengajuan yang menunggu.
        $this->assertGreaterThan(0,
            Attendance::where('company_id', $company->id)->where('status', 'late')->count());
        $this->assertGreaterThan(0,
            Leave::where('company_id', $company->id)->where('status', 'pending')->count());
        $this->assertGreaterThan(0,
            Leave::where('company_id', $company->id)->where('status', 'approved')->count());
    }

    public function test_pemulihan_menghapus_jejak_pengunjung_sebelumnya(): void
    {
        $this->demo()->pulihkan();
        $admin = $this->akunDemo();
        $company = $this->perusahaanDemo();

        // Pengunjung bisa melakukan apa saja di dalam demo.
        $admin->update([
            'password' => 'sudahDiubah99',
            'name' => 'Diubah Pengunjung',
            'is_active' => false,
        ]);

        $titipan = User::create([
            'company_id' => $company->id,
            'name' => 'Karyawan Titipan',
            'email' => 'titipan@demo.test',
            'password' => 'rahasia12345',
            'role' => User::ROLE_EMPLOYEE,
            'is_active' => true,
        ]);

        Shift::create([
            'company_id' => $company->id,
            'name' => 'Shift Iseng',
            'start_time' => '00:00:00',
            'end_time' => '23:59:00',
            'grace_minutes' => 999,
            'is_active' => true,
        ]);

        $this->demo()->pulihkan();

        $pulih = $this->akunDemo();

        $this->assertTrue(Hash::check(config('demo.password'), $pulih->password),
            'Password yang diganti pengunjung harus kembali');
        $this->assertTrue($pulih->is_active);
        $this->assertSame(config('demo.name'), $pulih->name);

        $this->assertDatabaseMissing('users', ['email' => 'titipan@demo.test']);
        $this->assertDatabaseMissing('shifts', ['name' => 'Shift Iseng']);
    }

    public function test_pemulihan_tidak_menyentuh_perusahaan_lain(): void
    {
        $lain = Company::create([
            'name' => 'PT Pelanggan Asli',
            'slug' => 'pt-pelanggan-asli',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $pelanggan = User::create([
            'company_id' => $lain->id,
            'name' => 'Karyawan Asli',
            'email' => 'asli@pelanggan.test',
            'password' => 'rahasia12345',
            'role' => User::ROLE_EMPLOYEE,
            'is_active' => true,
        ]);

        $this->demo()->pulihkan();

        $this->assertDatabaseHas('users', ['id' => $pelanggan->id, 'name' => 'Karyawan Asli']);
        $this->assertDatabaseHas('companies', ['id' => $lain->id]);
    }

    public function test_pemulihan_hanya_sekali_dalam_selang_waktunya(): void
    {
        $this->assertTrue($this->demo()->pulihkanBilaPerlu(), 'Pertama kali selalu dipulihkan');

        // Tiap percobaan masuk memicu pemeriksaan ini. Kalau membangun ulang
        // setiap kali, pekerjaan orang yang sedang mencoba akan terhapus.
        $this->assertFalse($this->demo()->pulihkanBilaPerlu());
    }

    public function test_masuk_memicu_pemulihan_walau_password_sudah_diganti(): void
    {
        $this->demo()->pulihkan();
        $this->akunDemo()->update(['password' => 'dikunciPengunjung']);

        // Waktunya dilewatkan supaya pemulihan berikutnya memang terjadwal.
        cache()->forget('demo_last_reset');

        $this->post('/login', [
            'email' => config('demo.email'),
            'password' => config('demo.password'),
        ]);

        $this->assertAuthenticated();
    }

    public function test_perintah_server_memulihkan_akun_super_admin(): void
    {
        // Peran ini tidak bisa diberikan lewat antarmuka mana pun, jadi tanpa
        // jalur server sekali hilang berarti terkunci selamanya.
        $this->artisan('absensi:superadmin', [
            '--email' => 'pemulihan@aldeftech.test',
            '--password' => 'rahasia12345',
        ])->assertSuccessful();

        $baru = User::withoutGlobalScopes()
            ->where('email', 'pemulihan@aldeftech.test')
            ->firstOrFail();

        $this->assertSame(User::ROLE_SUPER_ADMIN, $baru->role);
        $this->assertTrue($baru->is_active);
        $this->assertTrue(Hash::check('rahasia12345', $baru->password));
        $this->assertNull($baru->company_id,
            'Super admin tidak boleh terikat satu perusahaan - itu justru mempersempit pandangannya');
    }

    public function test_perintah_server_mengangkat_akun_yang_sudah_ada(): void
    {
        $company = Company::create([
            'name' => 'PT Turun Peran',
            'slug' => 'pt-turun-peran',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $korban = User::create([
            'company_id' => $company->id,
            'name' => 'Pernah Super',
            'email' => 'turun@aldeftech.test',
            'password' => 'lamaSekali99',
            'role' => User::ROLE_EMPLOYEE,
            'is_active' => false,
        ]);

        $this->artisan('absensi:superadmin', [
            '--email' => 'turun@aldeftech.test',
            '--password' => 'rahasia12345',
        ])->assertSuccessful();

        $pulih = $korban->fresh();

        $this->assertSame(User::ROLE_SUPER_ADMIN, $pulih->role);
        $this->assertTrue($pulih->is_active, 'Akun nonaktif ikut diaktifkan kembali');
        $this->assertNull($pulih->company_id);
        $this->assertSame($korban->id, $pulih->id, 'Akun yang sama, bukan akun baru');
    }

    public function test_perintah_server_menolak_password_lemah(): void
    {
        $this->artisan('absensi:superadmin', [
            '--email' => 'lemah@aldeftech.test',
            '--password' => 'pendek',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'lemah@aldeftech.test']);
    }

    public function test_layar_masuk_menyediakan_tombol_demo(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('isiDemo()', $html);
        $this->assertStringContainsString((string) config('demo.email'), $html);
        $this->assertStringContainsString('Coba Demo', $html);
    }

    public function test_tombol_demo_hilang_bila_fitur_dimatikan(): void
    {
        config(['demo.email' => '']);

        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringNotContainsString('isiDemo()', $html);
    }
}
