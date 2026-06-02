<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use NewsTech\Comment\Models\Comment;
use NewsTech\Core\Models\SystemSetting;
use NewsTech\Reader\Models\Reader;
use Tests\TestCase;

class NewsTechCommentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_guest_can_submit_comment_for_published_article(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'guest-comment-story',
        ]);

        $response = $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Aarav Reader',
                'email' => 'reader@example.com',
                'website' => 'https://example.com',
                'content' => 'This is a moderated guest comment.',
                'company' => '',
            ]);

        $response->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]));
        $response->assertSessionHas('comment_status', 'Your comment has been submitted and is awaiting moderation.');
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->getKey(),
            'name' => 'Aarav Reader',
            'email' => 'reader@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_submitted_frontend_comment_defaults_to_pending(): void
    {
        $article = Article::factory()->published()->create();

        $this->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
            'name' => 'Pending Reader',
            'email' => 'pending@example.com',
            'content' => 'Pending moderation comment.',
            'company' => '',
        ]);

        $comment = Comment::query()->first();

        $this->assertSame('pending', $comment?->status);
        $this->assertNull($comment?->approved_at);
    }

    public function test_logged_in_reader_comment_stores_reader_id_and_reader_identity(): void
    {
        $reader = Reader::factory()->create([
            'name' => 'Reader Identity',
            'email' => 'reader-identity@example.com',
        ]);
        $article = Article::factory()->published()->create([
            'slug' => 'reader-comment-story',
        ]);

        $response = $this->actingAs($reader, 'reader')
            ->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Ignored Name',
                'email' => 'ignored@example.com',
                'website' => 'https://reader.example.com',
                'content' => 'Reader-owned comment content.',
                'company' => '',
            ]);

        $response->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]));
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->getKey(),
            'reader_id' => $reader->getKey(),
            'name' => 'Reader Identity',
            'email' => 'reader-identity@example.com',
            'content' => 'Reader-owned comment content.',
        ]);
    }

    public function test_comment_submission_requires_valid_fields(): void
    {
        $article = Article::factory()->published()->create();

        $response = $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => '',
                'email' => 'not-an-email',
                'website' => 'invalid-url',
                'content' => '',
                'company' => '',
            ]);

        $response->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]));
        $response->assertInvalid(['name', 'email', 'website', 'content']);
    }

    public function test_unpublished_article_cannot_receive_comment_submission(): void
    {
        $article = Article::factory()->create([
            'slug' => 'draft-comment-story',
            'status' => 'draft',
        ]);

        $this->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
            'name' => 'Blocked Reader',
            'email' => 'blocked@example.com',
            'content' => 'Should not be stored.',
            'company' => '',
        ])->assertNotFound();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_approved_comments_appear_on_article_detail_page(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'approved-comment-story',
        ]);

        Comment::factory()->approved()->create([
            'article_id' => $article->getKey(),
            'name' => 'Visible Reader',
            'content' => 'Approved comment content.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('Visible Reader');
        $response->assertSee('Approved comment content.');
    }

    public function test_pending_and_rejected_comments_do_not_appear_publicly(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'hidden-comment-story',
        ]);

        Comment::factory()->create([
            'article_id' => $article->getKey(),
            'name' => 'Pending Reader',
            'content' => 'Pending comment.',
            'status' => 'pending',
        ]);

        Comment::factory()->rejected()->create([
            'article_id' => $article->getKey(),
            'name' => 'Rejected Reader',
            'content' => 'Rejected comment.',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertDontSee('Pending Reader');
        $response->assertDontSee('Pending comment.');
        $response->assertDontSee('Rejected Reader');
        $response->assertDontSee('Rejected comment.');
    }

    public function test_admin_can_access_comments_list(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->published()->create();
        Comment::factory()->create([
            'article_id' => $article->getKey(),
            'name' => 'Moderation Reader',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.comments.index'));

        $response->assertOk();
        $response->assertSee('Comments');
        $response->assertSee('Moderation Reader');
    }

    public function test_admin_can_approve_comment(): void
    {
        $adminUser = AdminUser::factory()->create();
        $comment = Comment::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.comments.approve', $comment))
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->getKey(),
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_comment(): void
    {
        $adminUser = AdminUser::factory()->create();
        $comment = Comment::factory()->approved()->create();

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.comments.reject', $comment))
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->getKey(),
            'status' => 'rejected',
        ]);
    }

    public function test_admin_comments_detail_shows_spam_metadata(): void
    {
        $adminUser = AdminUser::factory()->create();
        $comment = Comment::factory()->spam()->create([
            'ip_address' => '203.0.113.10',
            'user_agent' => 'NewsTech Test Agent',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.comments.show', $comment));

        $response->assertOk();
        $response->assertSee('Spam Flag');
        $response->assertSee('Blocked Word');
        $response->assertSee('203.0.113.10');
        $response->assertSee('NewsTech Test Agent');
    }

    public function test_admin_comments_views_show_reader_information_for_reader_owned_comment(): void
    {
        $adminUser = AdminUser::factory()->create();
        $reader = Reader::factory()->create([
            'name' => 'Comment Reader',
            'email' => 'comment-reader@example.com',
        ]);
        $comment = Comment::factory()->create([
            'reader_id' => $reader->getKey(),
            'name' => $reader->name,
            'email' => $reader->email,
        ]);

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.comments.index'))
            ->assertOk()
            ->assertSee('Reader · Comment Reader · comment-reader@example.com');

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.comments.show', $comment))
            ->assertOk()
            ->assertSee('Reader account linked')
            ->assertSee('Comment Source:')
            ->assertSee('Reader account');
    }

    public function test_admin_can_delete_comment(): void
    {
        $adminUser = AdminUser::factory()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->delete(route('admin.newstech.comments.destroy', $comment))
            ->assertRedirect(route('admin.newstech.comments.index'));

        $this->assertSoftDeleted('comments', [
            'id' => $comment->getKey(),
        ]);
    }

    public function test_comments_menu_link_is_visible_in_admin_sidebar(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.newstech.dashboard'));

        $response->assertOk();
        $response->assertSee('Comments');
        $response->assertSee(route('admin.newstech.comments.index'), false);
    }

    public function test_comment_submission_form_renders_on_published_article_detail(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'comment-form-story',
        ]);

        $response = $this->get(route('newstech.articles.show', ['slug' => $article->slug]));

        $response->assertOk();
        $response->assertSee('Leave a comment');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="website"', false);
        $response->assertSee('name="content"', false);
        $response->assertSee('name="company"', false);
    }

    public function test_comments_enabled_false_hides_form_and_blocks_submission(): void
    {
        $this->setCommentSetting('comments.enabled', '0');

        $article = Article::factory()->published()->create([
            'slug' => 'comments-closed-story',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Comments are closed.')
            ->assertDontSee('name="name"', false)
            ->assertDontSee('Submit Comment');

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Closed Reader',
                'email' => 'closed@example.com',
                'content' => 'Trying to post while comments are disabled.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_comments_enabled_false_hides_guest_form_and_blocks_submission(): void
    {
        $this->setCommentSetting('comments.guest_comments_enabled', '0');

        $article = Article::factory()->published()->create([
            'slug' => 'guest-comments-disabled-story',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Guest comments are currently disabled.')
            ->assertDontSee('name="name"', false)
            ->assertDontSee('Submit Comment');

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Guest Reader',
                'email' => 'guest@example.com',
                'content' => 'Trying to post while guest comments are disabled.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);
    }

    public function test_guest_comments_disabled_still_allows_logged_in_reader_comments(): void
    {
        $this->setCommentSetting('comments.guest_comments_enabled', '0');

        $reader = Reader::factory()->create([
            'name' => 'Logged Reader',
            'email' => 'logged-reader@example.com',
        ]);
        $article = Article::factory()->published()->create([
            'slug' => 'reader-comments-allowed-story',
        ]);

        $this->actingAs($reader, 'reader')
            ->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Submitted with your reader account name.')
            ->assertSee('Submitted with your reader account email.')
            ->assertSee('Submit Comment');

        $response = $this->actingAs($reader, 'reader')
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Ignored Name',
                'email' => 'ignored@example.com',
                'content' => 'Reader comment while guest comments are disabled.',
                'company' => '',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'reader_id' => $reader->getKey(),
            'email' => 'logged-reader@example.com',
            'content' => 'Reader comment while guest comments are disabled.',
        ]);
    }

    public function test_guest_can_submit_reply_to_approved_comment(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'reply-story']);
        $parent = Comment::factory()->approved()->create([
            'article_id' => $article->getKey(),
            'parent_id' => null,
        ]);

        $response = $this->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
            'name' => 'Reply Guest',
            'email' => 'reply@example.com',
            'parent_id' => $parent->getKey(),
            'content' => 'This is a nested reply.',
            'company' => '',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'email' => 'reply@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_approved_reply_shows_nested_under_parent_comment(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'nested-approval-story']);
        $parent = Comment::factory()->approved()->create([
            'article_id' => $article->getKey(),
            'name' => 'Parent Reader',
            'content' => 'Parent comment.',
        ]);
        Comment::factory()->approved()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'name' => 'Reply Reader',
            'content' => 'Approved reply content.',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertSee('Parent Reader')
            ->assertSee('Approved reply content.')
            ->assertSee('Submit Reply');
    }

    public function test_pending_rejected_and_spam_replies_do_not_render_publicly(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'hidden-replies-story']);
        $parent = Comment::factory()->approved()->create([
            'article_id' => $article->getKey(),
            'content' => 'Parent comment.',
        ]);

        Comment::factory()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'name' => 'Pending Reply',
            'content' => 'Pending reply.',
            'status' => 'pending',
        ]);
        Comment::factory()->rejected()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'name' => 'Rejected Reply',
            'content' => 'Rejected reply.',
        ]);
        Comment::factory()->spam()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'name' => 'Spam Reply',
            'content' => 'Spam reply.',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertDontSee('Pending Reply')
            ->assertDontSee('Rejected Reply')
            ->assertDontSee('Spam Reply');
    }

    public function test_cannot_reply_to_comment_from_another_article(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'article-a']);
        $otherArticle = Article::factory()->published()->create(['slug' => 'article-b']);
        $parent = Comment::factory()->approved()->create([
            'article_id' => $otherArticle->getKey(),
        ]);

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Reply',
                'email' => 'blocked-reply@example.com',
                'parent_id' => $parent->getKey(),
                'content' => 'Invalid cross-article reply.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);
    }

    public function test_cannot_reply_to_rejected_parent_comment(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'rejected-parent-story']);
        $parent = Comment::factory()->rejected()->create([
            'article_id' => $article->getKey(),
        ]);

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Reply',
                'email' => 'blocked@example.com',
                'parent_id' => $parent->getKey(),
                'content' => 'Blocked reply to rejected parent.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);
    }

    public function test_admin_can_approve_reject_and_delete_replies(): void
    {
        $adminUser = AdminUser::factory()->create();
        $article = Article::factory()->published()->create();
        $parent = Comment::factory()->approved()->create(['article_id' => $article->getKey()]);
        $reply = Comment::factory()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parent->getKey(),
            'status' => 'pending',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.comments.approve', $reply))
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $reply->getKey(),
            'status' => 'approved',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.comments.reject', $reply))
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $reply->getKey(),
            'status' => 'rejected',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->delete(route('admin.newstech.comments.destroy', $reply))
            ->assertRedirect(route('admin.newstech.comments.index'));

        $this->assertSoftDeleted('comments', [
            'id' => $reply->getKey(),
        ]);
    }

    public function test_website_field_enabled_false_hides_website_field_and_ignores_input(): void
    {
        $this->setCommentSetting('comments.website_field_enabled', '0');

        $article = Article::factory()->published()->create([
            'slug' => 'website-field-disabled-story',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertDontSee('name="website"', false);

        $this->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
            'name' => 'Hidden Website Reader',
            'email' => 'hidden-website@example.com',
            'website' => 'https://should-be-ignored.test',
            'content' => 'Comment without a visible website field.',
            'company' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'email' => 'hidden-website@example.com',
            'website' => null,
        ]);
    }

    public function test_require_moderation_false_creates_approved_comment(): void
    {
        $this->setCommentSetting('comments.require_moderation', '0');

        $article = Article::factory()->published()->create();

        $response = $this->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
            'name' => 'Instant Reader',
            'email' => 'instant@example.com',
            'content' => 'Clean comment that should publish immediately.',
            'company' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('comment_status', 'Your comment has been published successfully.');
        $this->assertDatabaseHas('comments', [
            'email' => 'instant@example.com',
            'status' => 'approved',
        ]);
    }

    public function test_comment_length_rules_follow_settings(): void
    {
        $this->setCommentSetting('comments.min_comment_length', '10');
        $this->setCommentSetting('comments.max_comment_length', '20');

        $article = Article::factory()->published()->create();

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Short Reader',
                'email' => 'short@example.com',
                'content' => 'short',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Long Reader',
                'email' => 'long@example.com',
                'content' => str_repeat('a', 25),
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);
    }

    public function test_too_many_links_marks_comment_as_spam(): void
    {
        $article = Article::factory()->published()->create();

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Link Reader',
                'email' => 'links@example.com',
                'content' => 'https://one.test https://two.test https://three.test',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseHas('comments', [
            'email' => 'links@example.com',
            'status' => 'pending',
            'is_spam' => true,
            'spam_reason' => 'too_many_links',
        ]);
    }

    public function test_blocked_word_rule_marks_comment_as_spam(): void
    {
        $this->setCommentSetting('comments.blocked_words', "crypto scam\ncasino");

        $article = Article::factory()->published()->create();

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Word Reader',
                'email' => 'blocked-word@example.com',
                'content' => 'This looks like a crypto scam pitch.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseHas('comments', [
            'email' => 'blocked-word@example.com',
            'is_spam' => true,
            'spam_reason' => 'blocked_word',
        ]);
    }

    public function test_blocked_email_and_domain_rule_marks_comment_as_spam(): void
    {
        $this->setCommentSetting('comments.blocked_emails', "blocked@example.com\nspamdomain.test");

        $article = Article::factory()->published()->create();

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Email Reader',
                'email' => 'blocked@example.com',
                'content' => 'Blocked exact email.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Domain Reader',
                'email' => 'reader@spamdomain.test',
                'content' => 'Blocked domain email.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseHas('comments', [
            'email' => 'blocked@example.com',
            'spam_reason' => 'blocked_email',
        ]);
        $this->assertDatabaseHas('comments', [
            'email' => 'reader@spamdomain.test',
            'spam_reason' => 'blocked_email',
        ]);
    }

    public function test_blocked_ip_rule_marks_comment_as_spam(): void
    {
        $this->setCommentSetting('comments.blocked_ips', "203.0.113.10\n198.51.100.5");

        $article = Article::factory()->published()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Blocked Ip Reader',
                'email' => 'blocked-ip@example.com',
                'content' => 'Blocked IP comment.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseHas('comments', [
            'email' => 'blocked-ip@example.com',
            'is_spam' => true,
            'spam_reason' => 'blocked_ip',
        ]);
    }

    public function test_comment_throttle_blocks_repeated_submission_from_same_identity(): void
    {
        $article = Article::factory()->published()->create();

        $payload = [
            'name' => 'Throttle Reader',
            'email' => 'throttle@example.com',
            'content' => 'This comment should go through once.',
            'company' => '',
        ];

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.8'])
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), $payload)
            ->assertRedirect();

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.8'])
            ->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), $payload)
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);
    }

    public function test_auto_reject_spam_true_rejects_spam_comment(): void
    {
        $this->setCommentSetting('comments.auto_reject_spam', '1');
        $this->setCommentSetting('comments.blocked_words', 'casino');

        $article = Article::factory()->published()->create();

        $this->from(route('newstech.articles.show', ['slug' => $article->slug]))
            ->post(route('newstech.articles.comments.store', ['slug' => $article->slug]), [
                'name' => 'Rejected Spam Reader',
                'email' => 'rejected-spam@example.com',
                'content' => 'This casino comment should be rejected.',
                'company' => '',
            ])
            ->assertRedirect(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertInvalid(['content']);

        $this->assertDatabaseHas('comments', [
            'email' => 'rejected-spam@example.com',
            'status' => 'rejected',
            'is_spam' => true,
            'spam_reason' => 'blocked_word',
        ]);
    }

    public function test_spam_comments_do_not_render_publicly(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'spam-hidden-story',
        ]);

        Comment::factory()->spam()->create([
            'article_id' => $article->getKey(),
            'name' => 'Spam Reader',
            'content' => 'Spam comment.',
        ]);

        $this->get(route('newstech.articles.show', ['slug' => $article->slug]))
            ->assertOk()
            ->assertDontSee('Spam Reader')
            ->assertDontSee('Spam comment.');
    }

    protected function setCommentSetting(string $key, string $value): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
