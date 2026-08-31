<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

/**
 * Early-boot path helpers. No Laravel, no Composer PHP library.
 *
 * Source of truth: {vendor}/composer/webkernel.php (lifecycle, dump-autoload).
 * Fallback: Composer\InstalledVersions location (custom vendor-dir safe).
 */

if (! function_exists('resolve_filename')) {
    function resolve_filename(string $filename): string
    {
        $is_absolute = str_starts_with($filename, '/');
        $filename = preg_replace('#/+#', '/', $filename) ?? $filename;
        $parts = explode('/', $filename);
        $out = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }

        $resolved = implode('/', $out);
        if ($is_absolute) {
            $resolved = '/'.$resolved;
        }

        return rtrim($resolved, '/');
    }
}

if (! function_exists('webkernel_vendor_dir')) {
    /**
     * Absolute Composer vendor directory (vendor, third_party, …).
     * InstalledVersions lives at {vendor}/composer/InstalledVersions.php.
     */
    function webkernel_vendor_dir(): string
    {
        static $dir = null;
        if ($dir !== null) {
            return $dir;
        }

        $from_class = static function (string $class, int $up): ?string {
            if (! class_exists($class, false)) {
                return null;
            }
            $file = (new \ReflectionClass($class))->getFileName();
            if (! is_string($file) || $file === '' || str_starts_with($file, 'phar://')) {
                return null;
            }
            $vendor = dirname($file, $up);
            $real = realpath($vendor);

            return $real !== false ? $real : $vendor;
        };

        $installed = $from_class(\Composer\InstalledVersions::class, 2);
        if ($installed !== null) {
            return $dir = $installed;
        }
        if (function_exists('webkernel_composer_dir')) {
            $composer_dir = webkernel_composer_dir();
            if (is_string($composer_dir) && $composer_dir !== '' && ! str_starts_with($composer_dir, 'phar://')) {
                $vendor = dirname($composer_dir);
                $real = realpath($vendor);

                return $dir = $real !== false ? $real : $vendor;
            }
        }
        $loader = $from_class(\Composer\Autoload\ClassLoader::class, 2);
        if ($loader !== null) {
            return $dir = $loader;
        }

        return $dir = 'vendor';
    }
}

if (! function_exists('webkernel_boot_flush')) {
    function webkernel_boot_flush(): void
    {
        webkernel_boot(true);
    }
}

if (! function_exists('webkernel_boot')) {
    /**
     * @param  bool  $reset  Clear process memo (tests / after dump-autoload).
     * @return array{
     *   instance_id: string,
     *   webapp_root: string,
     *   vendor_dir: string,
     *   vendor_rel: string,
     *   generated_at?: string,
     *   stale?: bool
     * }
     */
    function webkernel_boot(bool $reset = false): array
    {
        static $boot = null;

        if ($reset) {
            $boot = null;

            return [];
        }

        if (is_array($boot)) {
            return $boot;
        }

        $vendor = webkernel_vendor_dir();
        $file = $vendor.'/composer/webkernel.php';

        if (is_file($file)) {
            $data = require $file;
            if (is_array($data) && isset($data['webapp_root'], $data['vendor_dir'])) {
                $stored = (string) $data['vendor_dir'];
                $stored_real = realpath($stored) ?: $stored;
                if ($stored_real === $vendor) {
                    return $boot = $data;
                }
                if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                    fwrite(STDERR, "webkernel: host path changed — run composer dump-autoload\n");
                }
            }
        }

        $root = dirname($vendor);
        if (! is_file($root.'/composer.json')) {
            $found = webkernel_find_composer_root($vendor);
            if ($found !== null) {
                $root = $found;
            }
        }

        $vendor_rel = basename($vendor);
        if (str_starts_with($vendor, $root.DIRECTORY_SEPARATOR)) {
            $vendor_rel = substr($vendor, strlen($root) + 1);
        }

        return $boot = [
            'instance_id' => '',
            'webapp_root' => $root,
            'vendor_dir' => $vendor,
            'vendor_rel' => str_replace('\\', '/', $vendor_rel),
            'stale' => true,
        ];
    }
}

if (! function_exists('webkernel_find_composer_root')) {
    function webkernel_find_composer_root(string $start): ?string
    {
        $dir = is_file($start) ? dirname($start) : $start;
        $real = realpath($dir);
        if ($real !== false) {
            $dir = $real;
        }

        $seen = [];
        while ($dir !== '' && ! isset($seen[$dir])) {
            $seen[$dir] = true;
            if (is_file($dir.DIRECTORY_SEPARATOR.'composer.json')) {
                return $dir;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return null;
    }
}

if (! function_exists('webapp_path')) {
    /**
     * Host application root (directory that owns composer.json), optional subpath.
     */
    function webapp_path(?string $path = null): string
    {
        $root = webkernel_boot()['webapp_root'];

        return $path
            ? $root.DIRECTORY_SEPARATOR.ltrim($path, '/\\')
            : $root;
    }
}

if (! function_exists('vendor_dir')) {
    function vendor_dir(?string $path = null): string
    {
        $dir = webkernel_boot()['vendor_dir'];

        return $path
            ? $dir.DIRECTORY_SEPARATOR.ltrim($path, '/\\')
            : $dir;
    }
}

if (! function_exists('webkernel_vendor_prefix')) {
    function webkernel_vendor_prefix(): string
    {
        $dir = vendor_dir();
        $real = realpath($dir) ?: $dir;

        return rtrim($real, '/\\').DIRECTORY_SEPARATOR;
    }
}

if (! function_exists('webkernel_package_root')) {
    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_package_root(string $name, ?callable $on_error = null): string
    {
        return \Webkernel\Paths\Package::root($name, $on_error);
    }
}

if (! function_exists('webkernel_package')) {
    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_package(
        string $name,
        ?string $subpath = null,
        bool $relative = false,
        ?callable $on_error = null,
    ): string {
        return \Webkernel\Paths\Package::path($name, $subpath, $relative, $on_error);
    }
}

if (! function_exists('webkernel_platform_dir')) {
    function webkernel_platform_dir(?string $subpath = null): string
    {
        return \Webkernel\Paths\Package::platform_dir($subpath);
    }
}

if (! function_exists('webkernel_cache_path')) {
    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_cache_path(
        string $subpath,
        bool $make_on_miss = true,
        ?callable $on_error = null,
    ): string {
        return \Webkernel\Paths\Package::cache_path($subpath, $make_on_miss, $on_error);
    }
}
