<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Menu\Http\Controllers\MenuGroupController;
use NewsTech\Menu\Http\Controllers\MenuItemController;

Route::resource('menus', MenuGroupController::class)
    ->parameter('menus', 'menu')
    ->except('show');

Route::prefix('menus/{menu}/items')
    ->name('menus.items.')
    ->group(function (): void {
        Route::get('create', [MenuItemController::class, 'create'])->name('create');
        Route::post('/', [MenuItemController::class, 'store'])->name('store');
        Route::get('{item}/edit', [MenuItemController::class, 'edit'])->name('edit');
        Route::put('{item}', [MenuItemController::class, 'update'])->name('update');
        Route::delete('{item}', [MenuItemController::class, 'destroy'])->name('destroy');
    });
