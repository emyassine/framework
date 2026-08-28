<?php declare(strict_types=1);

use Webkernel\Imagery\Branding;
use Webkernel\Route\Route;

Route::get('/__webkernel-app/branding/{brand}/{key}', [Branding::class, 'show']);
