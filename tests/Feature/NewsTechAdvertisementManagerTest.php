<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Article\Models\Article;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Models\SystemSetting;
use Tests\TestCase;

class NewsTechAdvertisementManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_advertisement_admin_routes_menu_and_acl_are_registered(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Advertisements');
        $this->assertTrue(app('router')->has('admin.newstech.advertisements.index'));
        $this->assertNotNull(collect(config('acl'))->firstWhere('key', 'site.advertisements'));
    }

    public function test_advertisement_menu_is_hidden_when_advertisements_are_disabled_but_settings_remain_available(): void
    {
        config()->set('newstech-advertisement.enabled', false);

        $adminUser = AdminUser::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.dashboard'))
            ->assertOk()
            ->assertDontSee('Advertisements');

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.settings.index'))
            ->assertOk()
            ->assertSee('Advertisement Settings');
    }

    public function test_admin_can_create_image_advertisement(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.advertisements.store'), [
            'name' => 'Homepage Hero Campaign',
            'slug' => 'homepage hero campaign',
            'type' => Advertisement::TYPE_IMAGE,
            'status' => Advertisement::STATUS_ACTIVE,
            'slot_key' => 'homepage_top',
            'title' => 'Campaign Hero',
            'image_path' => 'newstech/media/homepage-hero.jpg',
            'target_url' => 'https://example.com/campaign',
            'open_in_new_tab' => '1',
            'nofollow' => '0',
            'sponsored' => '1',
            'priority' => '25',
        ]);

        $advertisement = Advertisement::query()->first();

        $response->assertRedirect(route('admin.newstech.advertisements.edit', $advertisement));

        $this->assertDatabaseHas('advertisements', [
            'name' => 'Homepage Hero Campaign',
            'slug' => 'homepage-hero-campaign',
            'type' => Advertisement::TYPE_IMAGE,
            'slot_key' => 'homepage_top',
            'image_path' => 'newstech/media/homepage-hero.jpg',
            'created_by' => $adminUser->getKey(),
        ]);
    }

    public function test_admin_can_create_html_advertisement(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.advertisements.store'), [
            'name' => 'Inline Html Campaign',
            'type' => Advertisement::TYPE_HTML,
            'status' => Advertisement::STATUS_ACTIVE,
            'slot_key' => 'article_inline',
            'html_content' => '<div>Trusted campaign markup</div>',
            'open_in_new_tab' => '0',
            'nofollow' => '1',
            'sponsored' => '1',
            'priority' => '5',
        ]);

        $advertisement = Advertisement::query()->first();

        $response->assertRedirect(route('admin.newstech.advertisements.edit', $advertisement));
        $this->assertDatabaseHas('advertisements', [
            'name' => 'Inline Html Campaign',
            'type' => Advertisement::TYPE_HTML,
            'slot_key' => 'article_inline',
        ]);
    }

    public function test_admin_can_edit_an_advertisement(): void
    {
        $adminUser = AdminUser::factory()->create();
        $advertisement = Advertisement::factory()->create([
            'name' => 'Original Campaign',
            'slot_key' => 'homepage_top',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.advertisements.update', $advertisement), [
            'name' => 'Updated Campaign',
            'slug' => 'updated-campaign',
            'type' => Advertisement::TYPE_IMAGE,
            'status' => Advertisement::STATUS_INACTIVE,
            'slot_key' => 'footer_banner',
            'title' => 'Updated Title',
            'image_path' => 'newstech/media/updated-banner.jpg',
            'target_url' => 'https://example.com/updated',
            'open_in_new_tab' => '0',
            'nofollow' => '1',
            'sponsored' => '0',
            'priority' => '99',
        ]);

        $response->assertRedirect(route('admin.newstech.advertisements.edit', $advertisement));
        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'name' => 'Updated Campaign',
            'status' => Advertisement::STATUS_INACTIVE,
            'slot_key' => 'footer_banner',
            'updated_by' => $adminUser->getKey(),
        ]);
    }

    public function test_inactive_future_and_expired_ads_do_not_render(): void
    {
        Advertisement::factory()->inactive()->create([
            'slot_key' => 'homepage_top',
            'title' => 'Inactive Campaign',
        ]);

        Advertisement::factory()->scheduledFuture()->create([
            'slot_key' => 'homepage_top',
            'title' => 'Future Campaign',
        ]);

        Advertisement::factory()->expired()->create([
            'slot_key' => 'homepage_top',
            'title' => 'Expired Campaign',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertDontSee('Inactive Campaign');
        $response->assertDontSee('Future Campaign');
        $response->assertDontSee('Expired Campaign');
        $response->assertDontSee('Advertisement Placeholder');
    }

    public function test_active_managed_advertisement_renders_and_replaces_placeholder(): void
    {
        Advertisement::factory()->create([
            'slot_key' => 'homepage_top',
            'title' => 'Managed Campaign',
            'image_path' => 'newstech/media/homepage-campaign.jpg',
            'target_url' => 'https://example.com/managed',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Managed Campaign');
        $response->assertSee('data-managed-advertisement="homepage_top"', false);
        $response->assertSee('/storage/newstech/media/homepage-campaign.jpg', false);
        $response->assertDontSee('data-ad-slot="homepage_top"', false);
    }

    public function test_placeholder_fallback_can_be_hidden_when_disabled(): void
    {
        config()->set('newstech-advertisement.placeholders_enabled', false);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertDontSee('Advertisement Placeholder');
        $response->assertDontSee('Header Leaderboard');
        $response->assertDontSee('Homepage Top');
    }

    public function test_managed_advertisement_renders_only_in_its_configured_slot(): void
    {
        Advertisement::factory()->create([
            'slot_key' => 'homepage_top',
            'title' => 'City Bank Homepage Banner',
            'image_path' => 'newstech/media/city-bank-banner.jpg',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-managed-advertisement="homepage_top"', false);
        $response->assertDontSee('data-managed-advertisement="header_leaderboard"', false);
        $response->assertDontSee('data-managed-advertisement="homepage_sidebar"', false);
        $response->assertDontSee('data-ad-slot="header_leaderboard"', false);
        $response->assertDontSee('data-ad-slot="homepage_sidebar"', false);
    }

    public function test_click_tracking_route_increments_clicks_and_redirects(): void
    {
        $advertisement = Advertisement::factory()->create([
            'target_url' => 'https://example.com/click-destination',
            'clicks_count' => 0,
        ]);

        $response = $this->get(route('newstech.advertisements.click', $advertisement));

        $response->assertRedirect('https://example.com/click-destination');
        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'clicks_count' => 1,
        ]);
    }

    public function test_impressions_increment_when_rendered_and_tracking_can_be_disabled(): void
    {
        $advertisement = Advertisement::factory()->create([
            'slot_key' => 'homepage_top',
            'impressions_count' => 0,
        ]);

        $this->get(route('newstech.home'))->assertOk();

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'impressions_count' => 1,
        ]);

        config()->set('newstech-advertisement.track_impressions', false);

        $advertisement = Advertisement::factory()->create([
            'slot_key' => 'footer_banner',
            'impressions_count' => 0,
        ]);

        $this->get(route('newstech.home'))->assertOk();

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'impressions_count' => 0,
        ]);
    }

    public function test_click_tracking_can_be_disabled(): void
    {
        config()->set('newstech-advertisement.track_clicks', false);

        $advertisement = Advertisement::factory()->create([
            'target_url' => 'https://example.com/no-track',
            'clicks_count' => 0,
        ]);

        $response = $this->get(route('newstech.advertisements.click', $advertisement));

        $response->assertRedirect('https://example.com/no-track');
        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'clicks_count' => 0,
        ]);
    }

    public function test_disabling_advertisements_hides_all_frontend_ads_and_blocks_click_redirects(): void
    {
        config()->set('newstech-advertisement.enabled', false);
        config()->set('newstech-advertisement.placeholders_enabled', true);

        $advertisement = Advertisement::factory()->create([
            'slot_key' => 'homepage_top',
            'title' => 'Disabled Campaign',
            'target_url' => 'https://example.com/disabled-campaign',
            'clicks_count' => 0,
        ]);

        $this->get(route('newstech.home'))
            ->assertOk()
            ->assertDontSee('Disabled Campaign')
            ->assertDontSee('Advertisement Placeholder')
            ->assertDontSee('data-managed-advertisement', false)
            ->assertDontSee('data-ad-slot', false);

        $this->get(route('newstech.advertisements.click', $advertisement))
            ->assertNotFound();

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->getKey(),
            'clicks_count' => 0,
        ]);
    }

    public function test_advertisement_settings_group_can_save_values(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.settings.update', ['group' => 'advertisement']), [
            'advertisements_enabled' => '1',
            'placeholders_enabled' => '0',
            'track_impressions' => '0',
            'track_clicks' => '1',
            'default_open_in_new_tab' => '1',
            'default_nofollow' => '1',
            'default_sponsored' => '0',
        ]);

        $response->assertRedirect(route('admin.newstech.settings.show', ['group' => 'advertisement']));
        $this->assertDatabaseHas('system_settings', [
            'key' => 'advertisements.placeholders_enabled',
            'value' => '0',
        ]);
        $this->assertSame('1', SystemSetting::query()->where('key', 'advertisements.default_nofollow')->value('value'));
    }

    public function test_advertisement_placeholder_default_is_disabled_for_public_install(): void
    {
        $this->assertFalse(config('newstech-advertisement.placeholders_enabled'));
    }

    public function test_configured_slots_appear_in_admin_advertisement_form(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.advertisements.create'));

        $response->assertOk();
        $response->assertSee('Homepage Top');
        $response->assertSee('Homepage Sidebar');
        $response->assertSee('Footer Banner');
    }

    public function test_homepage_article_and_listing_pages_render_without_direct_advertisement_component_tags(): void
    {
        $article = Article::factory()->published()->create();
        $category = Category::factory()->create([
            'status' => true,
        ]);

        Article::factory()->published()->create([
            'category_id' => $category->getKey(),
        ]);

        $this->get(route('newstech.home'))
            ->assertOk()
            ->assertDontSee('<x-newstech-advertisement::slot', false);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertDontSee('<x-newstech-advertisement::slot', false);

        $this->get(route('newstech.categories.show', ['slug' => $category->slug]))
            ->assertOk()
            ->assertDontSee('<x-newstech-advertisement::slot', false);
    }
}
