<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Author\Models\Author;
use NewsTech\Media\Models\Media;
use Tests\TestCase;

class NewsTechAuthorModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_author_admin_routes(): void
    {
        $this->get(route('admin.newstech.authors.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.authors.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_author_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $author = Author::factory()->create([
            'name' => 'Aarav Mehta',
            'slug' => 'aarav-mehta',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.authors.index'));

        $response->assertOk();
        $response->assertSee('Authors');
        $response->assertSee('Authors are public content bylines and reporters, not admin login users.');
        $response->assertSee($author->name);
        $response->assertSee('Add Author');
    }

    public function test_author_create_and_edit_pages_explain_authors_are_not_admin_users(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'admin@newstech.test',
        ]);
        $author = Author::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.authors.create'))
            ->assertOk()
            ->assertSee('Authors are public content bylines and reporters, not admin login users.');

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.authors.edit', $author))
            ->assertOk()
            ->assertSee('Authors are public content bylines and reporters, not admin login users.');
    }

    public function test_author_form_uses_reusable_media_picker_component(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.authors.create'));

        $response->assertOk();
        $response->assertSee('Select Image');
        $response->assertSee('data-media-picker', false);
        $response->assertSee('data-media-picker-root="true"', false);
        $response->assertSee('data-media-picker-config', false);
        $response->assertSee('data-media-picker-hidden-input', false);
        $response->assertSee('name="avatar"', false);
        $response->assertDontSee('action="'.route('admin.newstech.media.picker.upload').'"', false);
    }

    public function test_logged_in_admin_can_create_author(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.authors.store'), [
            'name' => 'Sara Khan',
            'slug' => 'Sara Khan',
            'email' => 'sara.khan@newstech.test',
            'designation' => 'Political Editor',
            'bio' => 'Covers public policy and election analysis.',
            'avatar' => 'authors/sara-khan.jpg',
            'facebook_url' => 'https://facebook.com/sara.khan',
            'twitter_url' => 'https://x.com/sarakhan',
            'linkedin_url' => 'https://linkedin.com/in/sarakhan',
            'website_url' => 'https://sarakhan.example',
            'meta_title' => 'Sara Khan | NewsTech',
            'meta_description' => 'Reporter profile for Sara Khan.',
            'status' => '1',
        ]);

        $response->assertRedirect(route('admin.newstech.authors.index'));
        $this->assertDatabaseHas('authors', [
            'name' => 'Sara Khan',
            'slug' => 'sara-khan',
            'designation' => 'Political Editor',
            'status' => 1,
        ]);
    }

    public function test_author_can_save_selected_media_as_avatar(): void
    {
        $adminUser = AdminUser::factory()->create();
        $media = Media::factory()->create([
            'path' => 'newstech/media/author-avatar.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.authors.store'), [
            'name' => 'Media Avatar Author',
            'slug' => 'Media Avatar Author',
            'avatar' => $media->path,
        ]);

        $response->assertRedirect(route('admin.newstech.authors.index'));
        $this->assertDatabaseHas('authors', [
            'name' => 'Media Avatar Author',
            'avatar' => 'newstech/media/author-avatar.jpg',
        ]);
    }

    public function test_validation_fails_for_missing_name_and_slug(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.authors.create'))
            ->post(route('admin.newstech.authors.store'), [
                'name' => '',
                'slug' => '',
            ]);

        $response->assertRedirect(route('admin.newstech.authors.create'));
        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_logged_in_admin_can_update_author(): void
    {
        $adminUser = AdminUser::factory()->create();
        $author = Author::factory()->create([
            'name' => 'Rohan Singh',
            'slug' => 'rohan-singh',
            'status' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.authors.update', $author), [
            'name' => 'Rohan Singh Sharma',
            'slug' => 'Rohan Singh Sharma',
            'email' => 'rohan.sharma@newstech.test',
            'designation' => 'Senior Reporter',
            'bio' => 'Updated profile biography.',
            'avatar' => 'authors/rohan-sharma.jpg',
            'facebook_url' => 'https://facebook.com/rohan.sharma',
            'twitter_url' => 'https://x.com/rohansharma',
            'linkedin_url' => 'https://linkedin.com/in/rohansharma',
            'website_url' => 'https://rohansharma.example',
            'meta_title' => 'Rohan Sharma | NewsTech',
            'meta_description' => 'Updated author profile description.',
        ]);

        $response->assertRedirect(route('admin.newstech.authors.index'));
        $this->assertDatabaseHas('authors', [
            'id' => $author->getKey(),
            'name' => 'Rohan Singh Sharma',
            'slug' => 'rohan-singh-sharma',
            'designation' => 'Senior Reporter',
            'status' => 0,
        ]);
    }

    public function test_logged_in_admin_can_delete_author(): void
    {
        $adminUser = AdminUser::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.authors.destroy', $author));

        $response->assertRedirect(route('admin.newstech.authors.index'));
        $this->assertDatabaseMissing('authors', [
            'id' => $author->getKey(),
        ]);
    }

    public function test_author_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Authors');
        $response->assertSee(route('admin.newstech.authors.index'));
    }
}
