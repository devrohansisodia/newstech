<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Author\Http\Controllers\AuthorController;

Route::resource('authors', AuthorController::class)
    ->except('show');
