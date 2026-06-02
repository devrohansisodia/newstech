<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechAdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get(route('admin.newstech.profile.edit'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_authenticated_admin_can_open_profile_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.profile.edit'));

        $response->assertOk();
        $response->assertSee('Profile');
        $response->assertSee('Save Profile');
        $response->assertSee('name="current_password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $adminUser = AdminUser::factory()->create([
            'name' => 'Old Name',
            'email' => 'old-admin@newstech.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated-admin@newstech.test',
        ]);

        $response->assertRedirect(route('admin.newstech.profile.edit'));
        $response->assertSessionHas('profile_status');
        $this->assertDatabaseHas('admin_users', [
            'id' => $adminUser->getKey(),
            'name' => 'Updated Name',
            'email' => 'updated-admin@newstech.test',
        ]);
    }

    public function test_admin_can_update_password_with_current_password(): void
    {
        $adminUser = AdminUser::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.profile.update'), [
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('admin.newstech.profile.edit'));
        $this->assertTrue(Hash::check('new-password-123', $adminUser->fresh()->password));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'primary-admin@newstech.test',
        ]);
        $otherAdminUser = AdminUser::factory()->create([
            'email' => 'secondary-admin@newstech.test',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.profile.edit'))
            ->put(route('admin.newstech.profile.update'), [
                'name' => $adminUser->name,
                'email' => $otherAdminUser->email,
            ]);

        $response->assertRedirect(route('admin.newstech.profile.edit'));
        $response->assertSessionHasErrors('email');
    }

    public function test_current_password_is_required_when_updating_password(): void
    {
        $adminUser = AdminUser::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.profile.edit'))
            ->put(route('admin.newstech.profile.update'), [
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

        $response->assertRedirect(route('admin.newstech.profile.edit'));
        $response->assertSessionHasErrors('current_password');
    }
}
