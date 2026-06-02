<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Advertisement\Http\Controllers\AdvertisementClickController;

Route::get('/ads/{advertisement}/click', AdvertisementClickController::class)
    ->name('advertisements.click');
