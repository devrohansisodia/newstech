<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Comment\Models\Comment;
use NewsTech\Media\Models\Media;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechArticleModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_article_admin_routes(): void
    {
        $this->get(route('admin.newstech.articles.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $this->get(route('admin.newstech.articles.create'))
            ->assertRedirect(route('admin.newstech.login'));
    }

    public function test_logged_in_admin_can_access_article_index(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->create([
            'title' => 'Emergency Budget Approved',
            'slug' => 'emergency-budget-approved',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.index'));

        $response->assertOk();
        $response->assertSee('Articles');
        $response->assertDontSee('Editorial Module');
        $response->assertSee($article->title);
        $response->assertSee('Add Article');
    }

    public function test_media_picker_component_renders_on_article_form(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.create'));

        $response->assertOk();
        $response->assertSee('Select Image');
        $response->assertSee('data-media-picker-open', false);
        $response->assertSee('text-amber-700', false);
        $response->assertSee('data-media-picker', false);
        $response->assertSee('data-media-picker-root="true"', false);
        $response->assertSee('data-vue-mount-status="pending"', false);
        $response->assertSee('data-media-picker-config', false);
        $response->assertSee('data-media-picker-hidden-input', false);
        $response->assertSee('data-media-picker-preview', false);
        $response->assertSee('data-media-picker-open', false);
        $response->assertSee('data-media-picker-clear', false);
        $response->assertSee('meta name="csrf-token"', false);
        $response->assertSee('name="featured_image"', false);
        $response->assertDontSee('action="'.route('admin.newstech.media.picker.upload').'"', false);
    }

    public function test_rich_text_editor_component_renders_on_article_form(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.create'));

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
        $response->assertSee('Featured Image');
        $response->assertSee('Select or replace the main article image.');
    }

    public function test_article_form_renders_seo_score_panel_root_and_focus_keyword_field(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.create'));

        $response->assertOk();
        $response->assertSee('name="focus_keyword"', false);
        $response->assertSee('data-seo-score-panel-root="true"', false);
        $response->assertSee('data-seo-score-panel-config', false);
    }

    public function test_article_create_form_renders_category_tree_checkboxes_instead_of_single_select(): void
    {
        $adminUser = AdminUser::factory()->create();
        $parentCategory = Category::factory()->create(['name' => 'Politics']);
        $childCategory = Category::factory()->create([
            'name' => 'Elections',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.create'));

        $response->assertOk();
        $response->assertSee('name="categories[]"', false);
        $response->assertSee('type="checkbox"', false);
        $response->assertSee($parentCategory->name);
        $response->assertSee($childCategory->name);
        $response->assertDontSee('name="category_id"', false);
    }

    public function test_article_edit_form_preselects_multiple_categories(): void
    {
        $adminUser = AdminUser::factory()->create();
        $primaryCategory = Category::factory()->create(['name' => 'Politics']);
        $secondaryCategory = Category::factory()->create(['name' => 'Elections']);
        $article = Article::factory()->create([
            'category_id' => $primaryCategory->getKey(),
        ]);

        $article->categories()->sync([$primaryCategory->getKey(), $secondaryCategory->getKey()]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.edit', $article));

        $response->assertOk();
        $response->assertSee('name="categories[]"', false);
        $response->assertSee('value="'.$primaryCategory->getKey().'"', false);
        $response->assertSee('value="'.$secondaryCategory->getKey().'"', false);
        $response->assertSee('checked', false);
    }

    public function test_article_edit_form_category_tree_omits_category_descriptions_and_stays_in_one_container(): void
    {
        $adminUser = AdminUser::factory()->create();
        $primaryCategory = Category::factory()->create([
            'name' => 'Politics',
            'description' => 'Government and election coverage.',
        ]);
        $article = Article::factory()->create([
            'category_id' => $primaryCategory->getKey(),
        ]);

        $article->categories()->sync([$primaryCategory->getKey()]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.edit', $article));

        $response->assertOk();
        $response->assertSee('Choose one or more categories. The first selected category remains the primary category.');
        $response->assertSee('rounded-2xl border border-stone-200 bg-white p-3', false);
        $response->assertSee('Politics');
        $response->assertDontSee('Government and election coverage.');
    }

    public function test_article_form_uses_top_header_actions_and_removes_form_actions_copy(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->create();

        $createResponse = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Create Article');
        $createResponse->assertSee('Back to Articles');
        $createResponse->assertSee('form="article-form"', false);
        $createResponse->assertDontSee('Form Actions');
        $createResponse->assertDontSee('Editorial Module');
        $createResponse->assertDontSee('Core article fields for title, slug, summary, longform content, and editorial ownership.');

        $editResponse = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.edit', $article));
        $editResponse->assertOk();
        $editResponse->assertSee('Update Article');
        $editResponse->assertSee('Back to Articles');
        $editResponse->assertSee('form="article-form"', false);
        $editResponse->assertDontSee('Form Actions');
        $editResponse->assertDontSee('Editorial Module');
        $editResponse->assertDontSee('Update an existing article while keeping the newsroom workflow consistent with the rest of the platform foundation.');
        $editResponse->assertDontSee('Editorial Basics');
        $editResponse->assertDontSee('Marks the article for future featured placement.');
        $editResponse->assertDontSee('Marks the article for future breaking news treatment.');
        $editResponse->assertSee('General');
        $editResponse->assertSeeInOrder(['Publishing Controls', 'Featured Image', 'Content Relations'], false);
    }

    public function test_logged_in_admin_can_create_article(): void
    {
        $adminUser = AdminUser::factory()->create();
        $primaryCategory = Category::factory()->create();
        $secondaryCategory = Category::factory()->create();
        $author = Author::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.articles.store'), [
            'categories' => [$primaryCategory->getKey(), $secondaryCategory->getKey()],
            'author_id' => $author->getKey(),
            'title' => 'Metro Rail Expansion Clears Final Approval',
            'slug' => 'Metro Rail Expansion Clears Final Approval',
            'excerpt' => 'Transit expansion project receives final clearance.',
            'content' => '<h2>Transit expansion project</h2><p>Full article body for the <strong>transit expansion</strong> project.</p>',
            'featured_image' => 'articles/metro-rail-expansion.jpg',
            'status' => 'scheduled',
            'is_featured' => '1',
            'is_breaking' => '1',
            'scheduled_at' => '2026-05-12T10:30',
            'meta_title' => 'Metro Rail Expansion | NewsTech',
            'meta_description' => 'Transit project update.',
            'focus_keyword' => 'metro rail expansion',
            'tag_ids' => $tags->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('admin.newstech.articles.index'));
        $this->assertDatabaseHas('articles', [
            'category_id' => $primaryCategory->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Metro Rail Expansion Clears Final Approval',
            'slug' => 'metro-rail-expansion-clears-final-approval',
            'status' => 'scheduled',
            'is_featured' => 1,
            'is_breaking' => 1,
            'content' => '<h2>Transit expansion project</h2><p>Full article body for the <strong>transit expansion</strong> project.</p>',
            'focus_keyword' => 'metro rail expansion',
        ]);
        $this->assertDatabaseHas('article_category', [
            'article_id' => Article::query()->where('slug', 'metro-rail-expansion-clears-final-approval')->value('id'),
            'category_id' => $primaryCategory->getKey(),
        ]);
        $this->assertDatabaseHas('article_category', [
            'article_id' => Article::query()->where('slug', 'metro-rail-expansion-clears-final-approval')->value('id'),
            'category_id' => $secondaryCategory->getKey(),
        ]);
    }

    public function test_article_can_store_featured_image_separately_from_inline_editor_images(): void
    {
        $adminUser = AdminUser::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        $inlineContent = '<p>Opening paragraph.</p><img src="/storage/newstech/media/story-top.webp" alt="Story top image"><p>Middle paragraph.</p><img src="https://cdn.example.com/story-end.webp" alt="Story end image">';

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.articles.store'), [
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Inline Image Story',
            'slug' => 'Inline Image Story',
            'content' => $inlineContent,
            'featured_image' => 'newstech/media/featured-story.webp',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.newstech.articles.index'));
        $this->assertDatabaseHas('articles', [
            'title' => 'Inline Image Story',
            'featured_image' => 'newstech/media/featured-story.webp',
            'content' => $inlineContent,
        ]);
    }

    public function test_article_can_save_selected_media_as_featured_image(): void
    {
        $adminUser = AdminUser::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $media = Media::factory()->create([
            'path' => 'newstech/media/article-featured.jpg',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->post(route('admin.newstech.articles.store'), [
            'category_id' => $category->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Selected Media Story',
            'slug' => 'Selected Media Story',
            'featured_image' => $media->path,
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.newstech.articles.index'));
        $this->assertDatabaseHas('articles', [
            'title' => 'Selected Media Story',
            'featured_image' => 'newstech/media/article-featured.jpg',
        ]);
    }

    public function test_article_edit_form_shows_existing_featured_image_preview_in_media_picker_fallback(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->create([
            'featured_image' => 'newstech/media/existing-featured-image.webp',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.edit', $article));

        $response->assertOk();
        $response->assertSee('name="featured_image"', false);
        $response->assertSee('value="newstech/media/existing-featured-image.webp"', false);
        $response->assertSee('/storage/newstech/media/existing-featured-image.webp', false);
        $response->assertSee('name="focus_keyword"', false);
        $response->assertSee('data-seo-score-panel-root="true"', false);
    }

    public function test_validation_fails_for_missing_title_and_slug(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->from(route('admin.newstech.articles.create'))
            ->post(route('admin.newstech.articles.store'), [
                'title' => '',
                'slug' => '',
            ]);

        $response->assertRedirect(route('admin.newstech.articles.create'));
        $response->assertSessionHasErrors(['title', 'slug']);
    }

    public function test_logged_in_admin_can_update_article(): void
    {
        $adminUser = AdminUser::factory()->create();
        $primaryCategory = Category::factory()->create();
        $secondaryCategory = Category::factory()->create();
        $author = Author::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $article = Article::factory()->create([
            'status' => 'draft',
            'is_featured' => true,
            'is_breaking' => true,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->put(route('admin.newstech.articles.update', $article), [
            'categories' => [$primaryCategory->getKey(), $secondaryCategory->getKey()],
            'author_id' => $author->getKey(),
            'title' => 'Updated Council Budget Vote',
            'slug' => 'Updated Council Budget Vote',
            'excerpt' => 'Updated summary.',
            'content' => '<p>Updated article <em>content</em> with <a href="https://example.com">reference link</a>.</p>',
            'featured_image' => 'articles/updated-budget-vote.jpg',
            'status' => 'published',
            'published_at' => '2026-05-10T09:00',
            'meta_title' => 'Updated Budget Vote | NewsTech',
            'meta_description' => 'Updated SEO text.',
            'focus_keyword' => 'council budget vote',
            'tag_ids' => $tags->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('admin.newstech.articles.index'));
        $this->assertDatabaseHas('articles', [
            'id' => $article->getKey(),
            'category_id' => $primaryCategory->getKey(),
            'author_id' => $author->getKey(),
            'title' => 'Updated Council Budget Vote',
            'slug' => 'updated-council-budget-vote',
            'status' => 'published',
            'is_featured' => 0,
            'is_breaking' => 0,
            'content' => '<p>Updated article <em>content</em> with <a href="https://example.com">reference link</a>.</p>',
            'focus_keyword' => 'council budget vote',
        ]);
        $this->assertDatabaseHas('article_category', [
            'article_id' => $article->getKey(),
            'category_id' => $primaryCategory->getKey(),
        ]);
        $this->assertDatabaseHas('article_category', [
            'article_id' => $article->getKey(),
            'category_id' => $secondaryCategory->getKey(),
        ]);
    }

    public function test_article_edit_form_shows_existing_html_content_in_editor_source(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->create([
            'content' => '<p>Existing <strong>formatted</strong> article content.</p><img src="/storage/newstech/media/existing-inline.webp" alt="Existing inline image">',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.articles.edit', $article));

        $response->assertOk();
        $response->assertSee('data-rich-text-editor', false);
        $response->assertSee('&lt;p&gt;Existing &lt;strong&gt;formatted&lt;/strong&gt; article content.&lt;/p&gt;', false);
        $response->assertSee('&lt;img src=&quot;/storage/newstech/media/existing-inline.webp&quot; alt=&quot;Existing inline image&quot;&gt;', false);
    }

    public function test_logged_in_admin_can_delete_article(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->delete(route('admin.newstech.articles.destroy', $article));

        $response->assertRedirect(route('admin.newstech.articles.index'));
        $this->assertSoftDeleted('articles', [
            'id' => $article->getKey(),
        ]);
    }

    public function test_article_can_attach_category(): void
    {
        $article = Article::factory()->create();
        $category = Category::factory()->create();

        $article->category()->associate($category);
        $article->save();

        $this->assertSame($category->getKey(), $article->fresh()->category?->getKey());
    }

    public function test_article_can_attach_multiple_categories_while_preserving_primary_category(): void
    {
        $article = Article::factory()->create();
        $primaryCategory = Category::factory()->create();
        $secondaryCategory = Category::factory()->create();

        $article->category()->associate($primaryCategory);
        $article->save();
        $article->categories()->sync([$primaryCategory->getKey(), $secondaryCategory->getKey()]);

        $this->assertSame($primaryCategory->getKey(), $article->fresh()->category?->getKey());
        $this->assertCount(2, $article->fresh()->categories);
    }

    public function test_article_can_attach_author(): void
    {
        $article = Article::factory()->create();
        $author = Author::factory()->create();

        $article->author()->associate($author);
        $article->save();

        $this->assertSame($author->getKey(), $article->fresh()->author?->getKey());
    }

    public function test_article_can_attach_tags(): void
    {
        $article = Article::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $article->tags()->sync($tags->pluck('id')->all());

        $this->assertCount(2, $article->fresh()->tags);
    }

    public function test_article_can_attach_comments(): void
    {
        $article = Article::factory()->create();
        Comment::factory()->count(2)->create([
            'article_id' => $article->getKey(),
        ]);

        $this->assertCount(2, $article->fresh()->comments);
    }

    public function test_article_appears_in_admin_menu_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Articles');
        $response->assertSee(route('admin.newstech.articles.index'));
    }
}
