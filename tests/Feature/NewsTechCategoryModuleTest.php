<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Category\Models\Category;
use Tests\TestCase;

class NewsTechCategoryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_category_admin_routes(): void
    {
        $this->get(route('admin.newstech.categories.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.categories.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_category_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $category = Category::factory()->create([
            'name' => 'Politics',
            'slug' => 'politics',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.categories.index'));

        $response->assertOk();
        $response->assertSee('Categories');
        $response->assertSee($category->name);
        $response->assertSee('Add Category');
        $response->assertSee(route('admin.newstech.categories.create'), false);
        $response->assertSee(route('admin.newstech.categories.edit', $category), false);
        $response->assertSee(route('admin.newstech.categories.destroy', $category), false);
    }

    public function test_logged_in_admin_can_create_category(): void
    {
        $adminUser = AdminUser::factory()->create();
        $parentCategory = Category::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.categories.store'), [
            'parent_id' => $parentCategory->getKey(),
            'name' => 'State Elections',
            'slug' => 'State Elections',
            'description' => 'Coverage of assembly and regional election developments.',
            'meta_title' => 'State Elections News',
            'meta_description' => 'Regional election updates and analysis.',
            'status' => '1',
            'sort_order' => 3,
        ]);

        $response->assertRedirect(route('admin.newstech.categories.index'));
        $this->assertDatabaseHas('categories', [
            'parent_id' => $parentCategory->getKey(),
            'name' => 'State Elections',
            'slug' => 'state-elections',
            'status' => 1,
            'sort_order' => 3,
        ]);
    }

    public function test_validation_fails_for_missing_name_and_slug(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.categories.create'))
            ->post(route('admin.newstech.categories.store'), [
                'name' => '',
                'slug' => '',
            ]);

        $response->assertRedirect(route('admin.newstech.categories.create'));
        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_logged_in_admin_can_update_category(): void
    {
        $adminUser = AdminUser::factory()->create();
        $parentCategory = Category::factory()->create(['name' => 'Politics']);
        $category = Category::factory()->create([
            'name' => 'Election Watch',
            'slug' => 'election-watch',
            'status' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.categories.update', $category), [
            'parent_id' => $parentCategory->getKey(),
            'name' => 'National Election Watch',
            'slug' => 'National Election Watch',
            'description' => 'Updated category description.',
            'meta_title' => 'Election Watch News',
            'meta_description' => 'Updated SEO description.',
            'sort_order' => 6,
        ]);

        $response->assertRedirect(route('admin.newstech.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->getKey(),
            'parent_id' => $parentCategory->getKey(),
            'name' => 'National Election Watch',
            'slug' => 'national-election-watch',
            'status' => 0,
            'sort_order' => 6,
        ]);
    }

    public function test_logged_in_admin_can_access_category_edit_page(): void
    {
        $adminUser = AdminUser::factory()->create();
        $category = Category::factory()->create([
            'name' => 'Local News',
            'slug' => 'local-news',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.categories.edit', $category));

        $response->assertOk();
        $response->assertSee('Edit Category');
        $response->assertSee('Update Category');
    }

    public function test_logged_in_admin_can_delete_category(): void
    {
        $adminUser = AdminUser::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.categories.destroy', $category));

        $response->assertRedirect(route('admin.newstech.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->getKey(),
        ]);
    }

    public function test_category_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Categories');
        $response->assertSee(route('admin.newstech.categories.index'));
    }
}
