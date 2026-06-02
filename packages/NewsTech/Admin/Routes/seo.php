<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Seo\Http\Controllers\Admin\SeoAnalysisController;

Route::post('seo/analyze', SeoAnalysisController::class)
    ->name('seo.analyze');
