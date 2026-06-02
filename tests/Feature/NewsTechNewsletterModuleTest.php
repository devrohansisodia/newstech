<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use NewsTech\Newsletter\Mail\NewsletterCampaignMail;
use NewsTech\Newsletter\Models\NewsletterCampaign;
use NewsTech\Newsletter\Models\NewsletterSubscriber;
use Tests\TestCase;

class NewsTechNewsletterModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_form_renders_on_expected_frontend_pages(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Subscriber Growth Story',
            'slug' => 'subscriber-growth-story',
        ]);

        $homeResponse = $this->get(route('newstech.home'));
        $articleResponse = $this->get(route('newstech.articles.show', $article->slug));

        $homeResponse->assertOk();
        $homeResponse->assertSee('Start the NewsTech newsletter list');
        $homeResponse->assertSee(route('newstech.newsletter.subscribe'));

        $articleResponse->assertOk();
        $articleResponse->assertSee('Follow the newsroom by email');
        $articleResponse->assertSee(route('newstech.newsletter.subscribe'));
    }

    public function test_subscriber_can_subscribe_from_frontend(): void
    {
        $response = $this->from(route('newstech.home'))
            ->post(route('newstech.newsletter.subscribe'), [
                'email' => 'reader@example.com',
                'source' => 'homepage',
            ]);

        $response->assertRedirect(route('newstech.home'));
        $response->assertSessionHas('newsletter_status');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => 'homepage',
        ]);
    }

    public function test_duplicate_active_subscriber_is_handled_cleanly(): void
    {
        NewsletterSubscriber::factory()->create([
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => 'footer',
            'subscribed_at' => now()->subDay(),
        ]);

        $response = $this->from(route('newstech.home'))
            ->post(route('newstech.newsletter.subscribe'), [
                'email' => 'reader@example.com',
                'source' => 'homepage',
            ]);

        $response->assertRedirect(route('newstech.home'));
        $response->assertSessionHas('newsletter_status', 'This email is already subscribed to the NewsTech newsletter.');
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_unsubscribed_subscriber_can_resubscribe_if_allowed(): void
    {
        NewsletterSubscriber::factory()->unsubscribed()->create([
            'email' => 'returning@example.com',
        ]);

        $response = $this->from(route('newstech.home'))
            ->post(route('newstech.newsletter.subscribe'), [
                'email' => 'returning@example.com',
                'source' => 'footer',
            ]);

        $response->assertRedirect(route('newstech.home'));

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'returning@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => 'footer',
            'unsubscribed_at' => null,
        ]);
    }

    public function test_guest_and_admin_behavior_for_admin_subscriber_list(): void
    {
        $this->get(route('admin.newstech.newsletter.index'))
            ->assertRedirect(route('admin.newstech.login'));

        $subscriber = NewsletterSubscriber::factory()->create([
            'email' => 'briefing@example.com',
            'source' => 'homepage',
        ]);

        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.newsletter.index'));

        $response->assertOk();
        $response->assertSee('Newsletter Subscribers');
        $response->assertSee($subscriber->email);
        $response->assertSee('Campaigns');
    }

    public function test_admin_can_update_and_delete_a_subscriber(): void
    {
        $adminUser = AdminUser::factory()->create();
        $subscriber = NewsletterSubscriber::factory()->create([
            'email' => 'status-change@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.newsletter.subscribers.update', $subscriber), [
                'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
                'source' => 'manual',
            ])
            ->assertRedirect(route('admin.newstech.newsletter.subscribers.show', $subscriber));

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->getKey(),
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'source' => 'manual',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->delete(route('admin.newstech.newsletter.subscribers.destroy', $subscriber))
            ->assertRedirect(route('admin.newstech.newsletter.index'));

        $this->assertDatabaseMissing('newsletter_subscribers', [
            'id' => $subscriber->getKey(),
        ]);
    }

    public function test_admin_can_create_edit_and_send_campaign_to_active_subscribers(): void
    {
        Mail::fake();

        $adminUser = AdminUser::factory()->create();
        $activeSubscriber = NewsletterSubscriber::factory()->create([
            'email' => 'active@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'unsubscribed_at' => null,
        ]);
        NewsletterSubscriber::factory()->unsubscribed()->create([
            'email' => 'unsubscribed@example.com',
        ]);
        NewsletterSubscriber::factory()->inactive()->create([
            'email' => 'inactive@example.com',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.newsletter.campaigns.store'), [
                'name' => 'Morning Briefing',
                'subject' => 'Daily editorial briefing',
                'preheader' => 'Top stories in one email',
                'content' => '<p>Morning campaign content.</p>',
                'status' => NewsletterCampaign::STATUS_DRAFT,
                'scheduled_at' => null,
            ])
            ->assertRedirect();

        $campaign = NewsletterCampaign::query()->firstOrFail();

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.newsletter.campaigns.update', $campaign), [
                'name' => 'Morning Briefing Updated',
                'subject' => 'Updated daily briefing',
                'preheader' => 'Still the top stories',
                'content' => '<p>Updated campaign content.</p>',
                'status' => NewsletterCampaign::STATUS_DRAFT,
                'scheduled_at' => null,
            ])
            ->assertRedirect(route('admin.newstech.newsletter.campaigns.show', $campaign));

        $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.newsletter.campaigns.send', $campaign))
            ->assertRedirect(route('admin.newstech.newsletter.campaigns.show', $campaign));

        $campaign->refresh();

        $this->assertSame(1, $campaign->recipients_count);
        $this->assertSame(1, $campaign->delivered_count);
        $this->assertSame(0, $campaign->failed_count);
        $this->assertSame(NewsletterCampaign::STATUS_SENT, $campaign->status);

        $this->assertDatabaseHas('newsletter_campaign_recipients', [
            'campaign_id' => $campaign->getKey(),
            'subscriber_id' => $activeSubscriber->getKey(),
            'email' => 'active@example.com',
            'status' => 'sent',
        ]);

        Mail::assertSent(NewsletterCampaignMail::class, function (NewsletterCampaignMail $mail) use ($activeSubscriber): bool {
            return $mail->hasTo($activeSubscriber->email)
                && $mail->hasSubject('Updated daily briefing');
        });

        $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.newsletter.campaigns.send', $campaign))
            ->assertRedirect(route('admin.newstech.newsletter.campaigns.show', $campaign));

        Mail::assertSentTimes(NewsletterCampaignMail::class, 1);
    }

    public function test_mailable_contains_unsubscribe_link(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create([
            'email' => 'mailcheck@example.com',
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Mail Check',
            'subject' => 'Mail Subject',
            'content' => '<p>Mail body</p>',
            'status' => NewsletterCampaign::STATUS_DRAFT,
        ]);

        $mailable = new NewsletterCampaignMail($campaign, $subscriber);

        $mailable->assertHasSubject('Mail Subject');
        $mailable->assertSeeInHtml(route('newstech.newsletter.unsubscribe', $subscriber->unsubscribe_token));
    }

    public function test_unsubscribe_token_marks_subscriber_unsubscribed_and_invalid_token_fails_safely(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create([
            'email' => 'optout@example.com',
        ]);

        $this->get(route('newstech.newsletter.unsubscribe', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee('You have been unsubscribed');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->getKey(),
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);

        $this->get(route('newstech.newsletter.unsubscribe', 'invalid-token'))
            ->assertNotFound();
    }
}
