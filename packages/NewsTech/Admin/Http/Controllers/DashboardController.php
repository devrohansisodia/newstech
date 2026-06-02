<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;

class DashboardController
{
    public function __construct(protected ArticleRepository $articles) {}

    public function __invoke(): View
    {
        return view('newstech-admin::dashboard', [
            'topViewedArticles' => $this->articles->topViewedArticles(),
        ]);
    }
}
