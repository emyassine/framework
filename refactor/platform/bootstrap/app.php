<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
//> Without the following, a "Hello world" request takes 0.020883 ms
//> Render a dashboard with only request_lifetime() function takes 0.33 ms

require __DIR__.'/fast-boot.php';

return \Webkernel\WebApp::configure()
	    ->with_middleware(
			function (\Webkernel\Platform\Middleware $middleware): void {})
		->with_exceptions(
			function (\Webkernel\Platform\Exceptions $exceptions): void {
        $exceptions->should_render_json_when(
            fn (\Webkernel\Http\Request $request) => $request->is('api/*'),
        );
    })
    ->create();
