<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Bookmark\Http\Controllers\BookmarkFolderController;
use NewsTech\Bookmark\Http\Controllers\BookmarkController;
use NewsTech\Bookmark\Http\Controllers\ReaderHistoryController;

Route::middleware('reader.auth')->group(function (): void {
    Route::post('/news/{slug}/bookmark', [BookmarkController::class, 'store'])->name('articles.bookmarks.store');
    Route::delete('/news/{slug}/bookmark', [BookmarkController::class, 'destroy'])->name('articles.bookmarks.destroy');
    Route::put('/bookmarks/{bookmark}/folder', [BookmarkController::class, 'updateFolder'])->name('bookmarks.folder.update');
    Route::post('/account/bookmark-folders', [BookmarkFolderController::class, 'store'])->name('account.bookmark-folders.store');
    Route::get('/account/bookmarks', [BookmarkController::class, 'index'])->name('account.bookmarks');
    Route::get('/account/history', [ReaderHistoryController::class, 'index'])->name('account.history');
});
