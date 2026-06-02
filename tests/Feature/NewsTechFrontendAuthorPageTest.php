<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use NewsTech\Author\Models\Author;
use Tests\TestCase;

class NewsTechFrontendAuthorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_page_renders_selected_avatar_path_as_public_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('newstech/media/author-public-avatar.webp', 'image');

        $author = Author::factory()->create([
            'slug' => 'frontend-author',
            'avatar' => 'newstech/media/author-public-avatar.webp',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.authors.show', ['slug' => $author->slug]));

        $response->assertOk();
        $response->assertSee('/storage/newstech/media/author-public-avatar.webp', false);
    }
}
