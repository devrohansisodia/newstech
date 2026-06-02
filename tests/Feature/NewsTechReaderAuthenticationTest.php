<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Reader\Models\Reader;
use NewsTech\Reader\Notifications\ReaderResetPasswordNotification;
use NewsTech\Reader\Notifications\ReaderVerifyEmailNotification;
use Tests\TestCase;

class NewsTechReaderAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_register(): void
    {
        Notification::fake();

        $response = $this->post(route('newstech.readers.register.store'), [
            'name' => 'Aarav Reader',
            'email' => 'reader@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('newstech.account.dashboard'));
        $this->assertAuthenticated('reader');
        $this->assertDatabaseHas('readers', [
            'name' => 'Aarav Reader',
            'email' => 'reader@example.com',
            'is_active' => true,
        ]);
        $this->assertNull(Reader::query()->where('email', 'reader@example.com')->value('email_verified_at'));
        Notification::assertSentTo(
            Reader::query()->first(),
            ReaderVerifyEmailNotification::class
        );
    }

    public function test_duplicate_email_cannot_register_reader_account(): void
    {
        Reader::factory()->create([
            'email' => 'reader@example.com',
        ]);

        $response = $this->from(route('newstech.readers.register'))
            ->post(route('newstech.readers.register.store'), [
                'name' => 'Duplicate Reader',
                'email' => 'reader@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('newstech.readers.register'));
        $response->assertInvalid(['email']);
    }

    public function test_reader_can_login_and_last_login_is_recorded(): void
    {
        $reader = Reader::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'password123',
            'last_login_at' => null,
        ]);

        $response = $this->post(route('newstech.readers.login.store'), [
            'email' => $reader->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('newstech.account.dashboard'));
        $this->assertAuthenticated('reader');
        $this->assertNotNull($reader->fresh()?->last_login_at);
    }

    public function test_inactive_reader_cannot_login(): void
    {
        $reader = Reader::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from(route('newstech.readers.login'))
            ->post(route('newstech.readers.login.store'), [
                'email' => $reader->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('newstech.readers.login'));
        $response->assertInvalid(['email']);
        $this->assertGuest('reader');
    }

    public function test_reader_can_logout(): void
    {
        $reader = Reader::factory()->create();

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.readers.logout'));

        $response->assertRedirect(route('newstech.home'));
        $this->assertGuest('reader');
    }

    public function test_authenticated_reader_can_access_account_dashboard_and_profile_pages(): void
    {
        $reader = Reader::factory()->create();

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.dashboard'))
            ->assertOk()
            ->assertSee('Account dashboard');

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.profile'))
            ->assertOk()
            ->assertSee('Profile settings');
    }

    public function test_guest_cannot_access_account_pages(): void
    {
        $this->get(route('newstech.account.dashboard'))
            ->assertRedirect(route('newstech.readers.login'));

        $this->get(route('newstech.account.profile'))
            ->assertRedirect(route('newstech.readers.login'));
    }

    public function test_reader_can_update_profile_and_password(): void
    {
        $reader = Reader::factory()->create([
            'email' => 'before@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.account.profile.update'), [
                'name' => 'Updated Reader',
                'email' => 'updated@example.com',
                'website' => 'https://example.com',
                'bio' => 'Updated reader bio.',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('newstech.account.profile'));
        $response->assertSessionHas('account_status', 'Your reader profile has been updated.');
        $this->assertDatabaseHas('readers', [
            'id' => $reader->getKey(),
            'name' => 'Updated Reader',
            'email' => 'updated@example.com',
            'website' => 'https://example.com',
            'bio' => 'Updated reader bio.',
        ]);
    }

    public function test_admin_auth_remains_separate_from_reader_auth(): void
    {
        $adminUser = AdminUser::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->get(route('newstech.account.dashboard'))
            ->assertRedirect(route('newstech.readers.login'));

        $this->assertAuthenticated('admin');
        $this->assertGuest('reader');
    }

    public function test_reader_cannot_access_admin_panel(): void
    {
        $reader = Reader::factory()->create();

        $this->actingAs($reader, 'reader')
            ->get(route('admin.newstech.dashboard'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_reader_can_request_password_reset_link(): void
    {
        Notification::fake();

        $reader = Reader::factory()->create([
            'email' => 'reader@example.com',
        ]);

        $response = $this->post(route('newstech.readers.password.email'), [
            'email' => $reader->email,
        ]);

        $response->assertSessionHas('reader_password_status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $reader->email,
        ]);
        Notification::assertSentTo($reader, ReaderResetPasswordNotification::class);
    }

    public function test_reader_can_reset_password_with_valid_token(): void
    {
        $reader = Reader::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'password123',
        ]);

        $token = Password::broker('readers')->createToken($reader);

        $response = $this->post(route('newstech.readers.password.store'), [
            'token' => $token,
            'email' => $reader->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('newstech.readers.login'));

        $this->post(route('newstech.readers.login.store'), [
            'email' => $reader->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->post(route('newstech.readers.login.store'), [
            'email' => $reader->email,
            'password' => 'newpassword123',
        ])->assertRedirect(route('newstech.account.dashboard'));
    }

    public function test_invalid_password_reset_token_fails(): void
    {
        $reader = Reader::factory()->create([
            'email' => 'reader@example.com',
        ]);

        $response = $this->from(route('newstech.readers.password.reset', ['token' => 'invalid-token', 'email' => $reader->email]))
            ->post(route('newstech.readers.password.store'), [
                'token' => 'invalid-token',
                'email' => $reader->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_reader_can_verify_email_with_signed_url(): void
    {
        $reader = Reader::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'newstech.readers.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $reader->getKey(),
                'hash' => sha1($reader->email),
            ]
        );

        $response = $this->actingAs($reader, 'reader')->get($verificationUrl);

        $response->assertRedirect(route('newstech.account.dashboard'));
        $this->assertNotNull($reader->fresh()?->email_verified_at);
    }

    public function test_reader_can_resend_email_verification_notification(): void
    {
        Notification::fake();

        $reader = Reader::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.readers.verification.send'));

        $response->assertSessionHas('verification_status', 'Verification link sent!');
        Notification::assertSentTo($reader, ReaderVerifyEmailNotification::class);
    }

    public function test_invalid_email_verification_does_not_verify_another_reader(): void
    {
        $reader = Reader::factory()->create(['email_verified_at' => null]);
        $otherReader = Reader::factory()->create(['email_verified_at' => null]);

        $verificationUrl = URL::temporarySignedRoute(
            'newstech.readers.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $reader->getKey(),
                'hash' => sha1($reader->email),
            ]
        );

        $this->actingAs($otherReader, 'reader')->get($verificationUrl)->assertForbidden();
        $this->assertNull($reader->fresh()?->email_verified_at);
    }

    public function test_verified_reader_no_longer_sees_verification_notice(): void
    {
        $reader = Reader::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.dashboard'))
            ->assertOk()
            ->assertDontSee('Your email address is not verified yet.');
    }
}
