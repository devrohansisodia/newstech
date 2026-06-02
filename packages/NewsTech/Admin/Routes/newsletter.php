<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Newsletter\Http\Controllers\Admin\CampaignController;
use NewsTech\Newsletter\Http\Controllers\Admin\SubscriberController;

Route::get('newsletter/subscribers', [SubscriberController::class, 'index'])
    ->name('newsletter.index');
Route::get('newsletter/subscribers/{subscriber}', [SubscriberController::class, 'show'])
    ->name('newsletter.subscribers.show');
Route::put('newsletter/subscribers/{subscriber}', [SubscriberController::class, 'update'])
    ->name('newsletter.subscribers.update');
Route::delete('newsletter/subscribers/{subscriber}', [SubscriberController::class, 'destroy'])
    ->name('newsletter.subscribers.destroy');

Route::get('newsletter/campaigns', [CampaignController::class, 'index'])
    ->name('newsletter.campaigns.index');
Route::get('newsletter/campaigns/create', [CampaignController::class, 'create'])
    ->name('newsletter.campaigns.create');
Route::post('newsletter/campaigns', [CampaignController::class, 'store'])
    ->name('newsletter.campaigns.store');
Route::get('newsletter/campaigns/{campaign}', [CampaignController::class, 'show'])
    ->name('newsletter.campaigns.show');
Route::get('newsletter/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])
    ->name('newsletter.campaigns.edit');
Route::put('newsletter/campaigns/{campaign}', [CampaignController::class, 'update'])
    ->name('newsletter.campaigns.update');
Route::delete('newsletter/campaigns/{campaign}', [CampaignController::class, 'destroy'])
    ->name('newsletter.campaigns.destroy');
Route::post('newsletter/campaigns/{campaign}/send', [CampaignController::class, 'send'])
    ->name('newsletter.campaigns.send');
