<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Page\Models\Page;
use Tests\TestCase;

class NewsTechFrontendStaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_loads(): void
    {
        $response = $this->get(route('newstech.about'));

        $response->assertOk();
        $response->assertSee('About NewsTech');
    }

    public function test_contact_page_loads(): void
    {
        $response = $this->get(route('newstech.contact'));

        $response->assertOk();
        $response->assertSee('Get in touch with the NewsTech team.');
    }

    public function test_privacy_policy_page_loads(): void
    {
        $response = $this->get(route('newstech.privacy-policy'));

        $response->assertOk();
        $response->assertSee('How NewsTech handles privacy, analytics, and reader data.');
    }

    public function test_terms_page_loads(): void
    {
        $response = $this->get(route('newstech.terms'));

        $response->assertOk();
        $response->assertSee('Terms for using the NewsTech website and published content.');
    }

    public function test_static_pages_render_seo_title_meta_and_canonical(): void
    {
        $response = $this->get(route('newstech.about'));

        $response->assertOk();
        $response->assertSee('<title>NewsTech | About</title>', false);
        $response->assertSee('name="description" content="Learn about the NewsTech editorial platform, newsroom direction, and publishing approach."', false);
        $response->assertSee('rel="canonical" href="'.route('newstech.about').'"', false);
    }

    public function test_dynamic_page_route_name_loads_active_database_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Community Standards',
            'slug' => 'community-standards',
            'content' => 'Community standards content.',
            'status' => true,
        ]);

        $response = $this->get(route('newstech.pages.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('Community standards content.');
    }

    public function test_footer_contains_static_page_links(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee(route('newstech.about'));
        $response->assertSee(route('newstech.contact'));
        $response->assertSee(route('newstech.privacy-policy'));
        $response->assertSee(route('newstech.terms'));
    }
}
