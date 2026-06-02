<?php

namespace NewsTech\Newsletter\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Newsletter\Http\Requests\UpdateNewsletterSubscriberRequest;
use NewsTech\Newsletter\Models\NewsletterSubscriber;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;

class SubscriberController
{
    public function __construct(protected NewsletterSubscriberRepository $subscribers) {}

    public function index(): View
    {
        $subscribers = $this->subscribers->latestQuery()->get();

        $dataGrid = DataGridDefinition::make('newsletter-subscribers', 'Newsletter Subscribers')
            ->description('Manage subscriber status, review source details, and hand off the audience to newsletter campaigns without leaving the shared admin shell.')
            ->columns([
                ColumnDefinition::make('email', 'Email')->sortable(),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                    'Unsubscribed' => 'warning',
                ]),
                ColumnDefinition::make('source', 'Source'),
                ColumnDefinition::make('subscribed_at', 'Subscribed')->align('right')->sortable(),
                ColumnDefinition::make('unsubscribed_at', 'Unsubscribed')->align('right'),
            ])
            ->rows($subscribers->map(fn (NewsletterSubscriber $subscriber): array => [
                'id' => $subscriber->getKey(),
                'email' => $subscriber->email,
                'status_label' => $subscriber->statusLabel(),
                'source' => $subscriber->source ? str($subscriber->source)->headline()->toString() : 'Not set',
                'subscribed_at' => $subscriber->subscribed_at?->format('M d, Y · H:i'),
                'unsubscribed_at' => $subscriber->unsubscribed_at?->format('M d, Y · H:i') ?: 'Active',
            ])->all())
            ->rowActions([
                ActionDefinition::make('view', 'View')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.newsletter.subscribers.show', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->tone('danger')
                    ->usingMethod('DELETE')
                    ->url(fn (array $row): string => route('admin.newstech.newsletter.subscribers.destroy', $row['id'])),
            ])
            ->emptyState(
                'No newsletter subscribers yet.',
                'Subscriptions from the homepage, article pages, and footer will appear here once readers start opting in.'
            );

        return view('newstech-admin::newsletter.subscribers.index', [
            'dataGrid' => $dataGrid,
            'subscriberCount' => $subscribers->count(),
            'activeSubscriberCount' => $subscribers->where('status', 'active')->count(),
            'homepageSubscriberCount' => $subscribers->where('source', 'homepage')->count(),
        ]);
    }

    public function show(NewsletterSubscriber $subscriber): View
    {
        $latestRecipients = $subscriber->campaignRecipients()
            ->with('campaign')
            ->latest()
            ->limit(5)
            ->get();

        return view('newstech-admin::newsletter.subscribers.show', [
            'subscriber' => $subscriber,
            'latestRecipients' => $latestRecipients,
        ]);
    }

    public function update(UpdateNewsletterSubscriberRequest $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        $attributes = [
            'status' => $request->validated()['status'],
            'source' => $request->validated()['source'] ?: null,
        ];

        if ($attributes['status'] === NewsletterSubscriber::STATUS_ACTIVE) {
            $this->subscribers->activate($subscriber);
            $subscriber = $this->subscribers->update($subscriber, ['source' => $attributes['source']]);
        } elseif ($attributes['status'] === NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
            $subscriber = $this->subscribers->unsubscribe($subscriber);
            $subscriber = $this->subscribers->update($subscriber, ['source' => $attributes['source']]);
        } else {
            $subscriber = $this->subscribers->deactivate($subscriber);
            $subscriber = $this->subscribers->update($subscriber, ['source' => $attributes['source']]);
        }

        return redirect()
            ->route('admin.newstech.newsletter.subscribers.show', $subscriber)
            ->with('newsletter_status', 'Subscriber updated successfully.');
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $this->subscribers->delete($subscriber);

        return redirect()
            ->route('admin.newstech.newsletter.index')
            ->with('newsletter_status', 'Subscriber deleted successfully.');
    }
}
