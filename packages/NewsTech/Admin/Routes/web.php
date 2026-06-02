<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Admin\Http\Controllers\DashboardController;
use NewsTech\Admin\Http\Controllers\NewPasswordController;
use NewsTech\Admin\Http\Controllers\PasswordResetLinkController;
use NewsTech\Admin\Http\Controllers\SessionController;
use NewsTech\Admin\Http\Controllers\SettingsController;

Route::middleware(config('newstech-admin.route.middleware'))
    ->name(config('newstech-admin.route.name'))
    ->prefix(config('newstech-admin.route.prefix'))
    ->group(function (): void {
        Route::middleware('admin.guest')->group(function (): void {
            Route::get('login', [SessionController::class, 'create'])->name('login');
            Route::post('login', [SessionController::class, 'store'])->name('login.store');
            Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
            Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
        });

        Route::middleware('admin.auth')->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');

            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('settings/{group}', [SettingsController::class, 'show'])->name('settings.show');
            Route::put('settings/{group?}', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('logout', [SessionController::class, 'destroy'])->name('logout');
        });
    });
