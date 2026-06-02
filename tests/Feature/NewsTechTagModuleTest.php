<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechTagModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_tag_admin_routes(): void
    {
        $this->get(route('admin.newstech.tags.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.tags.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_tag_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'Breaking News',
            'slug' => 'breaking-news',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.tags.index'));

        $response->assertOk();
        $response->assertSee('Tags');
        $response->assertSee($tag->name);
        $response->assertSee('Add Tag');
    }

    public function test_logged_in_admin_can_create_tag(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.tags.store'), [
            'name' => 'Election Watch',
            'slug' => 'Election Watch',
            'description' => 'Stories and analysis related to election developments.',
            'meta_title' => 'Election Watch Tag',
            'meta_description' => 'Election coverage grouped by tag.',
            'status' => '1',
        ]);

        $response->assertRedirect(route('admin.newstech.tags.index'));
        $this->assertDatabaseHas('tags', [
            'name' => 'Election Watch',
            'slug' => 'election-watch',
            'status' => 1,
        ]);
    }

    public function test_validation_fails_for_missing_name_and_slug(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.tags.create'))
            ->post(route('admin.newstech.tags.store'), [
                'name' => '',
                'slug' => '',
            ]);

        $response->assertRedirect(route('admin.newstech.tags.create'));
        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_logged_in_admin_can_update_tag(): void
    {
        $adminUser = AdminUser::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'Live Updates',
            'slug' => 'live-updates',
            'status' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.tags.update', $tag), [
            'name' => 'Live Coverage',
            'slug' => 'Live Coverage',
            'description' => 'Updated tag description.',
            'meta_title' => 'Live Coverage Tag',
            'meta_description' => 'Updated SEO description.',
        ]);

        $response->assertRedirect(route('admin.newstech.tags.index'));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->getKey(),
            'name' => 'Live Coverage',
            'slug' => 'live-coverage',
            'status' => 0,
        ]);
    }

    public function test_logged_in_admin_can_delete_tag(): void
    {
        $adminUser = AdminUser::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.tags.destroy', $tag));

        $response->assertRedirect(route('admin.newstech.tags.index'));
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->getKey(),
        ]);
    }

    public function test_tag_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Tags');
        $response->assertSee(route('admin.newstech.tags.index'));
    }
}
