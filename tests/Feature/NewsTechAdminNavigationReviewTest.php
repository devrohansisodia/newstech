<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminNavigationReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function loginAdmin(AdminUser $adminUser): void
    {
        $response = $this->post(route('admin.newstech.login.store'), [
            'email' => $adminUser->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.newstech.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_authenticated_admin_can_open_main_admin_navigation_targets(): void
    {
        $adminUser = AdminUser::factory()->create();

        $routes = [
            'admin.newstech.dashboard',
            'admin.newstech.profile.edit',
            'admin.newstech.settings.index',
            'admin.newstech.articles.index',
            'admin.newstech.comments.index',
            'admin.newstech.categories.index',
            'admin.newstech.tags.index',
            'admin.newstech.authors.index',
            'admin.newstech.pages.index',
            'admin.newstech.newsletter.index',
            'admin.newstech.advertisements.index',
            'admin.newstech.menus.index',
            'admin.newstech.foundation.datagrid-demo.index',
            'admin.newstech.foundation.form-demo.index',
            'admin.newstech.foundation.media-demo.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($adminUser, 'admin')
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_authenticated_admin_can_open_direct_admin_paths_without_redirecting_to_dashboard(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $this->loginAdmin($adminUser);

        $paths = [
            '/admin/categories',
            '/admin/tags',
            '/admin/authors',
            '/admin/articles',
            '/admin/comments',
            '/admin/pages',
            '/admin/newsletter/subscribers',
            '/admin/advertisements',
            '/admin/menus',
            '/admin/settings',
            '/admin/profile',
            '/admin/foundation/datagrid',
            '/admin/foundation/form',
            '/admin/foundation/media',
        ];

        foreach ($paths as $path) {
            $response = $this->get($path);

            $this->assertNotSame(302, $response->getStatusCode());
            $response->assertOk();
            $this->assertNotSame(route('admin.newstech.dashboard'), $response->headers->get('Location'));
        }
    }

    public function test_admin_dashboard_sidebar_contains_main_navigation_links(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee(route('admin.newstech.dashboard'), false);
        $response->assertSee(route('admin.newstech.settings.index'), false);
        $response->assertSee(route('admin.newstech.articles.index'), false);
        $response->assertSee(route('admin.newstech.comments.index'), false);
        $response->assertSee(route('admin.newstech.categories.index'), false);
        $response->assertSee(route('admin.newstech.tags.index'), false);
        $response->assertSee(route('admin.newstech.authors.index'), false);
        $response->assertSee(route('admin.newstech.pages.index'), false);
        $response->assertSee(route('admin.newstech.newsletter.index'), false);
        $response->assertSee(route('admin.newstech.advertisements.index'), false);
        $response->assertSee(route('admin.newstech.menus.index'), false);
        $response->assertSee('data-brand-logo', false);
        $response->assertDontSee('Open Editorial');
        $response->assertDontSee('Open Taxonomy');
        $response->assertDontSee('Open Site');
        $response->assertDontSee('Open Foundation');
        $response->assertDontSee('Foundation');
        $response->assertDontSee('Media Library');
        $response->assertSee('Advertisements');
        $response->assertDontSee('Modular publishing foundation for the modern newsroom.');
    }

    public function test_key_admin_pages_hide_old_implementation_copy(): void
    {
        $adminUser = AdminUser::factory()->create();

        $pages = [
            route('admin.newstech.dashboard'),
            route('admin.newstech.articles.index'),
            route('admin.newstech.settings.index'),
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($adminUser, 'admin')->get($url);

            $response->assertOk();
            $response->assertDontSee('Editorial Module');
            $response->assertDontSee('control center');
            $response->assertDontSee('platform foundation');
            $response->assertDontSee('Phase 1.');
            $response->assertDontSee('Modular publishing foundation for the modern newsroom.');
            $response->assertDontSee('Admin Workspace');
        }
    }

    public function test_guest_is_redirected_to_login_for_protected_direct_admin_paths(): void
    {
        foreach ([
            '/admin/categories',
            '/admin/tags',
            '/admin/authors',
            '/admin/articles',
            '/admin/comments',
            '/admin/pages',
            '/admin/newsletter/subscribers',
            '/admin/advertisements',
            '/admin/menus',
            '/admin/settings',
            '/admin/profile',
        ] as $path) {
            $this->get($path)->assertRedirect(route('admin.newstech.login'));
        }
    }
}
