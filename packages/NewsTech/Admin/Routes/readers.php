<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Reader\Http\Controllers\Admin\ReaderController;

Route::resource('readers', ReaderController::class)
    ->except('show');
