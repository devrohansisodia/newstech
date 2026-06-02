<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechFrontendArticleDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_detail_page_loads(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Published Article Detail Story',
            'slug' => 'published-article-detail-story',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_article_detail_page_renders_selected_media_path_as_public_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('newstech/media/detail-hero.webp', 'image');

        $article = Article::factory()->published()->create([
            'title' => 'Detail Media Story',
            'slug' => 'detail-media-story',
            'featured_image' => 'newstech/media/detail-hero.webp',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('/storage/newstech/media/detail-hero.webp', false);
    }

    public function test_article_detail_page_shows_title_content_category_author_and_tags(): void
    {
        $category = Category::factory()->create(['name' => 'Politics']);
        $author = Author::factory()->create(['name' => 'Aarav Mehta']);
        $tags = Tag::factory()->count(2)->create();

        $article = Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Election Rules Updated',
            'excerpt' => 'Election rules summary.',
            'content' => '<h2>Election rules update</h2><p>Full election rules article <strong>content</strong>.</p>',
        ]);

        $article->tags()->sync($tags->pluck('id')->all());

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('Election Rules Updated');
        $response->assertSee('Election rules summary.');
        $response->assertSee('<h2>Election rules update</h2>', false);
        $response->assertSee('<strong>content</strong>', false);
        $response->assertDontSee('&lt;strong&gt;content&lt;/strong&gt;', false);
        $response->assertSee('Politics');
        $response->assertSee('Aarav Mehta');
        $response->assertSee($tags[0]->name);
        $response->assertSee($tags[1]->name);
    }

    public function test_article_detail_page_uses_primary_category_for_breadcrumbs_when_multiple_categories_are_attached(): void
    {
        $primaryCategory = Category::factory()->create(['name' => 'Politics', 'slug' => 'politics']);
        $secondaryCategory = Category::factory()->create(['name' => 'Elections', 'slug' => 'elections']);

        $article = Article::factory()->published()->create([
            'category_id' => $primaryCategory->getKey(),
            'title' => 'Primary Category Breadcrumb Story',
        ]);

        $article->categories()->sync([$primaryCategory->getKey(), $secondaryCategory->getKey()]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee(route('newstech.categories.show', ['slug' => $primaryCategory->slug]));
        $response->assertSee($primaryCategory->name);
    }

    public function test_article_detail_page_renders_inline_images_and_content_spacing_classes(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Inline Image Detail Story',
            'slug' => 'inline-image-detail-story',
            'content' => '<p>Opening paragraph.</p><img src="/storage/newstech/media/story-inline.webp" alt="Inline story image"><p>Closing paragraph.</p>',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('class="nt-prose', false);
        $response->assertSee('data-rich-content', false);
        $response->assertSee('<img src="/storage/newstech/media/story-inline.webp" alt="Inline story image">', false);
        $response->assertDontSee('&lt;img src=&quot;/storage/newstech/media/story-inline.webp&quot;', false);
    }

    public function test_article_detail_page_strips_unsafe_link_and_image_attributes(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Sanitized Rich Text Story',
            'slug' => 'sanitized-rich-text-story',
            'content' => '<p><a href="javascript:alert(1)" onclick="alert(2)">Bad Link</a></p><p><a href="https://example.com" onclick="alert(3)">Safe Link</a></p><img src="/storage/newstech/media/story-inline.webp" alt="Inline story image" onerror="alert(4)"><img src="javascript:alert(5)" alt="Unsafe image">',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('<a>Bad Link</a>', false);
        $response->assertSee('<a href="https://example.com" target="_blank" rel="noopener noreferrer">Safe Link</a>', false);
        $response->assertSee('<img src="/storage/newstech/media/story-inline.webp" alt="Inline story image">', false);
        $response->assertDontSee('onclick=', false);
        $response->assertDontSee('onerror=', false);
        $response->assertDontSee('javascript:alert', false);
    }

    public function test_draft_article_detail_page_returns_404(): void
    {
        $article = Article::factory()->create([
            'slug' => 'draft-hidden-story',
            'status' => 'draft',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertNotFound();
    }

    public function test_review_scheduled_and_archived_articles_do_not_load_publicly(): void
    {
        $reviewArticle = Article::factory()->create([
            'slug' => 'review-hidden-story',
            'status' => 'review',
        ]);

        $scheduledArticle = Article::factory()->create([
            'slug' => 'scheduled-hidden-story',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
        ]);

        $archivedArticle = Article::factory()->create([
            'slug' => 'archived-hidden-story',
            'status' => 'archived',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $reviewArticle->slug]))
            ->assertNotFound();

        $this->get(route('newstech.articles.show', ['slug' => $scheduledArticle->slug]))
            ->assertNotFound();

        $this->get(route('newstech.articles.show', ['slug' => $archivedArticle->slug]))
            ->assertNotFound();
    }

    public function test_article_detail_page_renders_seo_title_meta_and_canonical(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'SEO Headline Story',
            'slug' => 'seo-headline-story',
            'meta_title' => 'SEO Headline Story | NewsTech',
            'meta_description' => 'SEO article description for the public detail page.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('<title>SEO Headline Story | NewsTech</title>', false);
        $response->assertSee('name="description" content="SEO article description for the public detail page."', false);
        $response->assertSee('rel="canonical" href="'.route('newstech.articles.show', ['slug' => $article->slug]).'"', false);
    }

    public function test_article_detail_page_renders_news_article_structured_data(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Structured Data Story',
            'slug' => 'structured-data-story',
            'excerpt' => 'Structured data excerpt.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('"@type": "NewsArticle"', false);
        $response->assertSee('"headline": "Structured Data Story"', false);
    }

    public function test_homepage_links_published_articles_to_detail_route(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Homepage Linked Story',
            'slug' => 'homepage-linked-story',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee(route('newstech.articles.show', ['slug' => $article->slug]));
    }

    public function test_article_detail_sidebar_listing_cards_clamp_excerpt_preview_lines(): void
    {
        $category = Category::factory()->create(['name' => 'Assembly', 'slug' => 'assembly']);

        $article = Article::factory()->published()->create([
            'category_id' => $category->getKey(),
            'title' => 'Primary Assembly Debate Story',
            'slug' => 'primary-assembly-debate-story',
        ]);

        Article::factory()->published()->count(3)->create([
            'category_id' => $category->getKey(),
            'excerpt' => 'This related story excerpt should stay limited to a short sidebar card preview and should not render as a long block in the article detail side column.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('Related stories');
        $response->assertSee('Latest published articles');
        $response->assertSee('nt-line-clamp-4', false);
    }
}
