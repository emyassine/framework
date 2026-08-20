<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Support;

/**
 * Classic Laravel @vite entry names + framework-cache anchors.
 *
 * Logical keys stay resources/css|js/app.* for Blade.
 * Real files for Tailwind live under storage/framework/cache when host has none.
 */
final class LaravelViteEntries
{
    public const CACHE_REL = 'storage/framework/cache/webkernel/vite';

    /** @var list<string> */
    public const LOGICAL = [
        'resources/css/app.css',
        'resources/js/app.js',
    ];
}
