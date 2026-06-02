<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Reader\Http\Controllers\NewReaderPasswordController;
use NewsTech\Reader\Http\Controllers\PasswordResetLinkController;
use NewsTech\Reader\Http\Controllers\ReaderAccountController;
use NewsTech\Reader\Http\Controllers\ReaderEmailVerificationNotificationController;
use NewsTech\Reader\Http\Controllers\ReaderEmailVerificationPromptController;
use NewsTech\Reader\Http\Controllers\ReaderSessionController;
use NewsTech\Reader\Http\Controllers\RegisteredReaderController;
use NewsTech\Reader\Http\Controllers\VerifyReaderEmailController;

Route::middleware('reader.guest')->group(function (): void {
    Route::get('/login', [ReaderSessionController::class, 'create'])->name('readers.login');
    Route::post('/login', [ReaderSessionController::class, 'store'])->name('readers.login.store');
    Route::get('/register', [RegisteredReaderController::class, 'create'])->name('readers.register');
    Route::post('/register', [RegisteredReaderController::class, 'store'])->name('readers.register.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('readers.password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('readers.password.email');
    Route::get('/reset-password/{token}', [NewReaderPasswordController::class, 'create'])->name('readers.password.reset');
    Route::post('/reset-password', [NewReaderPasswordController::class, 'store'])->name('readers.password.store');
});

Route::middleware('reader.auth')->group(function (): void {
    Route::post('/logout', [ReaderSessionController::class, 'destroy'])->name('readers.logout');
    Route::get('/email/verify', ReaderEmailVerificationPromptController::class)->name('readers.verification.notice');
    Route::post('/email/verification-notification', [ReaderEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('readers.verification.send');
    Route::get('/email/verify/{id}/{hash}', VerifyReaderEmailController::class)
        ->middleware('signed')
        ->name('readers.verification.verify');
    Route::get('/account', [ReaderAccountController::class, 'dashboard'])->name('account.dashboard');
    Route::get('/account/profile', [ReaderAccountController::class, 'editProfile'])->name('account.profile');
    Route::post('/account/profile', [ReaderAccountController::class, 'updateProfile'])->name('account.profile.update');
});
