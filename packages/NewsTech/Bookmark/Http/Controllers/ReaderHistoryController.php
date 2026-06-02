<?php

namespace NewsTech\Bookmark\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Bookmark\Repositories\ReaderArticleHistoryRepository;

class ReaderHistoryController extends Controller
{
    public function __construct(protected ReaderArticleHistoryRepository $history) {}

    public function index(): View
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();

        return view('newstech-frontend::account.history', [
            'reader' => $reader,
            'history' => $reader ? $this->history->paginateForReader($reader) : null,
        ]);
    }
}
