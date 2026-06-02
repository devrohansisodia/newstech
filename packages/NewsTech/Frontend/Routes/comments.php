<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Comment\Http\Controllers\CommentController;

Route::post('/news/{slug}/comments', [CommentController::class, 'store'])
    ->name('articles.comments.store');
