<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechPackageRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_frontend_package_homepage_loads(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertStatus(200);
        $response->assertSee('No published stories yet');
        $response->assertSee('Breaking news strip');
    }

    public function test_the_admin_package_dashboard_loads(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Editorial');
        $response->assertSee('Top viewed published articles');
    }
}
