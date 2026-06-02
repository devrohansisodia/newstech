<?php

namespace NewsTech\Advertisement\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Core\Repositories\BaseRepository;

/**
 * @extends BaseRepository<Advertisement>
 */
class AdvertisementRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Advertisement::class;
    }

    /**
     * @return Builder<Advertisement>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->orderByDesc('priority')
            ->orderBy('slot_key')
            ->orderByDesc('created_at');
    }

    /**
     * @return Builder<Advertisement>
     */
    public function activeForSlotQuery(string $slotKey): Builder
    {
        return $this->query()
            ->where('slot_key', $slotKey)
            ->where('status', Advertisement::STATUS_ACTIVE)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->orderByDesc('priority')
            ->orderByDesc('created_at');
    }

    public function resolveRenderableForSlot(string $slotKey): ?Advertisement
    {
        return $this->activeForSlotQuery($slotKey)->first();
    }

    public function incrementImpressions(Advertisement $advertisement): void
    {
        $advertisement->increment('impressions_count');
    }

    public function incrementClicks(Advertisement $advertisement): void
    {
        $advertisement->increment('clicks_count');
    }
}
