<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Tag\Http\Controllers\TagController;

Route::resource('tags', TagController::class)
    ->except('show');
