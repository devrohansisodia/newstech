<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Page\Http\Controllers\PageController;

Route::resource('pages', PageController::class)
    ->except('show');
