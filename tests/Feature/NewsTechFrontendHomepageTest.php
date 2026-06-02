<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Models\SystemSetting;
use NewsTech\Reader\Models\Reader;
use Tests\TestCase;

class NewsTechFrontendHomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('No published stories yet');
        $response->assertSee('Latest news');
    }

    public function test_homepage_shows_published_articles(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Published Front Page Story',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_homepage_renders_selected_article_media_path_as_public_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('newstech/media/homepage-card.webp', 'image');

        Article::factory()->published()->create([
            'title' => 'Homepage Media Path Story',
            'featured_image' => 'newstech/media/homepage-card.webp',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('/storage/newstech/media/homepage-card.webp', false);
    }

    public function test_homepage_keeps_full_url_featured_images_supported(): void
    {
        Article::factory()->published()->create([
            'title' => 'Homepage Full Url Story',
            'featured_image' => 'https://cdn.example.com/media/homepage-full-url.webp',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('https://cdn.example.com/media/homepage-full-url.webp', false);
    }

    public function test_homepage_does_not_show_draft_articles(): void
    {
        Article::factory()->published()->create([
            'title' => 'Visible Published Story',
        ]);

        $draftArticle = Article::factory()->create([
            'title' => 'Draft Story Should Stay Hidden',
            'status' => 'draft',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Visible Published Story');
        $response->assertDontSee($draftArticle->title);
    }

    public function test_homepage_shows_featured_articles_section(): void
    {
        Article::factory()->featured()->create([
            'title' => 'Front Page Featured Story',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Featured articles');
        $response->assertSee('Front Page Featured Story');
    }

    public function test_homepage_shows_breaking_news_section(): void
    {
        Article::factory()->breaking()->create([
            'title' => 'Major Breaking Story',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Breaking news strip');
        $response->assertSee('Major Breaking Story');
    }

    public function test_homepage_shows_category_section_when_category_has_published_articles(): void
    {
        $category = Category::factory()->create([
            'name' => 'Politics',
            'slug' => 'politics',
        ]);

        $author = Author::factory()->create();

        Article::factory()->published()->count(2)->create([
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Politics');
        $response->assertSee('Published coverage from the Politics desk.');
    }

    public function test_frontend_header_falls_back_when_no_logo_is_set(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-brand-logo="fallback"', false);
        $response->assertSee(config('newstech.brand.name'));
        $response->assertSee(config('newstech.brand.tagline'));
        $response->assertSee('data-frontend-auth-menu-trigger', false);
        $response->assertDontSee('Published newsroom feed');
        $response->assertDontSee('Blade-first frontend');
        $response->assertSee('Login');
        $response->assertSee('Register');
    }

    public function test_homepage_latest_and_featured_sections_use_three_column_desktop_grid(): void
    {
        Article::factory()->published()->count(6)->create();

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('grid gap-5 md:grid-cols-2 xl:grid-cols-3', false);
        $response->assertDontSee('xl:grid-cols-4', false);
    }

    public function test_non_hero_article_cards_clamp_excerpt_preview_lines(): void
    {
        Article::factory()->published()->count(3)->create([
            'excerpt' => 'This is a deliberately long preview excerpt that should remain limited to a short homepage card summary instead of expanding into full article content on the listing view.',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('nt-line-clamp-4', false);
    }

    public function test_homepage_hero_and_editor_pick_cards_clamp_excerpt_preview_lines(): void
    {
        Article::factory()->published()->create([
            'title' => 'Homepage Hero Clamp Story',
            'excerpt' => 'This homepage hero excerpt should stay constrained to a short preview so readers move into the article detail page for the full story instead of reading a long block directly on the homepage card.',
            'published_at' => now()->subMinute(),
        ]);

        Article::factory()->featured()->count(2)->create([
            'excerpt' => 'This featured editor pick excerpt should stay short on the listing card and should not expand into a long paragraph inside the homepage side rail.',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Latest main story');
        $response->assertSee('Editor picks');
        $response->assertSee('nt-line-clamp-4', false);
    }

    public function test_frontend_header_renders_logo_when_logo_setting_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('newstech/settings/branding/site-logo.png', 'logo');

        SystemSetting::query()->create([
            'key' => 'website.identity.logo_path',
            'value' => 'newstech/settings/branding/site-logo.png',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-brand-logo="custom"', false);
        $response->assertSee('/storage/newstech/settings/branding/site-logo.png', false);
    }

    public function test_authenticated_reader_header_dropdown_contains_reader_account_links(): void
    {
        $reader = Reader::factory()->create([
            'name' => 'Reader Example',
            'email' => 'reader@example.com',
        ]);

        $response = $this->actingAs($reader, 'reader')->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-frontend-auth-menu-panel', false);
        $response->assertSee('Reader Example');
        $response->assertSee('reader@example.com');
        $response->assertSee('Account');
        $response->assertSee('Saved Articles');
        $response->assertSee('Reading History');
        $response->assertSee('Logout');
    }

    public function test_frontend_footer_renders_footer_logo_when_setting_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('newstech/settings/branding/footer-logo.png', 'logo');

        SystemSetting::query()->create([
            'key' => 'website.identity.footer_logo_path',
            'value' => 'newstech/settings/branding/footer-logo.png',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('/storage/newstech/settings/branding/footer-logo.png', false);
    }

    public function test_homepage_renders_full_width_layout_when_configured(): void
    {
        SystemSetting::query()->create([
            'key' => 'website.homepage.layout',
            'value' => 'full_width',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-homepage-layout="full_width"', false);
        $response->assertSee('data-homepage-main="full_width"', false);
        $response->assertDontSee('data-homepage-sidebar', false);
    }

    public function test_homepage_renders_70_30_layout_when_configured(): void
    {
        SystemSetting::query()->create([
            'key' => 'website.homepage.layout',
            'value' => 'two_column_70_30',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('data-homepage-layout="two_column_70_30"', false);
        $response->assertSee('data-homepage-main="two_column_70_30"', false);
        $response->assertSee('data-homepage-sidebar', false);
    }

    public function test_homepage_sidebar_content_appears_when_configured(): void
    {
        SystemSetting::query()->insert([
            [
                'key' => 'website.homepage.layout',
                'value' => 'two_column_70_30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'website.homepage.sidebar_title',
                'value' => 'Inside NewsTech',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'website.homepage.sidebar_content',
                'value' => 'Daily editorial notes and supporting promos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'website.homepage.sidebar_link_label',
                'value' => 'Learn more',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'website.homepage.sidebar_link_url',
                'value' => 'https://example.com/about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Inside NewsTech');
        $response->assertSee('Daily editorial notes and supporting promos.');
        $response->assertSee('Learn more');
    }
}
