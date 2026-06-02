<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use Tests\TestCase;

class NewsTechArticleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_detail_visit_increments_view_count(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'public-view-story',
            'view_count' => 0,
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk();

        $this->assertSame(1, $article->fresh()->view_count);
    }

    public function test_unpublished_article_cannot_be_viewed_or_counted_publicly(): void
    {
        $article = Article::factory()->create([
            'slug' => 'hidden-draft-analytics-story',
            'status' => 'draft',
            'view_count' => 0,
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertNotFound();

        $this->assertSame(0, $article->fresh()->view_count);
    }

    public function test_multiple_public_views_increment_count_correctly(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'repeat-view-story',
            'view_count' => 0,
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))->assertOk();
        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))->assertOk();
        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))->assertOk();

        $this->assertSame(3, $article->fresh()->view_count);
    }

    public function test_admin_article_datagrid_shows_view_count(): void
    {
        $adminUser = AdminUser::factory()->create();

        $article = Article::factory()->create([
            'title' => 'Datagrid View Count Story',
            'view_count' => 42,
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.articles.index'));

        $response->assertOk();
        $response->assertSee('Views');
        $response->assertSee('42');
        $response->assertSee($article->title);
    }

    public function test_admin_dashboard_shows_top_viewed_articles_widget(): void
    {
        $adminUser = AdminUser::factory()->create();

        Article::factory()->published()->create([
            'title' => 'Most Read Story',
            'view_count' => 250,
        ]);

        Article::factory()->published()->create([
            'title' => 'Second Most Read Story',
            'view_count' => 120,
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Top viewed published articles');
        $response->assertSee('Most Read Story');
        $response->assertSee('250 views');
    }
}
