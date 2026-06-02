<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminDataGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_demo_table_page(): void
    {
        $response = $this->get(route('admin.newstech.foundation.datagrid-demo.index'));

        $response->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_demo_table_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.datagrid-demo.index'));

        $response->assertOk();
        $response->assertSee('Admin table foundation is ready for future listing modules.');
        $response->assertSee('DataGrid Demo');
    }

    public function test_demo_table_page_renders_expected_column_labels(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.datagrid-demo.index'));

        $response->assertOk();
        $response->assertSee('Headline');
        $response->assertSee('Section');
        $response->assertSee('Status');
        $response->assertSee('Author');
        $response->assertSee('Published');
        $response->assertSee('Actions');
    }

    public function test_demo_table_page_renders_expected_sample_row_and_action_labels(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.foundation.datagrid-demo.index'));

        $response->assertOk();
        $response->assertSee('City Council Approves Late Budget After Midnight Session');
        $response->assertSee('Anika Sharma');
        $response->assertSee('Published');
        $response->assertSee('Preview');
        $response->assertSee('Duplicate');
    }
}
