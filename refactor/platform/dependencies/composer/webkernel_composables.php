<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Generated. Do not edit.

return [
    'acl' => \Webkernel\Composables\AclComposable::class,
    'auth' => \Webkernel\Composables\AuthComposable::class,
    'cache' => \Webkernel\Composables\CacheComposable::class,
    'cluster' => \Webkernel\Composables\ClusterComposable::class,
    'config' => \Webkernel\Composables\ConfigComposable::class,
    'console' => \Webkernel\Console\Kernel::class,
    'middleware' => \Webkernel\Platform\Middleware::class,
    'module' => \Webkernel\Composables\ModuleComposable::class,
    'page' => \Webkernel\Composables\PageComposable::class,
    'panel' => \Webkernel\Composables\PanelComposable::class,
    'performance' => \Webkernel\Performance\Performance::class,
    'platform' => \Webkernel\Composables\PlatformComposable::class,
    'request' => \Webkernel\Http\Request::class,
    'resource' => \Webkernel\Composables\ResourceComposable::class,
    'response' => \Webkernel\Composables\ResponseComposable::class,
    'route' => \Webkernel\Route\Route::class,
    'storage' => \Webkernel\Composables\StorageComposable::class,
    'terminal' => \Webkernel\Console\Terminal::class,
    'view' => \Webkernel\View\View::class,
];
