<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /** APK contoh yang dibuat selama pengujian, dihapus lagi sesudahnya. */
    private ?string $apkSementara = null;

    protected function tearDown(): void
    {
        if ($this->apkSementara && file_exists($this->apkSementara)) {
            unlink($this->apkSementara);
        }

        parent::tearDown();
    }

    private function buatApk(string $nama = 'ALDEF-Absensi-v9.9.apk'): void
    {
        $folder = public_path('downloads');

        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $this->apkSementara = $folder.'/'.$nama;
        file_put_contents($this->apkSementara, str_repeat('x', 2 * 1048576));
    }

    /**
     * APK asli mungkin sedang ada di public/downloads pada mesin pengembang.
     * Pengujian "belum ada APK" hanya masuk akal bila foldernya memang kosong.
     */
    private function lewatiBilaAdaApkAsli(): void
    {
        if (glob(public_path('downloads/*.apk'))) {
            $this->markTestSkipped('Ada APK asli di public/downloads.');
        }
    }

    public function test_halaman_depan_menampilkan_kartu_unduhan_saat_apk_tersedia(): void
    {
        $this->buatApk();

        $this->get('/')
            ->assertOk()
            ->assertSee('Unduh APK', false)
            ->assertSee('ALDEF-Absensi-v9.9.apk', false)
            ->assertSee('v9.9', false)
            ->assertSee('2,0 MB', false);
    }

    public function test_halaman_depan_menyembunyikan_kartu_unduhan_saat_apk_belum_diunggah(): void
    {
        $this->lewatiBilaAdaApkAsli();

        $this->get('/')
            ->assertOk()
            ->assertSee('APK sedang disiapkan', false)
            ->assertDontSee('Unduh APK (', false);
    }

    public function test_halaman_depan_menampilkan_akun_demo(): void
    {
        config(['demo.email' => 'demo@aldeftech.com', 'demo.password' => 'demo12345']);

        $this->get('/')
            ->assertOk()
            ->assertSee('demo@aldeftech.com', false)
            ->assertSee('demo12345', false);
    }

    public function test_akun_demo_tidak_ditampilkan_saat_fitur_demo_dimatikan(): void
    {
        config(['demo.email' => '']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Pakai akun demo', false)
            ->assertSee('Punya akun perusahaan?', false);
    }

    public function test_pengguna_yang_sudah_masuk_dialihkan_ke_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
