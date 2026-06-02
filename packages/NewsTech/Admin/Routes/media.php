<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Media\Http\Controllers\MediaController;

Route::get('media', [MediaController::class, 'index'])->name('media.index');
Route::post('media', [MediaController::class, 'store'])->name('media.store');
Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
Route::post('media/picker/upload', [MediaController::class, 'pickerUpload'])->name('media.picker.upload');
