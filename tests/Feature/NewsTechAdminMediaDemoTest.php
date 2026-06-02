<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminMediaDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_demo_media_upload_page(): void
    {
        $response = $this->get(route('admin.newstech.foundation.media-demo.index'));

        $response->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_demo_media_upload_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.media-demo.index'));

        $response->assertOk();
        $response->assertSee('Media upload foundation is ready for future editorial modules.');
        $response->assertSee('Upload Demo File');
    }

    public function test_valid_uploaded_file_is_stored(): void
    {
        Storage::fake('public');

        $adminUser = AdminUser::factory()->create();
        $file = UploadedFile::fake()->create('cover.jpg', 250, 'image/jpeg');

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.foundation.media-demo.store'), [
            'upload' => $file,
        ]);

        $response->assertRedirect(route('admin.newstech.foundation.media-demo.index'));
        $response->assertSessionHas('media_demo_upload');

        $storedPath = session('media_demo_upload.path');

        $this->assertIsString($storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_invalid_file_is_rejected(): void
    {
        Storage::fake('public');

        $adminUser = AdminUser::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 250, 'application/pdf');

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.foundation.media-demo.index'))
            ->post(route('admin.newstech.foundation.media-demo.store'), [
                'upload' => $file,
            ]);

        $response->assertRedirect(route('admin.newstech.foundation.media-demo.index'));
        $response->assertInvalid(['upload']);
        Storage::disk('public')->assertDirectoryEmpty(config('newstech.media.path'));
    }
}
