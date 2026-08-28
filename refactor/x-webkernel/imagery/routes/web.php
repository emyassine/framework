<?php

declare(strict_types=1);
use Webkernel\Imagery\Http\Controllers\IconController;
use Webkernel\Imagery\Http\Controllers\IconSvgController;

Route::middleware(['web'])->group(function (): void {
    Route::get('/imagery/icons', IconController::class)
        ->name('imagery.icons');

    Route::get('/imagery/icon/{icon}', IconSvgController::class)
        ->name('imagery.icon')
        ->where('icon', '.*');
});
