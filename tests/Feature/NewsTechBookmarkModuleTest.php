<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Reader\Models\Reader;
use Tests\TestCase;

class NewsTechBookmarkModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_bookmark_article(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'guest-bookmark-story',
        ]);

        $this->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]))
            ->assertRedirect(route('newstech.readers.login'));

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_reader_can_bookmark_published_article(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'slug' => 'bookmarkable-story',
        ]);

        $response = $this->actingAs($reader, 'reader')
            ->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]));

        $response->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]));
        $response->assertSessionHas('bookmark_status', 'Article saved.');
        $this->assertDatabaseHas('bookmarks', [
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);
    }

    public function test_reader_cannot_bookmark_unpublished_article(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->create([
            'slug' => 'draft-bookmark-story',
            'status' => 'draft',
        ]);

        $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]))
            ->assertNotFound();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_duplicate_bookmark_is_not_created(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'slug' => 'duplicate-bookmark-story',
        ]);

        $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]));

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]));

        $response->assertSessionHas('bookmark_status', 'This article is already saved.');
        $this->assertDatabaseCount('bookmarks', 1);
    }

    public function test_reader_can_remove_bookmark(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'slug' => 'remove-bookmark-story',
        ]);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);

        $response = $this->actingAs($reader, 'reader')
            ->delete(route('newstech.articles.bookmarks.destroy', ['slug' => $article->slug]));

        $response->assertSessionHas('bookmark_status', 'Article removed from saved articles.');
        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_saved_article_appears_on_account_bookmarks_page(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'title' => 'Saved Article Story',
            'slug' => 'saved-article-story',
        ]);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.bookmarks'))
            ->assertOk()
            ->assertSee('Saved articles')
            ->assertSee('Saved Article Story');
    }

    public function test_removed_bookmark_no_longer_appears_in_saved_articles(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'title' => 'Transient Bookmark Story',
            'slug' => 'transient-bookmark-story',
        ]);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->delete(route('newstech.articles.bookmarks.destroy', ['slug' => $article->slug]));

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.bookmarks'))
            ->assertOk()
            ->assertDontSee('Transient Bookmark Story');
    }

    public function test_article_detail_shows_saved_state_for_saved_article(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'slug' => 'saved-state-story',
        ]);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Remove Bookmark');
    }

    public function test_article_detail_shows_login_prompt_for_guest_bookmark_action(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'guest-bookmark-prompt-story',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Login to save this article');
    }

    public function test_only_published_saved_articles_are_listed(): void
    {
        $reader = Reader::factory()->create();
        $publishedArticle = Article::factory()->published()->create([
            'title' => 'Visible Saved Story',
            'slug' => 'visible-saved-story',
        ]);
        $draftArticle = Article::factory()->create([
            'title' => 'Hidden Saved Story',
            'slug' => 'hidden-saved-story',
            'status' => 'draft',
        ]);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $publishedArticle->getKey(),
        ]);
        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $draftArticle->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.bookmarks'))
            ->assertOk()
            ->assertSee('Visible Saved Story')
            ->assertDontSee('Hidden Saved Story');
    }

    public function test_reader_can_create_bookmark_folder(): void
    {
        $reader = Reader::factory()->create();

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.account.bookmark-folders.store'), [
                'name' => 'Weekend Reads',
            ]);

        $response->assertSessionHas('bookmark_status', 'Bookmark folder created.');
        $this->assertDatabaseHas('bookmark_folders', [
            'reader_id' => $reader->getKey(),
            'name' => 'Weekend Reads',
        ]);
    }

    public function test_reader_can_bookmark_article_into_folder(): void
    {
        $reader = Reader::factory()->create();
        $folder = BookmarkFolder::factory()->create([
            'reader_id' => $reader->getKey(),
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
        $article = Article::factory()->published()->create([
            'slug' => 'folder-bookmark-story',
        ]);

        $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]), [
                'folder_id' => $folder->getKey(),
            ])
            ->assertSessionHas('bookmark_status', 'Article saved.');

        $this->assertDatabaseHas('bookmarks', [
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
            'folder_id' => $folder->getKey(),
        ]);
    }

    public function test_reader_can_move_bookmark_between_folders(): void
    {
        $reader = Reader::factory()->create();
        $sourceFolder = BookmarkFolder::factory()->create(['reader_id' => $reader->getKey()]);
        $targetFolder = BookmarkFolder::factory()->create(['reader_id' => $reader->getKey()]);
        $bookmark = Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'folder_id' => $sourceFolder->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->put(route('newstech.bookmarks.folder.update', $bookmark), [
                'folder_id' => $targetFolder->getKey(),
            ])
            ->assertSessionHas('bookmark_status', 'Bookmark folder updated.');

        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->getKey(),
            'folder_id' => $targetFolder->getKey(),
        ]);
    }

    public function test_reader_cannot_access_another_readers_folder_or_bookmarks(): void
    {
        $reader = Reader::factory()->create();
        $otherReader = Reader::factory()->create();
        $otherFolder = BookmarkFolder::factory()->create(['reader_id' => $otherReader->getKey()]);
        $bookmark = Bookmark::factory()->create(['reader_id' => $otherReader->getKey()]);
        $article = Article::factory()->published()->create([
            'slug' => 'protected-folder-story',
        ]);

        $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.bookmarks.store', ['slug' => $article->slug]), [
                'folder_id' => $otherFolder->getKey(),
            ])
            ->assertNotFound();

        $this->actingAs($reader, 'reader')
            ->put(route('newstech.bookmarks.folder.update', $bookmark), [
                'folder_id' => null,
            ])
            ->assertNotFound();
    }

    public function test_account_bookmarks_page_can_filter_by_folder(): void
    {
        $reader = Reader::factory()->create();
        $folder = BookmarkFolder::factory()->create([
            'reader_id' => $reader->getKey(),
            'name' => 'Saved Reports',
            'slug' => 'saved-reports',
        ]);
        $otherFolder = BookmarkFolder::factory()->create(['reader_id' => $reader->getKey()]);
        $articleInFolder = Article::factory()->published()->create(['title' => 'Folder Story']);
        $otherArticle = Article::factory()->published()->create(['title' => 'Other Story']);

        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $articleInFolder->getKey(),
            'folder_id' => $folder->getKey(),
        ]);
        Bookmark::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $otherArticle->getKey(),
            'folder_id' => $otherFolder->getKey(),
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.bookmarks', ['folder' => 'saved-reports']))
            ->assertOk()
            ->assertSee('Folder Story')
            ->assertDontSee('Other Story');
    }

    public function test_reader_article_view_creates_and_updates_history(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'slug' => 'history-story',
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk();

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk();

        $this->assertDatabaseHas('reader_article_histories', [
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
            'view_count' => 2,
        ]);
    }

    public function test_account_history_shows_recent_articles(): void
    {
        $reader = Reader::factory()->create();
        $article = Article::factory()->published()->create([
            'title' => 'History Visible Story',
            'slug' => 'history-visible-story',
        ]);

        ReaderArticleHistory::factory()->create([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
            'view_count' => 3,
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.account.history'))
            ->assertOk()
            ->assertSee('Reading history')
            ->assertSee('History Visible Story');
    }

    public function test_guest_article_view_does_not_create_reader_history(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'guest-history-story',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))->assertOk();

        $this->assertDatabaseCount('reader_article_histories', 0);
    }
}
