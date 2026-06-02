<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use Tests\TestCase;

class NewsTechFrontendAdvertisementSlotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_does_not_render_placeholder_slots_by_default(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertDontSee('Advertisement Placeholder');
        $response->assertDontSee('Header Leaderboard');
        $response->assertDontSee('Homepage Top');
        $response->assertDontSee('Homepage Sidebar');
        $response->assertDontSee('Footer Banner');
    }

    public function test_article_detail_does_not_render_placeholder_slots_by_default(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Ad Slot Article',
            'slug' => 'ad-slot-article',
            'content' => 'Story body for inline placement coverage.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertDontSee('Advertisement Placeholder');
        $response->assertDontSee('Article Top');
        $response->assertDontSee('Article Inline');
        $response->assertDontSee('Article Sidebar');
    }

    public function test_listing_and_search_pages_do_not_render_listing_placeholder_by_default(): void
    {
        $category = Category::factory()->create([
            'name' => 'Politics',
            'slug' => 'politics',
            'status' => true,
        ]);

        $author = Author::factory()->create([
            'name' => 'Nina Patel',
            'slug' => 'nina-patel',
            'status' => true,
        ]);

        Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Listing Page Story',
        ]);

        $categoryResponse = $this->get(route('newstech.categories.show', ['slug' => $category->slug]));
        $searchResponse = $this->get(route('newstech.search'));

        $categoryResponse->assertOk();
        $categoryResponse->assertDontSee('Listing Top');
        $categoryResponse->assertDontSee('Advertisement Placeholder');

        $searchResponse->assertOk();
        $searchResponse->assertDontSee('Listing Top');
        $searchResponse->assertDontSee('Advertisement Placeholder');
    }

    public function test_ad_slot_component_can_render_placeholders_when_explicitly_enabled(): void
    {
        config()->set('newstech-advertisement.placeholders_enabled', true);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('Advertisement Placeholder');
        $response->assertSee('Header Leaderboard');
        $response->assertSee('Homepage Top');
        $response->assertSee('Footer Banner');
    }
}
