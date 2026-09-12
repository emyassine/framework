<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config;

use Webkernel\Config\Compiler\ConfigCompiler;
use Webkernel\Config\Compiler\ConfigFingerprint;
use Webkernel\Config\Discovery\ConfigDiscovery;
use Webkernel\Config\Discovery\PublishableConfig;
use Webkernel\Config\Exceptions\ConfigGuardException;
use Webkernel\Config\Guards\ConfigGuard;
use Webkernel\Config\Repository\ConfigRepository;
use Webkernel\Config\Writer\ConfigWriter;

/**
 * Static façade for ultra-fast zero-overhead configuration access.
 *
 * Exposes direct flat hash table access approaching static C speeds with
 * automatic recompilation when source configuration files or directories change.
 */
final class Config
{
    /** @var array<string, mixed> Direct static memory array for sub-microsecond O(1) lookups */
    public static array $items = [];

    protected static ?ConfigRepository $repository = null;
    protected static ?ConfigGuard $guard = null;
    protected static ?ConfigDiscovery $discovery = null;
    protected static ?ConfigCompiler $compiler = null;

    protected static bool $booted = false;
    protected static string $root_path = '';
    protected static string $vendor_path = '';
    protected static string $cache_dir = '';
    protected static string $cache_file = '';
    protected static string $runtime_config_file = '';

    /** @var list<string> Additional custom configuration directories */
    protected static array $custom_directories = [];

    // Pure static façade
    private function __construct() {}

    // -------------------------------------------------------------------------
    // Bootstrap & Configuration
    // -------------------------------------------------------------------------

    /**
     * Explicitly sets system paths prior to initialization.
     *
     * @param $root_path string Absolute path to application/platform root.
     * @param $vendor_path string Absolute path to Composer vendor directory.
     * @param $cache_dir string Absolute path to cache directory.
     *
     * @return void
     */
    public static function set_paths(string $root_path, string $vendor_path = '', string $cache_dir = ''): void
    {
        self::$root_path = \rtrim($root_path, '/\\');
        self::$vendor_path = $vendor_path !== '' ? \rtrim($vendor_path, '/\\') : '';
        self::$cache_dir = $cache_dir !== '' ? \rtrim($cache_dir, '/\\') : '';
        self::flush();
    }

    /**
     * Registers an additional directory to scan for configuration files.
     *
     * @param $directory string
     *
     * @return void
     */
    public static function add_directory(string $directory): void
    {
        $normalized = \rtrim($directory, '/\\');

        if ($normalized !== '' && ! \in_array($normalized, self::$custom_directories, true)) {
            self::$custom_directories[] = $normalized;
            self::$discovery?->add_directory($normalized);
        }
    }

    /**
     * Registers a PlatformProvider class for package configuration extraction.
     *
     * @param $provider_class class-string
     *
     * @return void
     */
    public static function register_provider(string $provider_class): void
    {
        self::ensure_discovery();
        self::$discovery->add_provider($provider_class);
    }

    /**
     * Protects specified keys or prefixes from runtime modification.
     *
     * @param $protected_keys list<string>
     *
     * @return void
     */
    public static function protect(array $protected_keys): void
    {
        self::$guard = self::$guard !== null
            ? self::$guard->with_keys($protected_keys)
            : new ConfigGuard($protected_keys);

        self::$repository?->set_guard(self::$guard);
    }

    /**
     * Boots the configuration engine with automatic freshness verification.
     *
     * Compiles configuration sources if the compiled cache is absent or stale.
     *
     * @param $root_path string|null
     * @param $vendor_path string|null
     * @param $cache_dir string|null
     *
     * @return void
     */
    public static function boot(
        ?string $root_path = null,
        ?string $vendor_path = null,
        ?string $cache_dir = null,
    ): void {
        if ($root_path !== null) {
            self::$root_path = \rtrim($root_path, '/\\');
        }

        if ($vendor_path !== null) {
            self::$vendor_path = \rtrim($vendor_path, '/\\');
        }

        if ($cache_dir !== null) {
            self::$cache_dir = \rtrim($cache_dir, '/\\');
        }

        self::init_paths();
        self::ensure_discovery();
        self::ensure_compiler();

        $sources = self::$discovery->discover();
        $source_files = self::$discovery->get_source_files();
        $directories = self::$discovery->get_directories();

        // Include runtime config file in stale checks if it exists
        if (\is_file(self::$runtime_config_file)) {
            $source_files[] = self::$runtime_config_file;
        }

        // Automatic compile check: recompile immediately if stale
        if (ConfigFingerprint::is_stale(self::$cache_file, $source_files, $directories)) {
            self::do_compile($sources);
        } else {
            $cached = require self::$cache_file;

            if (\is_array($cached) && isset($cached['flat'])) {
                self::$items = $cached['flat'];
                $tree = $cached['tree'] ?? [];
            } else {
                self::$items = \is_array($cached) ? $cached : [];
                $tree = [];
            }

            self::$repository = new ConfigRepository($tree, self::$items, self::$guard);
        }

        self::$booted = true;
    }

    /**
     * Forces recompilation of all configuration sources and reloads cache.
     *
     * @return void
     */
    public static function recompile(): void
    {
        self::init_paths();
        self::ensure_discovery();
        self::ensure_compiler();

        $sources = self::$discovery->discover();
        self::do_compile($sources);
        self::$booted = true;
    }

    /**
     * Invalidates on-disk cache files and empties in-memory items.
     *
     * @return void
     */
    public static function invalidate(): void
    {
        self::init_paths();

        if (\is_file(self::$cache_file)) {
            @\unlink(self::$cache_file);
        }

        self::flush();
    }

    /**
     * Flushes in-memory state. Used between requests in persistent runtimes (Swoole, FrankenPHP).
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$items = [];
        self::$repository?->flush();
        self::$repository = null;
        self::$discovery = null;
        self::$compiler = null;
        self::$booted = false;
    }

    /**
     * Determines whether the configuration system has completed initial boot.
     *
     * @return bool
     */
    public static function is_booted(): bool
    {
        return self::$booted;
    }

    // -------------------------------------------------------------------------
    // Value Access & Mutation
    // -------------------------------------------------------------------------

    /**
     * Retrieves a configuration value by dot-notation key at hardware speed.
     *
     * @param $key string Dot-notation configuration key.
     * @param $default mixed Fallback value when key is absent.
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? self::resolve_missing($key, $default);
    }

    /**
     * Determines whether a configuration key exists and is non-null.
     *
     * @param $key string Dot-notation configuration key.
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$items[$key]) || self::resolve_has($key);
    }

    /**
     * Sets a configuration value at runtime and persists it atomically.
     *
     * @param $key string Dot-notation configuration key.
     * @param $value mixed Value to assign.
     *
     * @return ConfigRepository Fluent repository instance.
     *
     * @throws ConfigGuardException When key is protected.
     */
    public static function set(string $key, mixed $value): ConfigRepository
    {
        if (! self::$booted) {
            self::boot();
        }

        self::$guard?->assert($key);

        // Update in-memory static items and repository
        self::$items[$key] = $value;
        self::$repository->set($key, $value);

        // If value is an array, expand its children into self::$items
        if (\is_array($value)) {
            $flattened = ConfigCompiler::flatten_tree($value, $key);
            foreach ($flattened as $k => $v) {
                self::$items[$k] = $v;
            }
        }

        // Persist to runtime configuration file
        self::persist_runtime_override($key, $value);

        return self::$repository;
    }

    /**
     * Returns the entire multi-dimensional configuration tree.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (! self::$booted) {
            self::boot();
        }

        return self::$repository->all();
    }

    /**
     * Returns the compiled flat dot-notation dictionary.
     *
     * @return array<string, mixed>
     */
    public static function all_flat(): array
    {
        if (! self::$booted) {
            self::boot();
        }

        return self::$items;
    }

    /**
     * Returns all publishable package configurations, optionally filtered by tag.
     *
     * @param $tag string|null
     *
     * @return list<PublishableConfig>
     */
    public static function publishables(?string $tag = null): array
    {
        self::ensure_discovery();

        return self::$discovery->get_publishables($tag);
    }

    /**
     * Resolves a named path from configuration into an absolute filesystem path.
     *
     * @param $key string Dot-notation key containing relative path, or raw path string.
     * @param $sub_path string Optional relative sub-segment to append.
     *
     * @return string Absolute filesystem path.
     */
    public static function path(string $key, string $sub_path = ''): string
    {
        if (! self::$booted) {
            self::boot();
        }

        $resolved = self::get($key);

        if (! \is_string($resolved) || $resolved === '') {
            $resolved = $key;
        }

        $full = \str_starts_with($resolved, '/')
            ? $resolved
            : self::$root_path . '/' . \ltrim($resolved, '/\\');

        if ($sub_path !== '') {
            $full .= '/' . \ltrim($sub_path, '/\\');
        }

        return $full;
    }

    /**
     * Returns the underlying repository instance.
     *
     * @return ConfigRepository
     */
    public static function repository(): ConfigRepository
    {
        if (! self::$booted) {
            self::boot();
        }

        return self::$repository;
    }

    /**
     * Alias of repository() for compatibility.
     *
     * @return ConfigRepository
     */
    public static function instance(): ConfigRepository
    {
        return self::repository();
    }

    // -------------------------------------------------------------------------
    // Internal Helpers
    // -------------------------------------------------------------------------

    /**
     * Fallback resolution when key is absent in self::$items or has null value.
     *
     * @param $key string
     * @param $default mixed
     *
     * @return mixed
     */
    protected static function resolve_missing(string $key, mixed $default): mixed
    {
        if (! self::$booted) {
            self::boot();

            if (isset(self::$items[$key])) {
                return self::$items[$key];
            }
        }

        if (\array_key_exists($key, self::$items)) {
            return self::$items[$key];
        }

        return $default;
    }

    /**
     * Fallback check when key evaluates to false in isset(self::$items[$key]).
     *
     * @param $key string
     *
     * @return bool
     */
    protected static function resolve_has(string $key): bool
    {
        if (! self::$booted) {
            self::boot();
        }

        return \array_key_exists($key, self::$items);
    }

    /**
     * Initializes default platform, vendor, cache, and runtime paths.
     *
     * @return void
     */
    protected static function init_paths(): void
    {
        if (self::$root_path === '') {
            self::$root_path = \base_path();
        }

        if (self::$vendor_path === '') {
            self::$vendor_path = \vendor_path();
        }

        if (self::$cache_dir === '') {
            $preferred_cache = self::$root_path . '/internal/cache';

            if (\is_dir($preferred_cache) || @\mkdir($preferred_cache, 0775, true)) {
                self::$cache_dir = $preferred_cache;
            } else {
                self::$cache_dir = self::$root_path . '/storage/framework/cache';
            }
        }

        self::$cache_file = self::$cache_dir . '/config_compiled.php';
        self::$runtime_config_file = self::$root_path . '/internal/platform-runtime.php';
    }

    /**
     * Ensures the ConfigDiscovery service is instantiated.
     *
     * @return void
     */
    protected static function ensure_discovery(): void
    {
        if (self::$discovery !== null) {
            return;
        }

        self::init_paths();

        $dirs = [
            self::$root_path . '/config',
            ...self::$custom_directories,
        ];

        self::$discovery = new ConfigDiscovery($dirs, self::$vendor_path);
    }

    /**
     * Ensures the ConfigCompiler service is instantiated.
     *
     * @return void
     */
    protected static function ensure_compiler(): void
    {
        if (self::$compiler === null) {
            self::$compiler = new ConfigCompiler();
        }
    }

    /**
     * Performs compilation, writes cache, and updates in-memory structures.
     *
     * @param $sources array<string, array{path: string, type: string}>
     *
     * @return void
     */
    protected static function do_compile(array $sources): void
    {
        $file_sources = [];
        foreach ($sources as $stem => $entry) {
            $file_sources[$stem] = $entry['path'];
        }

        $runtime_overrides = [];
        if (\is_file(self::$runtime_config_file)) {
            $loaded = require self::$runtime_config_file;
            if (\is_array($loaded)) {
                $runtime_overrides = $loaded;
            }
        }

        $compiled = self::$compiler->compile($file_sources, $runtime_overrides);

        self::$compiler->write_cache(self::$cache_file, $compiled['flat'], $compiled['tree']);

        self::$items = $compiled['flat'];
        self::$repository = new ConfigRepository($compiled['tree'], self::$items, self::$guard);
    }

    /**
     * Persists a key-value override into the platform runtime file atomically.
     *
     * @param $key string
     * @param $value mixed
     *
     * @return void
     */
    protected static function persist_runtime_override(string $key, mixed $value): void
    {
        $current = [];
        if (\is_file(self::$runtime_config_file)) {
            $loaded = require self::$runtime_config_file;
            if (\is_array($loaded)) {
                $current = $loaded;
            }
        }

        // Expand key into nested branch for runtime file
        $parts = \explode('.', $key);
        $tree = $value;
        for ($i = \count($parts) - 1; $i >= 0; $i--) {
            $tree = [$parts[$i] => $tree];
        }

        $merged = \array_replace_recursive($current, $tree);
        ConfigWriter::write(self::$runtime_config_file, $merged);
    }
}
