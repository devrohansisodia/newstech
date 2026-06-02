<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Page\Models\Page;
use Tests\TestCase;

class NewsTechFrontendDatabasePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_database_page_loads_publicly(): void
    {
        $page = Page::factory()->create([
            'title' => 'Advertise With NewsTech',
            'slug' => 'advertise-with-newstech',
            'content' => '<h2>Advertise With NewsTech</h2><p>Advertising opportunities for <strong>NewsTech partners</strong>.</p>',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.pages.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee($page->title);
        $response->assertSee('<h2>Advertise With NewsTech</h2>', false);
        $response->assertSee('<strong>NewsTech partners</strong>', false);
        $response->assertDontSee('&lt;strong&gt;NewsTech partners&lt;/strong&gt;', false);
    }

    public function test_database_page_renders_inline_images_and_content_spacing_classes(): void
    {
        $page = Page::factory()->create([
            'title' => 'Image Rich About Page',
            'slug' => 'image-rich-about-page',
            'content' => '<p>Intro paragraph.</p><img src="/storage/newstech/media/page-inline.webp" alt="Page inline image"><p>Outro paragraph.</p>',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.pages.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('class="nt-prose', false);
        $response->assertSee('data-rich-content', false);
        $response->assertSee('<img src="/storage/newstech/media/page-inline.webp" alt="Page inline image">', false);
        $response->assertDontSee('&lt;img src=&quot;/storage/newstech/media/page-inline.webp&quot;', false);
    }

    public function test_database_page_strips_unsafe_link_and_image_attributes(): void
    {
        $page = Page::factory()->create([
            'title' => 'Sanitized Database Page',
            'slug' => 'sanitized-database-page',
            'content' => '<p><a href="/contact" onclick="alert(1)">Contact</a></p><img src="/storage/newstech/media/page-inline.webp" alt="Page inline image" onerror="alert(2)"><img src="data:text/html;base64,abc" alt="Unsafe image">',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.pages.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('<a href="/contact">Contact</a>', false);
        $response->assertSee('<img src="/storage/newstech/media/page-inline.webp" alt="Page inline image">', false);
        $response->assertDontSee('onclick=', false);
        $response->assertDontSee('onerror=', false);
        $response->assertDontSee('data:text/html', false);
    }

    public function test_inactive_database_page_returns_404(): void
    {
        $page = Page::factory()->create([
            'slug' => 'hidden-page',
            'status' => false,
        ]);

        $this->get(route('newstech.pages.show', ['slug' => $page->slug]))
            ->assertNotFound();
    }

    public function test_database_backed_about_page_overrides_frontend_about_page_when_active(): void
    {
        Page::factory()->create([
            'title' => 'About NewsTech',
            'slug' => 'about',
            'content' => '<p>Database managed <strong>about</strong> page content.</p>',
            'meta_title' => 'About NewsTech From Admin',
            'meta_description' => 'Database managed about page metadata.',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.about'));

        $response->assertOk();
        $response->assertSee('<strong>about</strong>', false);
        $response->assertDontSee('A modular newsroom platform built for fast, SEO-first publishing.');
    }

    public function test_hardcoded_static_page_fallbacks_still_load_when_no_database_page_exists(): void
    {
        $this->get(route('newstech.about'))
            ->assertOk()
            ->assertSee('A modular newsroom platform built for fast, SEO-first publishing.');

        $this->get(route('newstech.contact'))
            ->assertOk()
            ->assertSee('Get in touch with the NewsTech team.');

        $this->get(route('newstech.privacy-policy'))
            ->assertOk()
            ->assertSee('How NewsTech handles privacy, analytics, and reader data.');

        $this->get(route('newstech.terms'))
            ->assertOk()
            ->assertSee('Terms for using the NewsTech website and published content.');
    }

    public function test_page_seo_meta_title_description_and_canonical_render_from_database_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Advertise With NewsTech',
            'slug' => 'advertise',
            'meta_title' => 'Advertise | NewsTech',
            'meta_description' => 'Reach the NewsTech audience with sponsorship opportunities.',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.pages.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('<title>Advertise | NewsTech</title>', false);
        $response->assertSee('name="description" content="Reach the NewsTech audience with sponsorship opportunities."', false);
        $response->assertSee('rel="canonical" href="'.route('newstech.pages.show', ['slug' => $page->slug]).'"', false);
    }
}
