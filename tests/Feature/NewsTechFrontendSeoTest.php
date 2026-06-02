<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use Tests\TestCase;

class NewsTechFrontendSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_home_page_renders_seo_title(): void
    {
        Article::factory()->published()->create([
            'title' => 'City Hall Approves Budget Plan',
            'excerpt' => 'Latest published coverage from the city desk.',
            'featured_image' => 'articles/city-hall-budget.jpg',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('<title>NewsTech | City Hall Approves Budget Plan</title>', false);
    }

    public function test_frontend_home_page_renders_meta_description(): void
    {
        Article::factory()->published()->create([
            'title' => 'State Assembly Opens Special Session',
            'excerpt' => 'Special session coverage leads the front page.',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('name="description" content="Special session coverage leads the front page."', false);
    }

    public function test_frontend_home_page_renders_canonical_url(): void
    {
        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('rel="canonical" href="'.route('newstech.home').'"', false);
    }

    public function test_frontend_home_page_renders_open_graph_tags(): void
    {
        Article::factory()->published()->create([
            'title' => 'Parliament Session Highlights',
            'excerpt' => "A quick summary of today's parliamentary developments.",
            'featured_image' => 'articles/parliament-session.jpg',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('property="og:title" content="NewsTech | Parliament Session Highlights"', false);
        $response->assertSee('property="og:description" content="A quick summary of today&#039;s parliamentary developments."', false);
        $response->assertSee('property="og:url" content="'.route('newstech.home').'"', false);
    }

    public function test_frontend_home_page_renders_twitter_card_tags(): void
    {
        Article::factory()->published()->create([
            'title' => 'Market Watch Evening Update',
            'excerpt' => "Markets, policy, and business headlines leading tonight's front page.",
            'featured_image' => 'articles/market-watch.jpg',
        ]);

        $response = $this->get(route('newstech.home'));

        $response->assertOk();
        $response->assertSee('name="twitter:card" content="summary_large_image"', false);
        $response->assertSee('name="twitter:title" content="NewsTech | Market Watch Evening Update"', false);
        $response->assertSee('name="twitter:description" content="Markets, policy, and business headlines leading tonight&#039;s front page."', false);
    }
}
