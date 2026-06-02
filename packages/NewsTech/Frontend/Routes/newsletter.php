<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Newsletter\Http\Controllers\SubscriptionController;
use NewsTech\Newsletter\Http\Controllers\UnsubscribeController;

Route::post('newsletter/subscribe', [SubscriptionController::class, 'store'])
    ->name('newsletter.subscribe');

Route::get('newsletter/unsubscribe/{token}', UnsubscribeController::class)
    ->name('newsletter.unsubscribe');
