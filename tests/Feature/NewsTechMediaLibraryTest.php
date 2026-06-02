<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Media\Models\Media;
use Tests\TestCase;

class NewsTechMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_media_admin_page(): void
    {
        $this->get(route('admin.newstech.media.index'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_authenticated_admin_can_access_media_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.media.index'));

        $response->assertOk();
        $response->assertSee('Media library');
        $response->assertSee('data-media-library-root="true"', false);
        $response->assertSee('data-media-library-config', false);
        $response->assertSee('data-vue-mount-status="pending"', false);
    }

    public function test_media_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Media');
        $response->assertSee(route('admin.newstech.media.index'), false);
    }

    public function test_authenticated_admin_can_upload_image_to_media_library(): void
    {
        Storage::fake('public');
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.media.store'), [
            'file' => UploadedFile::fake()->image('homepage-hero.png'),
            'alt_text' => 'Homepage hero image',
            'caption' => 'Library upload caption',
        ]);

        $response->assertRedirect(route('admin.newstech.media.index'));

        /** @var Media $media */
        $media = Media::query()->latest('id')->firstOrFail();

        $this->assertSame('Homepage hero image', $media->alt_text);
        $this->assertSame('Library upload caption', $media->caption);
        $this->assertSame('webp', $media->extension);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertStringEndsWith('.webp', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_authenticated_admin_can_upload_multiple_images_to_media_library(): void
    {
        Storage::fake('public');
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.media.store'), [
            'files' => [
                UploadedFile::fake()->image('batch-one.png'),
                UploadedFile::fake()->image('batch-two.png'),
            ],
            'alt_text' => 'Batch alt text',
            'caption' => 'Batch caption',
        ]);

        $response->assertRedirect(route('admin.newstech.media.index'));
        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('media', [
            'original_name' => 'batch-one.png',
            'alt_text' => 'Batch alt text',
            'caption' => 'Batch caption',
            'extension' => 'webp',
        ]);
        $this->assertDatabaseHas('media', [
            'original_name' => 'batch-two.png',
            'alt_text' => 'Batch alt text',
            'caption' => 'Batch caption',
            'extension' => 'webp',
        ]);
    }

    public function test_picker_upload_endpoint_still_works(): void
    {
        Storage::fake('public');
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->postJson(route('admin.newstech.media.picker.upload'), [
            'file' => UploadedFile::fake()->image('picker-upload.png'),
            'alt_text' => 'Picker upload',
            'caption' => 'Picker caption',
        ]);

        $response->assertOk()
            ->assertJsonPath('media.alt_text', 'Picker upload')
            ->assertJsonPath('media.caption', 'Picker caption');
    }

    public function test_media_update_endpoint_returns_json_for_picker_details_panel(): void
    {
        $adminUser = AdminUser::factory()->create();
        $media = Media::factory()->create([
            'alt_text' => null,
            'caption' => null,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->putJson(route('admin.newstech.media.update', $media), [
            'alt_text' => 'Picker details alt',
            'caption' => 'Picker details caption',
        ]);

        $response->assertOk()
            ->assertJsonPath('media.alt_text', 'Picker details alt')
            ->assertJsonPath('media.caption', 'Picker details caption');
    }

    public function test_media_index_shows_uploaded_image_without_inline_metadata(): void
    {
        $adminUser = AdminUser::factory()->create();
        Media::factory()->create([
            'original_name' => 'city-skyline.jpg',
            'filename' => 'city-skyline.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.media.index'));

        $response->assertOk();
        $response->assertSee('city-skyline.jpg');
        $response->assertSee('data-media-library-config', false);
    }

    public function test_media_index_uses_pagination(): void
    {
        $adminUser = AdminUser::factory()->create();

        foreach (range(1, 13) as $index) {
            Media::factory()->create([
                'original_name' => "asset-{$index}.jpg",
                'filename' => "asset-{$index}.jpg",
            ]);
        }

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.media.index'));

        $response->assertOk();
        $response->assertSee('?page=2', false);
    }

    public function test_media_index_displays_newest_uploads_first(): void
    {
        $adminUser = AdminUser::factory()->create();

        $olderMedia = Media::factory()->create([
            'original_name' => 'older-upload.jpg',
            'filename' => 'older-upload.jpg',
        ]);

        $newerMedia = Media::factory()->create([
            'original_name' => 'newer-upload.jpg',
            'filename' => 'newer-upload.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.media.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '"id":'.$newerMedia->getKey(),
            '"id":'.$olderMedia->getKey(),
        ], false);
    }

    public function test_media_index_config_contains_endpoint_urls_and_pagination_markup(): void
    {
        $adminUser = AdminUser::factory()->create();
        Media::factory()->count(13)->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.media.index'));

        $response->assertOk();
        $response->assertSee(route('admin.newstech.media.store'), false);
        $response->assertSee('?page=2', false);
        $response->assertSee('"paginationHtml"', false);
        $response->assertSee('"items"', false);
    }

    public function test_media_delete_endpoint_returns_json_for_vue_media_library(): void
    {
        Storage::fake('public');
        $adminUser = AdminUser::factory()->create();
        Storage::disk('public')->put('newstech/media/delete-json.jpg', 'image');

        $media = Media::factory()->create([
            'path' => 'newstech/media/delete-json.jpg',
            'filename' => 'delete-json.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->deleteJson(route('admin.newstech.media.destroy', $media));

        $response->assertOk()
            ->assertJsonPath('message', 'Media deleted successfully.')
            ->assertJsonPath('media.path', 'newstech/media/delete-json.jpg');

        $this->assertSoftDeleted('media', ['id' => $media->getKey()]);
        Storage::disk('public')->assertMissing('newstech/media/delete-json.jpg');
    }

    public function test_media_edit_updates_alt_text_and_caption(): void
    {
        $adminUser = AdminUser::factory()->create();
        $media = Media::factory()->create([
            'alt_text' => null,
            'caption' => null,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.media.update', $media), [
            'alt_text' => 'Updated alt copy',
            'caption' => 'Updated caption copy',
        ]);

        $response->assertRedirect(route('admin.newstech.media.index'));

        $this->assertDatabaseHas('media', [
            'id' => $media->getKey(),
            'alt_text' => 'Updated alt copy',
            'caption' => 'Updated caption copy',
        ]);
    }

    public function test_media_delete_soft_deletes_record_and_removes_file_when_unused(): void
    {
        Storage::fake('public');
        $adminUser = AdminUser::factory()->create();
        Storage::disk('public')->put('newstech/media/delete-me.jpg', 'image');

        $media = Media::factory()->create([
            'path' => 'newstech/media/delete-me.jpg',
            'filename' => 'delete-me.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.media.destroy', $media));

        $response->assertRedirect(route('admin.newstech.media.index'));
        $this->assertSoftDeleted('media', ['id' => $media->getKey()]);
        Storage::disk('public')->assertMissing('newstech/media/delete-me.jpg');
    }
}
