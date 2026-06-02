<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Category\Http\Controllers\CategoryController;

Route::resource('categories', CategoryController::class)
    ->except('show');
