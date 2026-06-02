<?php

namespace NewsTech\Newsletter\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Newsletter\Models\NewsletterCampaign;

/**
 * @extends BaseRepository<NewsletterCampaign>
 */
class NewsletterCampaignRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return NewsletterCampaign::class;
    }

    /**
     * @return Builder<NewsletterCampaign>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->latest('created_at');
    }
}
