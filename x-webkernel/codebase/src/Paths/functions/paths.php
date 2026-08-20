<?php declare(strict_types=1);

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

        if (class_exists(\Composer\InstalledVersions::class)) {
            $file = (new \ReflectionClass(\Composer\InstalledVersions::class))->getFileName();
            if (is_string($file) && $file !== '') {
                $vendor = dirname($file, 2);
                $real = realpath($vendor);

                return $dir = $real !== false ? $real : $vendor;
            }
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
     * Absolute install path of a package (Composer slug, basename, or extra.webkernel.prefix).
     *
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_package_root(string $name, ?callable $on_error = null): string
    {
        static $index = null;
        static $prefix_cache = [];
        static $prefixes_loaded = false;

        if (! is_array($index)) {
            $index = [];
            $installed_file = vendor_dir('composer/installed.php');
            if (is_file($installed_file)) {
                /** @var array{versions?: array<string, array<string, mixed>>} $installed */
                $installed = require $installed_file;
                $installed_dir = dirname($installed_file);

                foreach ($installed['versions'] ?? [] as $composer_name => $meta) {
                    if (! is_array($meta) || ! is_string($composer_name)) {
                        continue;
                    }
                    $install_path = (string) ($meta['install_path'] ?? '');
                    if ($install_path === '') {
                        continue;
                    }
                    if (! str_starts_with($install_path, '/')
                        && ! preg_match('#^[A-Za-z]:[\\\\/]#', $install_path)
                    ) {
                        $install_path = $installed_dir.DIRECTORY_SEPARATOR.$install_path;
                    }
                    $index[basename(str_replace('\\', '/', $install_path))] = $install_path;
                    if (str_contains($composer_name, '/')) {
                        [, $slug] = explode('/', $composer_name, 2);
                        $index[$slug] = $install_path;
                    } else {
                        $index[$composer_name] = $install_path;
                    }
                }
            }
        }

        if (isset($index[$name]) && is_string($index[$name]) && $index[$name] !== '') {
            return $index[$name];
        }
        if (isset($prefix_cache[$name])) {
            return $prefix_cache[$name];
        }

        if (! $prefixes_loaded) {
            $prefixes_loaded = true;
            foreach ($index as $install_path) {
                if (! is_string($install_path) || $install_path === '' || ! is_dir($install_path)) {
                    continue;
                }
                $composer_json = $install_path.'/composer.json';
                if (! is_file($composer_json)) {
                    continue;
                }
                $raw = file_get_contents($composer_json);
                if ($raw === false) {
                    continue;
                }
                /** @var array<string, mixed>|null $data */
                $data = json_decode($raw, true);
                $prefix = is_array($data) ? ($data['extra']['webkernel']['prefix'] ?? null) : null;
                if (is_string($prefix) && $prefix !== '') {
                    $prefix_cache[$prefix] = $install_path;
                }
            }
        }

        if (isset($prefix_cache[$name])) {
            return $prefix_cache[$name];
        }

        $message = sprintf('Package [%s] is not installed.', $name);
        if ($on_error !== null) {
            ($on_error)($message);
            throw new \LogicException('$on_error must not return: '.$message);
        }

        throw new \RuntimeException($message);
    }
}

if (! function_exists('webkernel_package')) {
    /**
     * Path inside an installed package root. Does not create missing paths.
     *
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_package(
        string $name,
        ?string $subpath = null,
        bool $relative = false,
        ?callable $on_error = null,
    ): string {
        $package_root = webkernel_package_root($name, $on_error);
        $segments = array_filter(
            [$package_root, $subpath],
            static fn (?string $v): bool => $v !== null && $v !== '',
        );
        $resolved = resolve_filename(implode(DIRECTORY_SEPARATOR, $segments));

        if (! file_exists($resolved)) {
            $message = sprintf('Path does not exist: %s', $resolved);
            if ($on_error !== null) {
                ($on_error)($message);
                throw new \LogicException('$on_error must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        if (! $relative) {
            return $resolved;
        }

        $root = webapp_path();
        $prefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($resolved, $prefix)
            ? substr($resolved, strlen($prefix))
            : ltrim(str_replace($root, '', $resolved), DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('webkernel_platform_dir')) {
    function webkernel_platform_dir(?string $subpath = null): string
    {
        $base = 'bootstrap/cache/webkernel';

        return $subpath !== null && $subpath !== ''
            ? webapp_path($base.'/'.ltrim($subpath, '/'))
            : webapp_path($base);
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
        $cache_base = rtrim(webapp_path('storage/framework/cache'), DIRECTORY_SEPARATOR);
        $subpath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subpath), DIRECTORY_SEPARATOR);
        $target = $subpath === ''
            ? $cache_base
            : $cache_base.DIRECTORY_SEPARATOR.$subpath;

        if (is_dir($target) || is_file($target)) {
            return $target;
        }

        if (! $make_on_miss) {
            $message = "Cache path does not exist: {$target}";
            if ($on_error !== null) {
                ($on_error)($message);
                throw new \LogicException('$on_error must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        if (! is_dir($cache_base) && ! @mkdir($cache_base, 0775, true) && ! is_dir($cache_base)) {
            $message = "Unable to create cache base: {$cache_base}";
            if ($on_error !== null) {
                ($on_error)($message);
                throw new \LogicException('$on_error must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        if (! is_dir($target) && ! @mkdir($target, 0775, true) && ! is_dir($target)) {
            $message = "Unable to create cache path: {$target}";
            if ($on_error !== null) {
                ($on_error)($message);
                throw new \LogicException('$on_error must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        return $target;
    }
}
