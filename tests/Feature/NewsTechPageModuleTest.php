<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Page\Models\Page;
use Tests\TestCase;

class NewsTechPageModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_page_admin_routes(): void
    {
        $this->get(route('admin.newstech.pages.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.pages.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_page_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $page = Page::factory()->create([
            'title' => 'About NewsTech',
            'slug' => 'about-newstech',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.pages.index'));

        $response->assertOk();
        $response->assertSee('Pages');
        $response->assertSee($page->title);
        $response->assertSee('Add Page');
    }

    public function test_rich_text_editor_component_renders_on_page_form(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.pages.create'));

        $response->assertOk();
        $response->assertSee('data-rich-text-editor', false);
        $response->assertSee('data-rich-text-editor-toolbar', false);
        $response->assertSee('data-rich-text-editor-content', false);
        $response->assertSee('data-rich-text-editor-source', false);
        $response->assertSee('data-rich-text-editor-action="link"', false);
        $response->assertSee('data-rich-text-editor-action="image"', false);
        $response->assertSee('data-rich-text-editor-image-picker-open', false);
        $response->assertSee('data-editor-image-modal-root', false);
        $response->assertDontSee('data-rich-text-editor-image-picker-root', false);
        $response->assertSee('name="content"', false);
    }

    public function test_page_form_renders_seo_score_panel_root_and_focus_keyword_field(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.pages.create'));

        $response->assertOk();
        $response->assertSee('name="focus_keyword"', false);
        $response->assertSee('data-seo-score-panel-root="true"', false);
        $response->assertSee('data-seo-score-panel-config', false);
    }

    public function test_logged_in_admin_can_create_page(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.pages.store'), [
            'title' => 'Advertise With Us',
            'slug' => 'Advertise With Us',
            'content' => '<h2>Advertise With Us</h2><p>Advertising information for <strong>NewsTech partners</strong>.</p>',
            'meta_title' => 'Advertise With NewsTech',
            'meta_description' => 'Advertising and sponsorship information.',
            'focus_keyword' => 'advertise with us',
            'status' => '1',
        ]);

        $response->assertRedirect(route('admin.newstech.pages.index'));
        $this->assertDatabaseHas('pages', [
            'title' => 'Advertise With Us',
            'slug' => 'advertise-with-us',
            'status' => 1,
            'content' => '<h2>Advertise With Us</h2><p>Advertising information for <strong>NewsTech partners</strong>.</p>',
            'focus_keyword' => 'advertise with us',
        ]);
    }

    public function test_page_can_store_html_content_with_inline_images(): void
    {
        $adminUser = AdminUser::factory()->create();
        $content = '<p>Intro paragraph.</p><img src="/storage/newstech/media/page-inline.webp" alt="Page inline image"><p>Closing paragraph.</p>';

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.pages.store'), [
            'title' => 'Image Rich Page',
            'slug' => 'Image Rich Page',
            'content' => $content,
            'status' => '1',
        ]);

        $response->assertRedirect(route('admin.newstech.pages.index'));
        $this->assertDatabaseHas('pages', [
            'title' => 'Image Rich Page',
            'slug' => 'image-rich-page',
            'content' => $content,
        ]);
    }

    public function test_validation_fails_for_missing_title_and_slug(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.pages.create'))
            ->post(route('admin.newstech.pages.store'), [
                'title' => '',
                'slug' => '',
            ]);

        $response->assertRedirect(route('admin.newstech.pages.create'));
        $response->assertSessionHasErrors(['title', 'slug']);
    }

    public function test_logged_in_admin_can_update_page(): void
    {
        $adminUser = AdminUser::factory()->create();
        $page = Page::factory()->create([
            'title' => 'Contact NewsTech',
            'slug' => 'contact-newstech',
            'status' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.pages.update', $page), [
            'title' => 'Contact The NewsTech Team',
            'slug' => 'Contact The NewsTech Team',
            'content' => '<p>Updated contact information with a <a href="/contact">direct contact link</a>.</p>',
            'meta_title' => 'Contact NewsTech',
            'meta_description' => 'Updated contact page metadata.',
            'focus_keyword' => 'contact newstech team',
        ]);

        $response->assertRedirect(route('admin.newstech.pages.index'));
        $this->assertDatabaseHas('pages', [
            'id' => $page->getKey(),
            'title' => 'Contact The NewsTech Team',
            'slug' => 'contact-the-newstech-team',
            'status' => 0,
            'content' => '<p>Updated contact information with a <a href="/contact">direct contact link</a>.</p>',
            'focus_keyword' => 'contact newstech team',
        ]);
    }

    public function test_page_edit_form_shows_existing_html_content_in_editor_source(): void
    {
        $adminUser = AdminUser::factory()->create();
        $page = Page::factory()->create([
            'content' => '<p>Existing <strong>page</strong> content.</p>',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.pages.edit', $page));

        $response->assertOk();
        $response->assertSee('data-rich-text-editor', false);
        $response->assertSee('&lt;p&gt;Existing &lt;strong&gt;page&lt;/strong&gt; content.&lt;/p&gt;', false);
        $response->assertSee('name="focus_keyword"', false);
        $response->assertSee('data-seo-score-panel-root="true"', false);
    }

    public function test_logged_in_admin_can_delete_page(): void
    {
        $adminUser = AdminUser::factory()->create();
        $page = Page::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.pages.destroy', $page));

        $response->assertRedirect(route('admin.newstech.pages.index'));
        $this->assertSoftDeleted('pages', [
            'id' => $page->getKey(),
        ]);
    }

    public function test_page_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Pages');
        $response->assertSee(route('admin.newstech.pages.index'));
    }

    public function test_existing_frontend_static_pages_still_load(): void
    {
        $this->get(route('newstech.about'))->assertOk();
        $this->get(route('newstech.contact'))->assertOk();
        $this->get(route('newstech.privacy-policy'))->assertOk();
        $this->get(route('newstech.terms'))->assertOk();
    }
}
