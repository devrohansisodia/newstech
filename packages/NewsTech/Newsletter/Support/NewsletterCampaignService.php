<?php

namespace NewsTech\Newsletter\Support;

use Illuminate\Support\Facades\Mail;
use NewsTech\Newsletter\Mail\NewsletterCampaignMail;
use NewsTech\Newsletter\Models\NewsletterCampaign;
use NewsTech\Newsletter\Models\NewsletterCampaignRecipient;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRepository;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;
use Throwable;

class NewsletterCampaignService
{
    public function __construct(
        protected NewsletterCampaignRepository $campaigns,
        protected NewsletterCampaignRecipientRepository $recipients,
        protected NewsletterSubscriberRepository $subscribers,
    ) {}

    public function sendCampaign(NewsletterCampaign $campaign): NewsletterCampaign
    {
        if (! $campaign->canSend()) {
            return $campaign;
        }

        $subscribers = $this->subscribers->activeQuery()->get();

        $this->recipients->ensureForCampaign($campaign, $subscribers);

        $campaign = $this->campaigns->update($campaign, [
            'status' => NewsletterCampaign::STATUS_SENDING,
            'recipients_count' => $campaign->recipients()->count(),
            'updated_by' => auth('admin')->id(),
        ]);

        $campaign->load('recipients.subscriber');

        foreach ($campaign->recipients as $recipient) {
            if ($recipient->status !== NewsletterCampaignRecipient::STATUS_PENDING) {
                continue;
            }

            $subscriber = $recipient->subscriber;

            if (! $subscriber || ! $subscriber->isActive()) {
                $recipient->fill([
                    'status' => NewsletterCampaignRecipient::STATUS_SKIPPED,
                    'failure_reason' => 'Subscriber inactive or unsubscribed.',
                ])->save();

                continue;
            }

            try {
                Mail::to($recipient->email)->send(new NewsletterCampaignMail($campaign, $subscriber));

                $recipient->fill([
                    'status' => NewsletterCampaignRecipient::STATUS_SENT,
                    'sent_at' => now(),
                    'failed_at' => null,
                    'failure_reason' => null,
                ])->save();
            } catch (Throwable $throwable) {
                $recipient->fill([
                    'status' => NewsletterCampaignRecipient::STATUS_FAILED,
                    'failed_at' => now(),
                    'failure_reason' => str($throwable->getMessage())->limit(500)->toString(),
                ])->save();
            }
        }

        return $this->refreshCampaignMetrics($campaign);
    }

    public function refreshCampaignMetrics(NewsletterCampaign $campaign): NewsletterCampaign
    {
        $recipientQuery = $this->recipients->forCampaignQuery($campaign);

        return $this->campaigns->update($campaign, [
            'status' => NewsletterCampaign::STATUS_SENT,
            'sent_at' => $campaign->sent_at ?: now(),
            'recipients_count' => (clone $recipientQuery)->count(),
            'delivered_count' => (clone $recipientQuery)->where('status', NewsletterCampaignRecipient::STATUS_SENT)->count(),
            'failed_count' => (clone $recipientQuery)->where('status', NewsletterCampaignRecipient::STATUS_FAILED)->count(),
            'updated_by' => auth('admin')->id(),
        ]);
    }
}
