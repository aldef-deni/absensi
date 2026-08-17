<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_photo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'budi@nusantara.id')->firstOrFail();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Budi Santoso',
            'email' => 'budi@nusantara.id',
            'photo' => UploadedFile::fake()->image('foto.png', 200, 200),
        ])->assertSessionHas('status', 'profile-updated');

        $user->refresh();

        $this->assertNotNull($user->photo);
        $this->assertFileExists(public_path('uploads/photos/'.$user->photo));
        $this->assertStringContainsString($user->photo, $user->avatarUrl());

        Storage::disk('photos')->delete($user->photo);
    }

    public function test_profile_photo_must_be_valid_image(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'budi@nusantara.id')->firstOrFail();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Budi Santoso',
            'email' => 'budi@nusantara.id',
            'photo' => UploadedFile::fake()->create('dokumen.txt', 100),
        ])->assertSessionHasErrors('photo');

        $user->refresh();
        $this->assertNull($user->photo);
    }

    public function test_avatar_url_falls_back_to_initials_without_photo(): void
    {
        $user = new User(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);

        $this->assertSame('BS', $user->avatarInitials());
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $user->avatarUrl());
    }
}
