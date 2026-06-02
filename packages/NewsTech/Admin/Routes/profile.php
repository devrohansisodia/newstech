<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Admin\Http\Controllers\ProfileController;

Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
