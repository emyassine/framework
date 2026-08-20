<?php declare(strict_types=1);

/**
 * Early-boot path free functions (before Laravel / base_path).
 * Thin wrappers over Webkernel\Paths\Composer + package install index.
 */

use Webkernel\Paths\Composer;

/**
 * Normalize a path: collapse slashes, resolve . / .., drop trailing slash.
 */
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
        $resolved = '/' . $resolved;
    }

    return rtrim($resolved, '/');
}

if (! function_exists('webapp_path')) {
    /**
     * Host application root (composer.json directory), optional subpath.
     */
    function webapp_path(?string $path = null): string
    {
        $root = Composer::root();

        return $path
            ? $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)
            : $root;
    }
}

if (! function_exists('vendor_dir')) {
    /**
     * Composer vendor-dir (e.g. third_party or vendor), optional subpath.
     */
    function vendor_dir(?string $path = null): string
    {
        $dir = Composer::vendor_dir();

        return $path
            ? $dir . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)
            : $dir;
    }
}

if (! function_exists('webkernel_package_root')) {
    /**
     * Absolute install path of a package (Composer slug, basename, or extra.webkernel.prefix).
     *
     * @param  string         $name
     * @param  callable|null  $on_error  fn(string): never
     */
    function webkernel_package_root(string $name, ?callable $on_error = null): string
    {
        static $index = null;
        static $prefix_cache = [];
        static $prefixes_loaded = false;

        if (! is_array($index)) {
            $index = [];
            $installed_file = vendor_dir() . '/composer/installed.php';
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
                        $install_path = $installed_dir . DIRECTORY_SEPARATOR . $install_path;
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
                $composer_json = $install_path . '/composer.json';
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
            throw new \LogicException('$on_error must not return: ' . $message);
        }

        throw new \RuntimeException($message);
    }
}

if (! function_exists('webkernel_package')) {
    /**
     * Path inside an installed package root. Does not create missing paths.
     *
     * @param  string         $name      Package slug or extra.webkernel.prefix.
     * @param  string|null    $subpath   Relative to package root.
     * @param  bool           $relative  Relative to webapp root when true.
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
                throw new \LogicException('$on_error must not return: ' . $message);
            }
            throw new \RuntimeException($message);
        }

        if (! $relative) {
            return $resolved;
        }

        $root = webapp_path();
        $prefix = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($resolved, $prefix)
            ? substr($resolved, strlen($prefix))
            : ltrim(str_replace($root, '', $resolved), DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('webkernel_platform_dir')) {
    /**
     * Host platform cache dir: bootstrap/cache/webkernel[/subpath].
     */
    function webkernel_platform_dir(?string $subpath = null): string
    {
        $base = 'bootstrap/cache/webkernel';

        return $subpath !== null && $subpath !== ''
            ? webapp_path($base . '/' . ltrim($subpath, '/'))
            : webapp_path($base);
    }
}

if (! function_exists('webkernel_modules_cache')) {
    /**
     * Host platform cache dir: bootstrap/cache/webkernel/modules[/subpath].
     */
    function webkernel_modules_cache(string $module_name, ?string $subpath = null): string
    {
        $path = webapp_path('bootstrap/cache/webkernel/modules/' . $module_name . ($subpath ? '/' . ltrim($subpath, '/') : ''));

        $dir = pathinfo($path, PATHINFO_EXTENSION) ? dirname($path) : $path;
        is_dir($dir) || mkdir($dir, 0755, true);

        return $path;
    }
}

// =============================================================================
// webkernel_cache_path
// Thin scoped wrapper: always targets storage/framework/cache.
// No webkernel_std_make_subpath — self-contained mkdir + join.
// =============================================================================

if (! function_exists('webkernel_cache_path')) {
    /**
     * Resolves or initializes a subpath within the framework cache directory.
     *
     * @param  string         $subpath     Relative path inside the cache directory.
     * @param  bool           $makeOnMiss  Automatically create target if missing.
     * @param  callable|null  $onError     Custom error handler fn(string): never
     * @return string Absolute resolved path.
     */
    function webkernel_cache_path(
        string $subpath,
        bool $makeOnMiss = true,
        ?callable $onError = null,
    ): string {
        $cacheBase = rtrim(webapp_path('storage/framework/cache'), DIRECTORY_SEPARATOR);
        $subpath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subpath), DIRECTORY_SEPARATOR);
        $target = $subpath === ''
            ? $cacheBase
            : $cacheBase.DIRECTORY_SEPARATOR.$subpath;

        if (is_dir($target) || is_file($target)) {
            return $target;
        }

        if (! $makeOnMiss) {
            $message = "Cache path does not exist: {$target}";
            if ($onError !== null) {
                ($onError)($message);
                throw new \LogicException('$onError must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        if (! is_dir($cacheBase) && ! @mkdir($cacheBase, 0775, true) && ! is_dir($cacheBase)) {
            $message = "Unable to create cache base: {$cacheBase}";
            if ($onError !== null) {
                ($onError)($message);
                throw new \LogicException('$onError must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        // Create full directory tree when target is a dir path (branding, etc.)
        if (! is_dir($target) && ! @mkdir($target, 0775, true) && ! is_dir($target)) {
            $message = "Unable to create cache path: {$target}";
            if ($onError !== null) {
                ($onError)($message);
                throw new \LogicException('$onError must not return: '.$message);
            }
            throw new \RuntimeException($message);
        }

        return $target;
    }
}

if (! function_exists('webkernel_load_asset')) {
    /**
     * Register a package / URL asset and return its public URL.
     *
     * @param  string       $from      Absolute file, "package:subpath", or http(s) URL
     * @param  string       $as        Catalog key, e.g. "codebase/gsap.js"
     * @param  string|null  $to        Optional path under public/, e.g. "js/webkernel/gsap.min.js"
     * @param  bool         $autoload  Inject js/css into every Webkernel page (default true)
     */
    function webkernel_load_asset(string $from, string $as, ?string $to = null, bool $autoload = true): string
    {
        return \Webkernel\Paths\Assets::load($from, $as, $to, $autoload);
    }
}

if (! function_exists('webkernel_asset')) {
    /**
     * Public URL of a registered asset. Throws if the key is unknown.
     */
    function webkernel_asset(string $as): string
    {
        return \Webkernel\Paths\Assets::url($as);
    }
}

// Laravel PackageDiscoverCommand expects this env when vendor-dir is non-default.
putenv('COMPOSER_VENDOR_DIR=' . vendor_dir());
