<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NewsTech\Core\Support\MediaManager;
use Tests\TestCase;

class NewsTechCoreMediaManagerTest extends TestCase
{
    public function test_media_support_class_can_generate_url_and_store_path(): void
    {
        Storage::fake('public');

        $mediaManager = app(MediaManager::class);
        $storedPath = $mediaManager->store(
            UploadedFile::fake()->create('hero.png', 200, 'image/png')
        );

        $this->assertStringStartsWith(config('newstech.media.path').'/', $storedPath);
        $this->assertSame(Storage::disk('public')->url($storedPath), $mediaManager->url($storedPath));
    }

    public function test_media_support_class_can_delete_uploaded_file(): void
    {
        Storage::fake('public');

        $mediaManager = app(MediaManager::class);
        $storedPath = $mediaManager->store(
            UploadedFile::fake()->create('thumb.webp', 200, 'image/webp')
        );

        Storage::disk('public')->assertExists($storedPath);

        $this->assertTrue($mediaManager->delete($storedPath));
        Storage::disk('public')->assertMissing($storedPath);
    }
}
