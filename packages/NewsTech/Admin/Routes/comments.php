<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Comment\Http\Controllers\Admin\CommentController;

Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
Route::get('comments/{comment}', [CommentController::class, 'show'])->name('comments.show');
Route::put('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
Route::put('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
