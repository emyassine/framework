<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine

// require __DIR__ . '/fast-boot.php'; // does instllation of thirdparty and some preparation
//
// use Webkernel\Configuration\{Exceptions, Middleware};
// use Webkernel\Http\Request;
// use Webkernel\WebApp;

// ---- Global WebApp Configuration ----------------------------------------------------------
// THOSE MEASURES ARE IN MILLISECONDS WITH SERVER RUNNING ON LOCALHOST
// PHP Version: 8.4.23 | OPcache: enabled | May differs from production (Nginx/Apache/FPM)
// -------------------------------------------------------------------------------------------
// Without the following, a "Hello world" request takes 0.020883 ms
// Render a dashboard with only request_lifetime() function takes 0.33 ms
// Including the autoloader takes


// return WebApp::configure()
//     ->with_middleware(function (Middleware $middleware): void {})
//     ->with_exceptions(function (Exceptions $exceptions): void {
//         $exceptions->should_render_json_when(
//             fn (Request $request) => $request->is('api/*'),
//         );
//     })
//     ->create();
