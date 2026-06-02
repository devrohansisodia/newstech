<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Advertisement\Http\Controllers\Admin\AdvertisementController;

Route::get('advertisements', [AdvertisementController::class, 'index'])->name('advertisements.index');
Route::get('advertisements/create', [AdvertisementController::class, 'create'])->name('advertisements.create');
Route::post('advertisements', [AdvertisementController::class, 'store'])->name('advertisements.store');
Route::get('advertisements/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('advertisements.edit');
Route::put('advertisements/{advertisement}', [AdvertisementController::class, 'update'])->name('advertisements.update');
Route::delete('advertisements/{advertisement}', [AdvertisementController::class, 'destroy'])->name('advertisements.destroy');
