<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\Config\ConfigQuickAccess;

if (! \function_exists('config')) {
    /**
     * Access the platform config singleton.
     *
     * config()               → PlatformConfig instance (for chaining / injection)
     * config('app.name')     → mixed  (shorthand for Config::get())
     * config('key', $default)→ mixed
     *
     * @return ($key is null ? PlatformConfig : mixed)
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
    	return ConfigQuickAccess::get($key, $default);
    }
}
