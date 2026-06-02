<?php

namespace NewsTech\Newsletter\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Newsletter\Models\NewsletterCampaign;
use NewsTech\Newsletter\Models\NewsletterCampaignRecipient;
use NewsTech\Newsletter\Models\NewsletterSubscriber;

/**
 * @extends BaseRepository<NewsletterCampaignRecipient>
 */
class NewsletterCampaignRecipientRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return NewsletterCampaignRecipient::class;
    }

    /**
     * @param  Collection<int, NewsletterSubscriber>  $subscribers
     */
    public function ensureForCampaign(NewsletterCampaign $campaign, Collection $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->query()->firstOrCreate([
                'campaign_id' => $campaign->getKey(),
                'email' => $subscriber->email,
            ], [
                'subscriber_id' => $subscriber->getKey(),
                'status' => NewsletterCampaignRecipient::STATUS_PENDING,
            ]);
        }
    }

    /**
     * @return Builder<NewsletterCampaignRecipient>
     */
    public function forCampaignQuery(NewsletterCampaign $campaign): Builder
    {
        return $this->query()
            ->where('campaign_id', $campaign->getKey())
            ->orderBy('id');
    }
}
