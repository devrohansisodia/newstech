<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Article\Http\Controllers\ArticleController;

Route::resource('articles', ArticleController::class)
    ->except('show');
