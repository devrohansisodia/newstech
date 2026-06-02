<?php

namespace NewsTech\Advertisement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Advertisement\Repositories\AdvertisementRepository;

class AdvertisementClickController
{
    public function __construct(protected AdvertisementRepository $advertisements) {}

    public function __invoke(Advertisement $advertisement): RedirectResponse
    {
        abort_unless(
            config('newstech-advertisement.enabled', true)
                && $advertisement->isRenderableAt()
                && filled($advertisement->target_url),
            404
        );

        if (config('newstech-advertisement.track_clicks', true)) {
            $this->advertisements->incrementClicks($advertisement);
        }

        return redirect()->away((string) $advertisement->target_url);
    }
}
