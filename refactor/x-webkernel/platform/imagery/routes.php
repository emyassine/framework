<?php declare(strict_types=1);

use Webkernel\Imagery\Http\Controllers\BrandingController;
use Webkernel\Route\Route;

Route::get('/__webkernel-app/branding/{brand}/{key}', [BrandingController::class, 'show']);
