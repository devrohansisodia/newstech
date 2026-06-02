<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminFormDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_demo_form_page(): void
    {
        $response = $this->get(route('admin.newstech.foundation.form-demo.index'));

        $response->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_demo_form_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.form-demo.index'));

        $response->assertOk();
        $response->assertSee('Admin form foundation is ready for future editing modules.');
        $response->assertSee('Form Demo');
    }

    public function test_demo_form_page_renders_expected_field_labels(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.form-demo.index'));

        $response->assertOk();
        $response->assertSee('Title');
        $response->assertSee('Slug');
        $response->assertSee('Excerpt');
        $response->assertSee('Section');
        $response->assertSee('Featured placement');
        $response->assertSee('Featured image');
    }

    public function test_demo_form_page_renders_expected_action_buttons(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.form-demo.index'));

        $response->assertOk();
        $response->assertSee('Back to Dashboard');
        $response->assertSee('Preview Form State');
    }
}
