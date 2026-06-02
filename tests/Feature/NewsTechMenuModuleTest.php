<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Category\Models\Category;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;
use NewsTech\Page\Models\Page;
use Tests\TestCase;

class NewsTechMenuModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_menu_admin_routes(): void
    {
        $this->get(route('admin.newstech.menus.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.menus.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_menu_group_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $menuGroup = MenuGroup::factory()->create([
            'name' => 'Primary Header Menu',
            'location' => 'header',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.menus.index'));

        $response->assertOk();
        $response->assertSee('Menus');
        $response->assertSee($menuGroup->name);
        $response->assertSee('Add Menu Group');
    }

    public function test_logged_in_admin_can_create_menu_group(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.menus.store'), [
                'name' => 'Footer Links',
                'location' => 'footer',
                'status' => '1',
            ]);

        $menuGroup = MenuGroup::query()->first();

        $this->assertNotNull($menuGroup);
        $response->assertRedirect(route('admin.newstech.menus.edit', $menuGroup));
        $this->assertDatabaseHas('menu_groups', [
            'name' => 'Footer Links',
            'location' => 'footer',
            'status' => 1,
        ]);
    }

    public function test_logged_in_admin_can_create_menu_item(): void
    {
        $adminUser = AdminUser::factory()->create();
        $menuGroup = MenuGroup::factory()->create();
        $page = Page::factory()->create([
            'title' => 'Advertise',
            'slug' => 'advertise',
            'status' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.menus.items.store', $menuGroup), [
                'label' => 'Advertise With Us',
                'type' => 'page',
                'page_id' => $page->getKey(),
                'sort_order' => 2,
                'status' => '1',
                'opens_in_new_tab' => '1',
            ]);

        $response->assertRedirect(route('admin.newstech.menus.edit', $menuGroup));
        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $menuGroup->getKey(),
            'label' => 'Advertise With Us',
            'type' => 'page',
            'page_id' => $page->getKey(),
            'sort_order' => 2,
            'status' => 1,
            'opens_in_new_tab' => 1,
        ]);
    }

    public function test_validation_fails_for_missing_required_menu_fields(): void
    {
        $adminUser = AdminUser::factory()->create();
        $menuGroup = MenuGroup::factory()->create();

        $groupResponse = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.menus.create'))
            ->post(route('admin.newstech.menus.store'), [
                'name' => '',
                'location' => '',
            ]);

        $groupResponse->assertRedirect(route('admin.newstech.menus.create'));
        $groupResponse->assertSessionHasErrors(['name', 'location']);

        $itemResponse = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.menus.items.create', $menuGroup))
            ->post(route('admin.newstech.menus.items.store', $menuGroup), [
                'label' => '',
                'type' => '',
            ]);

        $itemResponse->assertRedirect(route('admin.newstech.menus.items.create', $menuGroup));
        $itemResponse->assertSessionHasErrors(['label', 'type']);
    }

    public function test_frontend_header_uses_database_header_menu_when_available(): void
    {
        $aboutPage = Page::factory()->create([
            'title' => 'About NewsTech',
            'slug' => 'about',
            'status' => true,
        ]);

        $headerGroup = MenuGroup::factory()->create([
            'location' => 'header',
            'status' => true,
        ]);

        MenuItem::factory()->create([
            'menu_group_id' => $headerGroup->getKey(),
            'type' => 'page',
            'label' => 'Our Story',
            'page_id' => $aboutPage->getKey(),
            'url' => null,
        ]);

        MenuItem::factory()->create([
            'menu_group_id' => $headerGroup->getKey(),
            'type' => 'custom_url',
            'label' => 'Advertise',
            'url' => '/page/advertise',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Our Story');
        $response->assertSee(route('newstech.about'));
        $response->assertSee('Advertise');
        $response->assertSee('/page/advertise');
    }

    public function test_frontend_footer_uses_database_footer_menu_when_available(): void
    {
        $category = Category::factory()->create([
            'name' => 'Policy',
            'slug' => 'policy',
            'status' => true,
        ]);

        $footerGroup = MenuGroup::factory()->create([
            'location' => 'footer',
            'status' => true,
        ]);

        MenuItem::factory()->create([
            'menu_group_id' => $footerGroup->getKey(),
            'type' => 'category',
            'label' => 'Policy Desk',
            'category_id' => $category->getKey(),
            'url' => null,
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Policy Desk');
        $response->assertSee(route('newstech.categories.show', ['slug' => $category->slug]));
    }

    public function test_fallback_navigation_still_renders_when_no_menu_records_exist(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Home');
        $response->assertSee('About');
        $response->assertSee('Contact');
        $response->assertSee('Privacy Policy');
        $response->assertSee('Terms');
    }

    public function test_inactive_menu_items_do_not_render_publicly(): void
    {
        $headerGroup = MenuGroup::factory()->create([
            'location' => 'header',
            'status' => true,
        ]);

        MenuItem::factory()->create([
            'menu_group_id' => $headerGroup->getKey(),
            'label' => 'Hidden Link',
            'status' => false,
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertDontSee('Hidden Link');
    }
}
