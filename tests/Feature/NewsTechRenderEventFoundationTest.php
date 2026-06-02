<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Support\RenderEventManager;
use Tests\TestCase;

class NewsTechRenderEventFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_event_manager_renders_nothing_when_no_listeners_exist(): void
    {
        $view = $this->blade('{!! newstech_view_render_event("tests.render.empty") !!}');

        $this->assertSame('', trim((string) $view));
    }

    public function test_render_event_manager_renders_registered_callback_and_view_content(): void
    {
        $manager = app(RenderEventManager::class);

        config()->set('newstech-advertisement.placeholders_enabled', true);

        $manager->register('tests.render.filled', fn (): string => '<div>Callback Marker</div>');
        $manager->registerView('tests.render.filled', 'newstech-advertisement::placeholder', [
            'key' => 'listing_top',
            'compact' => true,
        ]);

        $view = $this->blade('{!! newstech_view_render_event("tests.render.filled") !!}');

        $view->assertSee('Callback Marker', false);
        $view->assertSee('Listing Top');
    }

    public function test_frontend_homepage_includes_registered_render_hook_output(): void
    {
        app(RenderEventManager::class)->register(
            'frontend.homepage.top.before',
            fn (): string => '<div data-test-hook="homepage-top">Homepage Hook</div>'
        );

        $this->get(route('newstech.home'))
            ->assertOk()
            ->assertSee('data-test-hook="homepage-top"', false)
            ->assertSee('Homepage Hook');
    }

    public function test_article_detail_includes_registered_render_hook_output(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Render Event Article',
            'slug' => 'render-event-article',
            'content' => '<p>Story body.</p>',
        ]);

        app(RenderEventManager::class)->register(
            'frontend.article.show.content.before',
            fn (): string => '<div data-test-hook="article-before-content">Article Hook</div>'
        );

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('data-test-hook="article-before-content"', false)
            ->assertSee('Article Hook');
    }

    public function test_listing_page_includes_registered_render_hook_output(): void
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
            'title' => 'Listing Hook Story',
        ]);

        app(RenderEventManager::class)->register(
            'frontend.listing.top',
            fn (): string => '<div data-test-hook="listing-top">Listing Hook</div>'
        );

        $this->get(route('newstech.categories.show', ['slug' => $category->slug]))
            ->assertOk()
            ->assertSee('data-test-hook="listing-top"', false)
            ->assertSee('Listing Hook');
    }

    public function test_admin_layout_headings_no_longer_repeat_newstech_admin_prefix(): void
    {
        $adminUser = AdminUser::factory()->create();

        foreach ([
            ['route' => 'admin.newstech.authors.index', 'heading' => 'Authors'],
            ['route' => 'admin.newstech.comments.index', 'heading' => 'Comments'],
            ['route' => 'admin.newstech.tags.index', 'heading' => 'Tags'],
        ] as $page) {
            $response = $this->actingAs($adminUser, 'admin')->get(route($page['route']));

            $response->assertOk();
            $response->assertDontSee('<h1 class="text-3xl font-black tracking-tight text-stone-950">'.$page['heading'].'</h1>', false);
            $response->assertSee('<h2 class="text-3xl font-black tracking-tight text-stone-950">'.$page['heading'].'</h2>', false);
        }
    }
}
