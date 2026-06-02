<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Seo\Support\SeoAnalyzer;
use Tests\TestCase;

class NewsTechSeoToolkitTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_seo_analysis_endpoint(): void
    {
        $this->postJson('/admin/seo/analyze', [
            'type' => 'article',
            'title' => 'Metro Budget Vote',
        ])->assertRedirect(route('admin.newstech.login'));
    }

    public function test_admin_can_analyze_article_payload_and_receive_score_grade_issues_and_preview(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->postJson('/admin/seo/analyze', [
            'type' => 'article',
            'title' => 'Metro Budget Vote',
            'slug' => 'metro-budget-vote',
            'excerpt' => 'A short budget summary for readers.',
            'content_html' => '<p>Short body.</p><img src="/storage/example.webp">',
            'meta_title' => '',
            'meta_description' => '',
            'featured_image' => '',
            'focus_keyword' => 'budget vote',
            'status' => 'draft',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'score',
            'grade',
            'errors',
            'warnings',
            'suggestions',
            'checklist',
            'preview' => [
                'title',
                'url',
                'description',
                'social_title',
                'social_description',
                'social_image',
                'canonical_url',
            ],
        ]);
        $response->assertJsonPath('grade', 'poor');
        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_admin_can_analyze_page_payload_and_receive_good_grade_for_complete_content(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->postJson('/admin/seo/analyze', [
            'type' => 'page',
            'title' => 'About NewsTech',
            'slug' => 'about-newstech',
            'content_html' => '<h2>Our mission</h2><p>About NewsTech explains how the newsroom publishes clear reporting for daily readers with editorial context, author transparency, taxonomy-driven discovery, and consistent update cycles across fast-moving coverage areas.</p><p>The page also outlines workflow expectations for editors, explains how article pages, supporting static pages, and search surfaces fit together, and gives readers a clearer understanding of the editorial platform behind the public site.</p><p>Read more on <a href="/contact">our contact page</a> or on <a href="https://example.com/partners" rel="noopener noreferrer">partner coverage</a>.</p>',
            'meta_title' => 'About NewsTech Editorial Platform | NewsTech',
            'meta_description' => 'About NewsTech explains the editorial platform, newsroom mission, publishing approach, and reader experience across the site.',
            'featured_image' => 'pages/about-cover.webp',
            'focus_keyword' => 'About NewsTech',
            'status' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('grade', 'good');
        $response->assertJsonPath('preview.title', 'About NewsTech Editorial Platform | NewsTech');
    }

    public function test_analyzer_flags_missing_meta_fields_invalid_slug_and_inline_images_without_alt_text(): void
    {
        $result = app(SeoAnalyzer::class)->analyze([
            'type' => 'article',
            'title' => 'Budget Hearing Update',
            'slug' => 'Budget Hearing Update',
            'content_html' => '<p>Short body.</p><img src="/storage/example.webp"><script>alert("x")</script>',
            'meta_title' => '',
            'meta_description' => '',
            'featured_image' => '',
            'focus_keyword' => 'budget hearing',
            'status' => 'draft',
        ])->toArray();

        $this->assertSame('poor', $result['grade']);
        $this->assertContains('meta_title_missing', array_column($result['errors'], 'code'));
        $this->assertContains('meta_description_missing', array_column($result['errors'], 'code'));
        $this->assertContains('slug_format', array_column($result['warnings'], 'code'));
        $this->assertContains('inline_images_missing_alt', array_column($result['warnings'], 'code'));
    }
}
