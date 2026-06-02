<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Core\Models\SystemSetting;
use NewsTech\Media\Models\Media;
use Tests\TestCase;

class NewsTechAdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_settings_page(): void
    {
        $this->get(route('admin.newstech.settings.index'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_settings_index_with_group_cards(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.settings.index'));

        $response->assertOk();
        $response->assertSee('Settings');
        $response->assertSee('Current Branding');
        $response->assertSee('Homepage Layout');
        $response->assertSee('Comment Controls');
        $response->assertSee('SEO Toolkit');
        $response->assertSee('Advertisement Settings');
    }

    public function test_settings_group_page_shows_only_the_selected_group(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.settings.show', ['group' => 'branding']));

        $response->assertOk();
        $response->assertSee('Current Branding');
        $response->assertSee('Site Identity');
        $response->assertDontSee('Comment Behavior');
        $response->assertDontSee('Homepage Layout</h2>', false);
    }

    public function test_admin_sidebar_contains_settings_menu_item_for_authenticated_admin(): void
    {
        $adminUser = AdminUser::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.settings.index'))
            ->assertOk()
            ->assertSee('Settings');
    }

    public function test_branding_settings_page_uses_expected_field_names(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.settings.show', ['group' => 'branding']));

        $response->assertOk();
        $response->assertSee('name="site_name"', false);
        $response->assertSee('name="logo"', false);
        $response->assertSee('name="footer_logo"', false);
        $response->assertSee('Select Image');
        $response->assertSee('data-media-picker-root="true"', false);
        $response->assertSee('data-media-picker-config', false);
    }

    public function test_branding_settings_page_can_render_multiple_vue_media_picker_roots(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.settings.show', ['group' => 'branding']));

        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'data-media-picker-root="true"'));
        $this->assertSame(2, substr_count($response->getContent(), 'data-media-picker-config'));
    }

    public function test_admin_can_save_branding_settings_and_frontend_reflects_them(): void
    {
        Storage::fake('public');

        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'branding']), [
            'site_name' => 'NewsTech Review',
            'logo' => UploadedFile::fake()->image('site-logo.png'),
            'footer_logo' => UploadedFile::fake()->image('footer-logo.png'),
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'branding']));

        $storedLogoPath = SystemSetting::query()->where('key', 'website.identity.logo_path')->value('value');
        $storedFooterLogoPath = SystemSetting::query()->where('key', 'website.identity.footer_logo_path')->value('value');

        $homepage = $this->get(route('newstech.home'));

        $homepage->assertOk();
        $homepage->assertSee('NewsTech Review');
        $homepage->assertSee('/storage/'.$storedLogoPath, false);
        $homepage->assertSee('/storage/'.$storedFooterLogoPath, false);
    }

    public function test_admin_can_save_homepage_layout_setting(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'homepage']), [
            'homepage_layout' => 'two_column_70_30',
            'homepage_sidebar_title' => 'Inside NewsTech',
            'homepage_sidebar_content' => 'Configured sidebar content.',
            'homepage_sidebar_link_label' => 'Read more',
            'homepage_sidebar_link_url' => 'https://example.com/about',
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'homepage']));

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.homepage.layout',
            'value' => 'two_column_70_30',
        ]);

        $this->get(route('newstech.home'))
            ->assertOk()
            ->assertSee('data-homepage-layout="two_column_70_30"', false)
            ->assertSee('Inside NewsTech')
            ->assertSee('Configured sidebar content.');
    }

    public function test_settings_can_use_selected_media_logo_paths(): void
    {
        $adminUser = AdminUser::factory()->create();
        $logo = Media::factory()->create([
            'path' => 'newstech/media/header-logo.png',
        ]);
        $footerLogo = Media::factory()->create([
            'path' => 'newstech/media/footer-logo.png',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'branding']), [
            'site_name' => 'Media Driven Brand',
            'logo' => $logo->path,
            'footer_logo' => $footerLogo->path,
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'branding']));

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.identity.logo_path',
            'value' => 'newstech/media/header-logo.png',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.identity.footer_logo_path',
            'value' => 'newstech/media/footer-logo.png',
        ]);
    }

    public function test_admin_can_save_comment_settings(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'comments']), [
            'comments_enabled' => '1',
            'require_moderation' => '0',
            'guest_comments_enabled' => '1',
            'website_field_enabled' => '0',
            'min_comment_length' => '10',
            'max_comment_length' => '1500',
            'max_links_per_comment' => '1',
            'blocked_words' => "casino\ncrypto scam",
            'blocked_emails' => "bad@example.com\nspamdomain.test",
            'blocked_ips' => '203.0.113.10',
            'auto_reject_spam' => '1',
            'throttle_seconds' => '90',
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'comments']));
        $response->assertSessionHas('page_status', 'Comment Controls updated successfully.');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'comments.require_moderation',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'comments.website_field_enabled',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'comments.blocked_words',
            'value' => "casino\ncrypto scam",
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'comments.throttle_seconds',
            'value' => '90',
        ]);
    }

    public function test_admin_can_save_seo_toolkit_settings(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'seo']), [
            'site_title_suffix' => '| NewsTech Daily',
            'default_meta_description' => 'Default NewsTech SEO snippet copy.',
            'enable_real_time_checks' => '1',
            'score_threshold_warning' => '78',
            'enable_social_preview' => '0',
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'seo']));

        $this->assertDatabaseHas('system_settings', [
            'key' => 'seo.site_title_suffix',
            'value' => '| NewsTech Daily',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'seo.default_meta_description',
            'value' => 'Default NewsTech SEO snippet copy.',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'seo.score_threshold_warning',
            'value' => '78',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'seo.enable_social_preview',
            'value' => '0',
        ]);
    }
}
