<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Page\Models\Page;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechFrontendSeoFeedsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_route_loads_xml_and_includes_public_urls(): void
    {
        $category = Category::factory()->create([
            'name' => 'Politics',
            'slug' => 'politics',
            'status' => true,
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Elections',
            'slug' => 'elections',
            'status' => true,
        ]);

        $author = Author::factory()->create([
            'name' => 'Riya Sen',
            'slug' => 'riya-sen',
            'status' => true,
        ]);

        $page = Page::factory()->create([
            'title' => 'Advertise',
            'slug' => 'advertise',
            'status' => true,
        ]);

        $publishedArticle = Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Budget Debate Opens',
            'slug' => 'budget-debate-opens',
        ]);

        $publishedArticle->tags()->sync([$tag->getKey()]);

        Article::factory()->create([
            'title' => 'Draft Story',
            'slug' => 'draft-story',
            'status' => 'draft',
        ]);

        $response = $this->get(route('newstech.sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee(route('newstech.home'), false);
        $response->assertSee(route('newstech.articles.show', ['slug' => $publishedArticle->slug]), false);
        $response->assertDontSee('draft-story', false);
        $response->assertSee(route('newstech.categories.show', ['slug' => $category->slug]), false);
        $response->assertSee(route('newstech.tags.show', ['slug' => $tag->slug]), false);
        $response->assertSee(route('newstech.authors.show', ['slug' => $author->slug]), false);
        $response->assertSee(route('newstech.pages.show', ['slug' => $page->slug]), false);
    }

    public function test_news_sitemap_only_includes_recent_published_articles(): void
    {
        $recentArticle = Article::factory()->published()->create([
            'title' => 'Metro Desk Update',
            'slug' => 'metro-desk-update',
            'published_at' => now()->subHours(6),
        ]);

        Article::factory()->published()->create([
            'title' => 'Older Published Story',
            'slug' => 'older-published-story',
            'published_at' => now()->subDays(5),
        ]);

        $response = $this->get(route('newstech.sitemap-news'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<news:news>', false);
        $response->assertSee(route('newstech.articles.show', ['slug' => $recentArticle->slug]), false);
        $response->assertDontSee('older-published-story', false);
    }

    public function test_rss_route_loads_xml_and_excludes_draft_articles(): void
    {
        $publishedArticle = Article::factory()->published()->create([
            'title' => 'Night Bulletin',
            'slug' => 'night-bulletin',
        ]);

        Article::factory()->create([
            'title' => 'Hidden Draft',
            'slug' => 'hidden-draft',
            'status' => 'draft',
        ]);

        $response = $this->get(route('newstech.rss'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<rss version="2.0">', false);
        $response->assertSee(route('newstech.articles.show', ['slug' => $publishedArticle->slug]), false);
        $response->assertDontSee('hidden-draft', false);
    }

    public function test_category_rss_route_loads_for_active_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Business',
            'slug' => 'business',
            'status' => true,
        ]);

        Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'title' => 'Markets Open Higher',
            'slug' => 'markets-open-higher',
        ]);

        $response = $this->get(route('newstech.categories.rss', ['slug' => $category->slug]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('Markets Open Higher');
        $response->assertSee('<category>Business</category>', false);
    }

    public function test_robots_txt_route_loads_plain_text(): void
    {
        $response = $this->get(route('newstech.robots'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *');
        $response->assertSee('Sitemap: '.route('newstech.sitemap'));
        $response->assertSee('Sitemap: '.route('newstech.sitemap-news'));
    }
}
