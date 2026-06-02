<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechFrontendTaxonomyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_category_page_loads(): void
    {
        $category = Category::factory()->create([
            'name' => 'Politics',
            'slug' => 'politics',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.categories.show', ['slug' => $category->slug]));

        $response->assertOk();
        $response->assertSee('Politics');
    }

    public function test_inactive_category_page_returns_404(): void
    {
        $category = Category::factory()->create([
            'slug' => 'hidden-category',
            'status' => false,
        ]);

        $this->get(route('newstech.categories.show', ['slug' => $category->slug]))
            ->assertNotFound();
    }

    public function test_category_page_shows_published_articles_only(): void
    {
        $category = Category::factory()->create(['name' => 'World']);

        $publishedArticle = Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'title' => 'Visible World Story',
        ]);

        $draftArticle = Article::factory()->create([
            'category_id' => $category->getKey(),
            'title' => 'Hidden Draft Story',
            'status' => 'draft',
        ]);

        $response = $this->get(route('newstech.categories.show', ['slug' => $category->slug]));

        $response->assertOk();
        $response->assertSee($publishedArticle->title);
        $response->assertDontSee($draftArticle->title);
    }

    public function test_category_page_shows_articles_assigned_through_category_pivot(): void
    {
        $primaryCategory = Category::factory()->create(['name' => 'Politics', 'slug' => 'politics']);
        $secondaryCategory = Category::factory()->create(['name' => 'Elections', 'slug' => 'elections']);

        $article = Article::factory()->published()->create([
            'category_id' => $primaryCategory->getKey(),
            'title' => 'Visible Through Pivot Story',
        ]);

        $article->categories()->sync([$primaryCategory->getKey(), $secondaryCategory->getKey()]);

        $response = $this->get(route('newstech.categories.show', ['slug' => $secondaryCategory->slug]));

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_active_tag_page_loads(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Policy',
            'slug' => 'policy',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.tags.show', ['slug' => $tag->slug]));

        $response->assertOk();
        $response->assertSee('Policy');
    }

    public function test_inactive_tag_page_returns_404(): void
    {
        $tag = Tag::factory()->create([
            'slug' => 'hidden-tag',
            'status' => false,
        ]);

        $this->get(route('newstech.tags.show', ['slug' => $tag->slug]))
            ->assertNotFound();
    }

    public function test_tag_page_shows_published_articles_only(): void
    {
        $tag = Tag::factory()->create(['name' => 'Elections']);

        $publishedArticle = Article::factory()->published()->create([
            'title' => 'Visible Tagged Story',
        ]);
        $publishedArticle->tags()->sync([$tag->getKey()]);

        $draftArticle = Article::factory()->create([
            'title' => 'Hidden Tagged Draft',
            'status' => 'draft',
        ]);
        $draftArticle->tags()->sync([$tag->getKey()]);

        $response = $this->get(route('newstech.tags.show', ['slug' => $tag->slug]));

        $response->assertOk();
        $response->assertSee($publishedArticle->title);
        $response->assertDontSee($draftArticle->title);
    }

    public function test_active_author_page_loads(): void
    {
        $author = Author::factory()->create([
            'name' => 'Sara Khan',
            'slug' => 'sara-khan',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.authors.show', ['slug' => $author->slug]));

        $response->assertOk();
        $response->assertSee('Sara Khan');
    }

    public function test_inactive_author_page_returns_404(): void
    {
        $author = Author::factory()->create([
            'slug' => 'hidden-author',
            'status' => false,
        ]);

        $this->get(route('newstech.authors.show', ['slug' => $author->slug]))
            ->assertNotFound();
    }

    public function test_author_page_shows_published_articles_only(): void
    {
        $author = Author::factory()->create(['name' => 'Desk Reporter']);

        $publishedArticle = Article::factory()->published()->create([
            'author_id' => $author->getKey(),
            'title' => 'Visible Byline Story',
        ]);

        $reviewArticle = Article::factory()->create([
            'author_id' => $author->getKey(),
            'title' => 'Hidden Review Story',
            'status' => 'review',
        ]);

        $response = $this->get(route('newstech.authors.show', ['slug' => $author->slug]));

        $response->assertOk();
        $response->assertSee($publishedArticle->title);
        $response->assertDontSee($reviewArticle->title);
    }

    public function test_article_detail_category_tag_and_author_links_point_to_real_routes(): void
    {
        $category = Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);
        $author = Author::factory()->create(['name' => 'Nina Patel', 'slug' => 'nina-patel']);
        $tag = Tag::factory()->create(['name' => 'Startups', 'slug' => 'startups']);

        $article = Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Funding Round Update',
        ]);
        $article->tags()->sync([$tag->getKey()]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee(route('newstech.categories.show', ['slug' => $category->slug]));
        $response->assertSee(route('newstech.tags.show', ['slug' => $tag->slug]));
        $response->assertSee(route('newstech.authors.show', ['slug' => $author->slug]));
    }
}
