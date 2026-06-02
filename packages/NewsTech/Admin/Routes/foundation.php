<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Admin\Http\Controllers\DataGridDemoController;
use NewsTech\Admin\Http\Controllers\FormDemoController;
use NewsTech\Admin\Http\Controllers\MediaDemoController;

Route::prefix('foundation')
    ->name('foundation.')
    ->group(function (): void {
        Route::get('datagrid', [DataGridDemoController::class, 'index'])->name('datagrid.index');
        Route::get('datagrid-demo', [DataGridDemoController::class, 'index'])->name('datagrid-demo.index');

        Route::get('form', [FormDemoController::class, 'index'])->name('form.index');
        Route::get('form-demo', [FormDemoController::class, 'index'])->name('form-demo.index');
        Route::post('form-demo/preview', [FormDemoController::class, 'preview'])->name('form-demo.preview');

        Route::get('media', [MediaDemoController::class, 'index'])->name('media.index');
        Route::get('media-demo', [MediaDemoController::class, 'index'])->name('media-demo.index');
        Route::post('media-demo', [MediaDemoController::class, 'store'])->name('media-demo.store');
    });
