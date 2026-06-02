<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Admin\Notifications\AdminResetPasswordNotification;
use NewsTech\Reader\Models\Reader;
use Tests\TestCase;

class NewsTechAdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_admin_forgot_password_page(): void
    {
        $this->get(route('admin.newstech.password.request'))
            ->assertOk()
            ->assertSee('data-brand-logo', false)
            ->assertDontSee('data-brand-copy', false)
            ->assertSee('nt-admin-auth-input', false)
            ->assertDontSee('Forgot password?')
            ->assertSee('action="'.route('admin.newstech.password.email').'"', false);
    }

    public function test_admin_reset_password_page_uses_same_auth_input_class(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
        ]);

        $token = Password::broker('admins')->createToken($adminUser);

        $this->get(route('admin.newstech.password.reset', [
            'token' => $token,
            'email' => $adminUser->email,
        ]))
            ->assertOk()
            ->assertSee('nt-admin-auth-input', false)
            ->assertSee('data-brand-logo', false)
            ->assertDontSee('data-brand-copy', false)
            ->assertDontSee('Reset password');
    }

    public function test_admin_can_request_password_reset_link(): void
    {
        Notification::fake();

        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
        ]);

        $this->post(route('admin.newstech.password.email'), [
            'email' => $adminUser->email,
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $adminUser->email,
        ]);

        Notification::assertSentTo($adminUser, AdminResetPasswordNotification::class);
    }

    public function test_password_reset_token_is_created_for_admin_provider(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
        ]);

        $token = Password::broker('admins')->createToken($adminUser);

        $this->assertIsString($token);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $adminUser->email,
        ]);
    }

    public function test_admin_can_reset_password_with_valid_token(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
            'password' => 'password123',
        ]);

        $token = Password::broker('admins')->createToken($adminUser);

        $this->post(route('admin.newstech.password.store'), [
            'token' => $token,
            'email' => $adminUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('admin.newstech.login'));

        $this->post(route('admin.newstech.login.store'), [
            'email' => $adminUser->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->post(route('admin.newstech.login.store'), [
            'email' => $adminUser->email,
            'password' => 'newpassword123',
        ])->assertRedirect(route('admin.newstech.dashboard'));
    }

    public function test_invalid_admin_reset_token_fails_safely(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'editor@newstech.test',
        ]);

        $this->from(route('admin.newstech.password.reset', [
            'token' => 'invalid-token',
            'email' => $adminUser->email,
        ]))->post(route('admin.newstech.password.store'), [
            'token' => 'invalid-token',
            'email' => $adminUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_reader_password_reset_provider_remains_separate_from_admins(): void
    {
        Notification::fake();

        $reader = Reader::factory()->create([
            'email' => 'reader@newstech.test',
        ]);

        $this->post(route('admin.newstech.password.email'), [
            'email' => $reader->email,
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $reader->email,
        ]);
    }
}
