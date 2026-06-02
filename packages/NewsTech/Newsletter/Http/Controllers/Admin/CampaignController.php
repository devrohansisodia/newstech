<?php

namespace NewsTech\Newsletter\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Newsletter\Http\Requests\StoreNewsletterCampaignRequest;
use NewsTech\Newsletter\Http\Requests\UpdateNewsletterCampaignRequest;
use NewsTech\Newsletter\Models\NewsletterCampaign;
use NewsTech\Newsletter\Models\NewsletterCampaignRecipient;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRepository;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;
use NewsTech\Newsletter\Support\NewsletterCampaignService;

class CampaignController
{
    public function __construct(
        protected NewsletterCampaignRepository $campaigns,
        protected NewsletterCampaignRecipientRepository $recipients,
        protected NewsletterSubscriberRepository $subscribers,
        protected NewsletterCampaignService $campaignService,
    ) {}

    public function index(): View
    {
        $campaigns = $this->campaigns->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('newsletter-campaigns', 'Newsletter Campaigns')
            ->description('Create, review, and send newsletter campaigns to active subscribers using Laravel mail.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('subject', 'Subject'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Draft' => 'neutral',
                    'Scheduled' => 'warning',
                    'Sending' => 'warning',
                    'Sent' => 'success',
                    'Cancelled' => 'danger',
                ]),
                ColumnDefinition::make('recipients_count', 'Recipients')->align('right'),
                ColumnDefinition::make('delivered_count', 'Delivered')->align('right'),
                ColumnDefinition::make('failed_count', 'Failed')->align('right'),
            ])
            ->rows($campaigns->map(fn (NewsletterCampaign $campaign): array => [
                'id' => $campaign->getKey(),
                'name' => $campaign->name,
                'subject' => $campaign->subject,
                'status_label' => $campaign->statusLabel(),
                'recipients_count' => (string) $campaign->recipients_count,
                'delivered_count' => (string) $campaign->delivered_count,
                'failed_count' => (string) $campaign->failed_count,
            ])->all())
            ->rowActions([
                ActionDefinition::make('view', 'View')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.newsletter.campaigns.show', $row['id'])),
                ActionDefinition::make('edit', 'Edit')
                    ->url(fn (array $row): string => route('admin.newstech.newsletter.campaigns.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->tone('danger')
                    ->usingMethod('DELETE')
                    ->url(fn (array $row): string => route('admin.newstech.newsletter.campaigns.destroy', $row['id'])),
            ])
            ->emptyState(
                'No newsletter campaigns yet.',
                'Create a first campaign to send the stored newsletter audience a reusable editorial email.'
            );

        return view('newstech-admin::newsletter.campaigns.index', [
            'dataGrid' => $dataGrid,
            'campaignCount' => $campaigns->count(),
            'sentCampaignCount' => $campaigns->where('status', NewsletterCampaign::STATUS_SENT)->count(),
            'activeSubscriberCount' => $this->subscribers->activeQuery()->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::newsletter.campaigns.create', [
            'campaign' => new NewsletterCampaign([
                'status' => NewsletterCampaign::STATUS_DRAFT,
            ]),
        ]);
    }

    public function store(StoreNewsletterCampaignRequest $request): RedirectResponse
    {
        /** @var NewsletterCampaign $campaign */
        $campaign = $this->campaigns->create([
            ...$request->validated(),
            'created_by' => auth('admin')->id(),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('admin.newstech.newsletter.campaigns.show', $campaign)
            ->with('newsletter_status', 'Campaign created successfully.');
    }

    public function show(NewsletterCampaign $campaign): View
    {
        $campaign->load('recipients.subscriber');

        $recipientGrid = DataGridDefinition::make('newsletter-campaign-recipients', 'Campaign Recipients')
            ->description('Delivery foundation rows created for this newsletter campaign.')
            ->columns([
                ColumnDefinition::make('email', 'Email'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Pending' => 'neutral',
                    'Sent' => 'success',
                    'Failed' => 'danger',
                    'Skipped' => 'warning',
                ]),
                ColumnDefinition::make('sent_at', 'Sent')->align('right'),
                ColumnDefinition::make('failure_reason', 'Failure Reason'),
            ])
            ->rows($campaign->recipients->map(fn (NewsletterCampaignRecipient $recipient): array => [
                'id' => $recipient->getKey(),
                'email' => $recipient->email,
                'status_label' => $recipient->statusLabel(),
                'sent_at' => $recipient->sent_at?->format('M d, Y · H:i') ?: 'Not sent',
                'failure_reason' => $recipient->failure_reason ?: 'None',
            ])->all())
            ->emptyState('No recipients generated yet.', 'Send the campaign to create recipient tracking rows.');

        return view('newstech-admin::newsletter.campaigns.show', [
            'campaign' => $campaign,
            'recipientGrid' => $recipientGrid,
            'activeSubscriberCount' => $this->subscribers->activeQuery()->count(),
        ]);
    }

    public function edit(NewsletterCampaign $campaign): View
    {
        abort_unless($campaign->canEdit(), 404);

        return view('newstech-admin::newsletter.campaigns.edit', [
            'campaign' => $campaign,
        ]);
    }

    public function update(UpdateNewsletterCampaignRequest $request, NewsletterCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->canEdit(), 404);

        $this->campaigns->update($campaign, [
            ...$request->validated(),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('admin.newstech.newsletter.campaigns.show', $campaign)
            ->with('newsletter_status', 'Campaign updated successfully.');
    }

    public function destroy(NewsletterCampaign $campaign): RedirectResponse
    {
        $this->campaigns->delete($campaign);

        return redirect()
            ->route('admin.newstech.newsletter.campaigns.index')
            ->with('newsletter_status', 'Campaign deleted successfully.');
    }

    public function send(NewsletterCampaign $campaign): RedirectResponse
    {
        if (! $campaign->canSend()) {
            return redirect()
                ->route('admin.newstech.newsletter.campaigns.show', $campaign)
                ->with('newsletter_status', 'This campaign has already been sent or is not eligible to send.');
        }

        $campaign = $this->campaignService->sendCampaign($campaign);

        return redirect()
            ->route('admin.newstech.newsletter.campaigns.show', $campaign)
            ->with('newsletter_status', 'Campaign send completed.');
    }
}
