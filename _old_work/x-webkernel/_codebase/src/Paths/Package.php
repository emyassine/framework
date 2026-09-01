<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Paths;

/**
 * Package install paths. Loaded only when webkernel_package_root() is called.
 */
final class Package
{
    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    public static function root(string $name, ?callable $on_error = null): string
    {
        static $index = null;
        static $prefix_cache = [];
        static $prefixes_loaded = false;

        if (! \is_array($index)) {
            $index = [];
            $installed_file = vendor_dir('composer/installed.php');
            if (\is_file($installed_file)) {
                /** @var array{versions?: array<string, array<string, mixed>>} $installed */
                $installed = require $installed_file;
                $installed_dir = \dirname($installed_file);

                foreach ($installed['versions'] ?? [] as $composer_name => $meta) {
                    if (! \is_array($meta) || ! \is_string($composer_name)) {
                        continue;
                    }
                    $install_path = (string) ($meta['install_path'] ?? '');
                    if ($install_path === '') {
                        continue;
                    }
                    if (! \str_starts_with($install_path, '/')
                        && ! \preg_match('#^[A-Za-z]:[\\\\/]#', $install_path)
                    ) {
                        $install_path = $installed_dir.DIRECTORY_SEPARATOR.$install_path;
                    }
                    $index[\basename(\str_replace('\\', '/', $install_path))] = $install_path;
                    if (\str_contains($composer_name, '/')) {
                        [, $slug] = \explode('/', $composer_name, 2);
                        $index[$slug] = $install_path;
                    } else {
                        $index[$composer_name] = $install_path;
                    }
                }
            }
        }

        if (isset($index[$name]) && \is_string($index[$name]) && $index[$name] !== '') {
            return $index[$name];
        }
        if (isset($prefix_cache[$name])) {
            return $prefix_cache[$name];
        }

        if (! $prefixes_loaded) {
            $prefixes_loaded = true;
            foreach ($index as $install_path) {
                if (! \is_string($install_path) || $install_path === '' || ! \is_dir($install_path)) {
                    continue;
                }
                $composer_json = $install_path.'/composer.json';
                if (! \is_file($composer_json)) {
                    continue;
                }
                $raw = \file_get_contents($composer_json);
                if ($raw === false) {
                    continue;
                }
                /** @var array<string, mixed>|null $data */
                $data = \json_decode($raw, true);
                $prefix = \is_array($data) ? ($data['extra']['webkernel']['prefix'] ?? null) : null;
                if (\is_string($prefix) && $prefix !== '') {
                    $prefix_cache[$prefix] = $install_path;
                }
            }
        }

        if (isset($prefix_cache[$name])) {
            return $prefix_cache[$name];
        }

        $message = \sprintf('Package [%s] is not installed.', $name);
        if ($on_error !== null) {
            ($on_error)($message);
            throw new \LogicException('$on_error must not return: '.$message);
        }

        throw new \RuntimeException($message);
    }

    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    public static function path(
        string $name,
        ?string $subpath = null,
        bool $relative = false,
        ?callable $on_error = null,
    ): string {
        $package_root = self::root($name, $on_error);
        $segments = \array_filter(
            [$package_root, $subpath],
            static fn (?string $v): bool => $v !== null && $v !== '',
        );
        $resolved = resolve_filename(\implode(DIRECTORY_SEPARATOR, $segments));

        if (! \file_exists($resolved)) {
            $message = \sprintf('Path does not exist: %s', $resolved);
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
        $prefix = \rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return \str_starts_with($resolved, $prefix)
            ? \substr($resolved, \strlen($prefix))
            : \ltrim(\str_replace($root, '', $resolved), DIRECTORY_SEPARATOR);
    }

    public static function platform_dir(?string $subpath = null): string
    {
        $base = 'bootstrap/cache/webkernel';

        return $subpath !== null && $subpath !== ''
            ? webapp_path($base.'/'.\ltrim($subpath, '/'))
            : webapp_path($base);
    }

    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    public static function cache_path(
        string $subpath,
        bool $make_on_miss = true,
        ?callable $on_error = null,
    ): string {
        $cache_base = \rtrim(webapp_path('storage/framework/cache'), DIRECTORY_SEPARATOR);
        $subpath = \ltrim(\str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subpath), DIRECTORY_SEPARATOR);
        $target = $subpath === ''
            ? $cache_base
            : $cache_base.DIRECTORY_SEPARATOR.$subpath;

        if (\is_dir($target) || \is_file($target)) {
            return $target;
        }

        if (! $make_on_miss) {
            self::fail("Cache path does not exist: {$target}", $on_error);
        }

        if (! \is_dir($cache_base) && ! @\mkdir($cache_base, 0775, true) && ! \is_dir($cache_base)) {
            self::fail("Unable to create cache base: {$cache_base}", $on_error);
        }

        if (! \is_dir($target) && ! @\mkdir($target, 0775, true) && ! \is_dir($target)) {
            self::fail("Unable to create cache path: {$target}", $on_error);
        }

        return $target;
    }

    /**
     * @param  callable|null  $on_error  fn(string): never
     */
    private static function fail(string $message, ?callable $on_error): never
    {
        if ($on_error !== null) {
            ($on_error)($message);
            throw new \LogicException('$on_error must not return: '.$message);
        }

        throw new \RuntimeException($message);
    }
}
