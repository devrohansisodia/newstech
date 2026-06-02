<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.newstech.dashboard'));

        $response->assertRedirect(route('admin.newstech.login'));
    }

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get(route('admin.newstech.login'));

        $response->assertOk();
        $response->assertSee('data-brand-logo', false);
        $response->assertDontSee('data-brand-copy', false);
        $response->assertDontSee('Admin Sign In');
        $response->assertDontSee(config('newstech.brand.tagline'));
        $response->assertSee('Remember me');
        $response->assertSee('Forgot password?');
        $response->assertSee('nt-admin-auth-input', false);
        $response->assertSee('action="'.route('admin.newstech.login.store').'"', false);
        $response->assertDontSee('Default local seed');
        $response->assertDontSee('admin@newstech.test / password');
    }

    public function test_valid_admin_can_login(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
            'password' => 'password',
        ]);

        $response = $this->post(route('admin.newstech.login.store'), [
            'email' => $adminUser->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.newstech.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_invalid_admin_cannot_login(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
            'password' => 'password',
        ]);

        $response = $this->from(route('admin.newstech.login'))->post(route('admin.newstech.login.store'), [
            'email' => $adminUser->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('admin.newstech.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('data-admin-profile-menu-trigger', false);
        $response->assertSee('Edit Profile');
        $response->assertSee('Logout');
        $response->assertDontSee($adminUser->name.' · '.$adminUser->email);
        $response->assertDontSee($adminUser->email.'</header>', false);
        $response->assertSee('absolute right-0 top-full', false);
        $response->assertSee('Top viewed published articles');
    }

    public function test_admin_dashboard_contains_configured_menu_labels(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Editorial');
        $response->assertSee('Taxonomy');
        $response->assertSee('Site');
        $response->assertSee('Settings');
        $response->assertDontSee('Foundation');
        $response->assertDontSee('Users');
        $response->assertDontSee('control center');
        $response->assertDontSee('Admin Workspace');
        $response->assertDontSee('Modular publishing foundation for the modern newsroom.');
    }

    public function test_admin_can_logout(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.logout'));

        $response->assertRedirect(route('admin.newstech.login'));
        $this->assertGuest('admin');
    }
}
