<?php declare(strict_types=1);
define('START_REQUEST', hrtime(true));
use Webkernel\Http\Request;
if (file_exists($maint = __DIR__.'/../storage/maintenance.php')) { require $maint; }

// ---- Start WebApp -------------------------------------------------------------------------
// Webkernel Web Application is ... and does ...
require __DIR__.'/../bootstrap/app.php';
Route::run();
