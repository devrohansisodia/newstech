<?php

namespace NewsTech\Advertisement\Support;

use Illuminate\Support\HtmlString;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Advertisement\Repositories\AdvertisementRepository;
use NewsTech\Core\Support\MediaManager;

class AdvertisementRenderer
{
    public function __construct(
        protected AdvertisementRepository $advertisements,
        protected AdvertisementSlotManager $slots,
        protected MediaManager $mediaManager,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function renderSlot(string $slotKey, bool $compact = false, array $payload = []): HtmlString
    {
        $slot = $this->slots->find($slotKey);

        if ($slot === null || ! ($slot['enabled'] ?? true) || ! config('newstech-advertisement.enabled', true)) {
            return new HtmlString('');
        }

        $advertisement = $this->advertisements->resolveRenderableForSlot($slotKey);

        if ($advertisement) {
            if (config('newstech-advertisement.track_impressions', true)) {
                $this->advertisements->incrementImpressions($advertisement);
                $advertisement->refresh();
            }

            return new HtmlString(
                view('newstech-advertisement::managed', [
                    'advertisement' => $advertisement,
                    'slot' => $slot,
                    'compact' => $compact,
                    'resolvedImageUrl' => $this->resolvedImageUrl($advertisement),
                    'clickUrl' => $this->clickUrl($advertisement),
                ])->render()
            );
        }

        if (! config('newstech-advertisement.placeholders_enabled', false)) {
            return new HtmlString('');
        }

        return new HtmlString(
            view('newstech-advertisement::placeholder', [
                'key' => $slotKey,
                'compact' => $compact,
            ])->render()
        );
    }

    public function resolvedImageUrl(Advertisement $advertisement): ?string
    {
        if (! is_string($advertisement->image_path) || $advertisement->image_path === '') {
            return null;
        }

        return $this->mediaManager->url($advertisement->image_path);
    }

    public function clickUrl(Advertisement $advertisement): ?string
    {
        if (! is_string($advertisement->target_url) || $advertisement->target_url === '') {
            return null;
        }

        if (! config('newstech-advertisement.track_clicks', true)) {
            return $advertisement->target_url;
        }

        return route('newstech.advertisements.click', $advertisement);
    }
}
