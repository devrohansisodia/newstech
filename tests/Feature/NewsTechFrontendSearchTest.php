<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechFrontendSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_loads(): void
    {
        $response = $this->get(route('newstech.search'));

        $response->assertOk();
        $response->assertSee('Search published articles');
    }

    public function test_keyword_search_returns_matching_published_article(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Budget Reform Analysis',
            'excerpt' => 'Deep look at budget reform policy.',
        ]);

        $response = $this->get(route('newstech.search', ['q' => 'budget']));

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_keyword_search_does_not_return_unpublished_articles(): void
    {
        Article::factory()->published()->create([
            'title' => 'Visible Search Story',
            'content' => 'This one should appear in search.',
        ]);

        $draftArticle = Article::factory()->create([
            'title' => 'Hidden Draft Search Story',
            'content' => 'This one should never appear.',
            'status' => 'draft',
        ]);

        $reviewArticle = Article::factory()->create([
            'title' => 'Hidden Review Search Story',
            'content' => 'This one should never appear either.',
            'status' => 'review',
        ]);

        $scheduledArticle = Article::factory()->create([
            'title' => 'Hidden Scheduled Search Story',
            'content' => 'This one should never appear either.',
            'status' => 'scheduled',
        ]);

        $archivedArticle = Article::factory()->create([
            'title' => 'Hidden Archived Search Story',
            'content' => 'This one should never appear either.',
            'status' => 'archived',
        ]);

        $response = $this->get(route('newstech.search', ['q' => 'search story']));

        $response->assertOk();
        $response->assertSee('Visible Search Story');
        $response->assertDontSee($draftArticle->title);
        $response->assertDontSee($reviewArticle->title);
        $response->assertDontSee($scheduledArticle->title);
        $response->assertDontSee($archivedArticle->title);
    }

    public function test_search_page_shows_empty_state_for_no_results(): void
    {
        $response = $this->get(route('newstech.search', ['q' => 'no-match-keyword']));

        $response->assertOk();
        $response->assertSee('No published articles found');
    }

    public function test_category_filter_works(): void
    {
        $politics = Category::factory()->create(['name' => 'Politics', 'slug' => 'politics']);
        $sports = Category::factory()->create(['name' => 'Sports', 'slug' => 'sports']);

        $politicsArticle = Article::factory()->published()->create([
            'category_id' => $politics->getKey(),
            'title' => 'Election Desk Story',
        ]);

        $sportsArticle = Article::factory()->published()->create([
            'category_id' => $sports->getKey(),
            'title' => 'Sports Desk Story',
        ]);

        $response = $this->get(route('newstech.search', ['category' => $politics->slug]));

        $response->assertOk();
        $response->assertSee($politicsArticle->title);
        $response->assertDontSee($sportsArticle->title);
    }

    public function test_author_filter_works(): void
    {
        $authorOne = Author::factory()->create(['name' => 'Aarav Mehta', 'slug' => 'aarav-mehta']);
        $authorTwo = Author::factory()->create(['name' => 'Nina Patel', 'slug' => 'nina-patel']);

        $matchingArticle = Article::factory()->published()->create([
            'author_id' => $authorOne->getKey(),
            'title' => 'Reporter One Story',
        ]);

        $otherArticle = Article::factory()->published()->create([
            'author_id' => $authorTwo->getKey(),
            'title' => 'Reporter Two Story',
        ]);

        $response = $this->get(route('newstech.search', ['author' => $authorOne->slug]));

        $response->assertOk();
        $response->assertSee($matchingArticle->title);
        $response->assertDontSee($otherArticle->title);
    }

    public function test_tag_filter_works(): void
    {
        $policyTag = Tag::factory()->create(['name' => 'Policy', 'slug' => 'policy']);
        $economyTag = Tag::factory()->create(['name' => 'Economy', 'slug' => 'economy']);

        $matchingArticle = Article::factory()->published()->create([
            'title' => 'Policy Tag Story',
        ]);
        $matchingArticle->tags()->sync([$policyTag->getKey()]);

        $otherArticle = Article::factory()->published()->create([
            'title' => 'Economy Tag Story',
        ]);
        $otherArticle->tags()->sync([$economyTag->getKey()]);

        $response = $this->get(route('newstech.search', ['tag' => $policyTag->slug]));

        $response->assertOk();
        $response->assertSee($matchingArticle->title);
        $response->assertDontSee($otherArticle->title);
    }
}
