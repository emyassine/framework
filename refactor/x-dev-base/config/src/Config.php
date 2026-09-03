<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config;
use Webkernel\Config\Enums\ConfigPath;
use Webkernel\Config\Guards\ConfigGuard;
use Webkernel\Config\Exceptions\ConfigGuardException;
/**
 * Static façade for the PlatformConfig singleton.
 *
 * Registered as "Webkernel\\Config" in composer.json classmap so that
 * `Webkernel\Config::get(...)` works without an explicit `use` import.
 *
 * Compatible with Swoole and FrankenPHP request-lifecycle flushes:
 * call Config::flush() between requests to reset the singleton.
 *
 * The global helper config() is defined in helpers.php (autoloaded via
 * composer.json "files" key) and delegates to Config::instance().
 */
final class Config
{
    private static ?PlatformConfig $instance = null;
    private static string $platform_path = '';
    private static string $vendor_path   = '';
    private static ?ConfigGuard $guard   = null;
    // No instantiation — pure static façade
    private function __construct() {}
    // -------------------------------------------------------------------------
    // Bootstrap API
    // -------------------------------------------------------------------------
    /**
     * Pre-seed paths before the first access (optional — instance() falls
     * back to platform_path() / vendor_path() globals automatically).
     *
     * @param  string $platform_path  Absolute path to the platform root directory.
     * @param  string $vendor_path    Absolute path to the vendor directory (optional).
     * @return void
     */
    public static function set_paths(string $platform_path, string $vendor_path = ''): void
    {
        self::$platform_path = $platform_path;
        self::$vendor_path   = $vendor_path;
        self::$instance      = null;
    }
    /**
     * Attach a guard that protects keys from runtime mutation via set().
     * Can be called before or after boot — applied on next singleton creation.
     *
     * @param  list<string> $protected_keys  Dot-notation keys to lock (e.g. "app.debug").
     * @return void
     */
    public static function protect(array $protected_keys): void
    {
        self::$guard = self::$guard !== null
            ? self::$guard->with_keys($protected_keys)
            : new ConfigGuard($protected_keys);
        // Apply to live instance if already booted
        self::$instance?->set_guard(self::$guard);
    }
    /**
     * Boot (or re-boot) the config singleton.
     *
     * Explicit $platform_path / $vendor_path override the stored values.
     * Passing null leaves the stored value unchanged.
     *
     * @param  string|null $platform_path  Absolute path to the platform root (null = keep stored).
     * @param  string|null $vendor_path    Absolute path to the vendor directory (null = keep stored).
     * @return PlatformConfig              The freshly created singleton instance.
     *
     * @throws \RuntimeException           When the resolved paths are missing or not directories.
     */
    public static function boot(?string $platform_path = null, ?string $vendor_path = null): PlatformConfig
    {
        if ($platform_path !== null) {
            self::$platform_path = $platform_path;
        }
        if ($vendor_path !== null) {
            self::$vendor_path = $vendor_path;
        }
        self::$instance = null;
        return self::instance();
    }
    /**
     * Return the live singleton, creating and booting it on first call.
     *
     * @return PlatformConfig
     *
     * @throws \RuntimeException  When the platform root or vendor path cannot be resolved.
     */
     public static function instance(): PlatformConfig
     {
         if (self::$instance === null) {
             // Resolve platform root path
             $root = self::$platform_path !== ''
                 ? self::$platform_path
                 : (\function_exists('base_path') ? \base_path() : null);
             if ($root === null || !is_dir($root)) {
                 throw new \RuntimeException(
                     'Platform root path is not configured. Something Went Wrong in the webkernel/lifecycle. Call Config::set_paths() or define platform_path().'
                 );
             }
             // Resolve vendor path
             $vendor = self::$vendor_path !== ''
                 ? self::$vendor_path
                 : (\function_exists('vendor_path') ? \vendor_path() : null);
             if ($vendor === null || !is_dir($vendor)) {
                 throw new \RuntimeException(
                     'Vendor path is not configured. Something Went Wrong in the webkernel/lifecycle. Call Config::set_paths() or define vendor_path().'
                 );
             }
             self::$instance = new PlatformConfig($root, $vendor, self::$guard);
             self::$instance->boot();
         }
         return self::$instance;
     }
    // -------------------------------------------------------------------------
    // Config read/write façade (mirror PlatformConfig public API)
    // -------------------------------------------------------------------------
    /**
     * Retrieve a configuration value by dot-notation key.
     *
     * The key format is: <filename>.<key>.<nested_key>
     * Examples:
     *   Config::get('app.name')
     *   Config::get('database.connections.mysql.host')
     *   Config::get('mail.mailers.smtp.port', 587)
     *
     * @param  string $key      Dot-notation path: filename + key segments joined by ".".
     * @param  mixed  $default  Fallback value when the key is absent or null.
     * @return mixed            The resolved value, or $default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::instance()->get($key, $default);
    }
    /**
     * Set a configuration value at runtime.
     *
     * @param  string $key    Dot-notation key to write (same format as get()).
     * @param  mixed  $value  Value to store.
     * @return PlatformConfig Fluent — returns the singleton for chaining.
     *
     * @throws ConfigGuardException  When the key is protected by a ConfigGuard.
     */
    public static function set(string $key, mixed $value): PlatformConfig
    {
        return self::instance()->set($key, $value);
    }
    /**
     * Determine whether a configuration key exists and is non-null.
     *
     * @param  string $key  Dot-notation key to check.
     * @return bool         True when the key is present and not null.
     */
    public static function has(string $key): bool
    {
        return self::instance()->has($key);
    }
    /**
     * Return the entire merged configuration tree.
     *
     * @return array<string, mixed>  All loaded config files, keyed by filename stem.
     */
    public static function all(): array
    {
        return self::instance()->all();
    }
    /**
     * Resolve a named path constant, with an optional sub-path appended.
     *
     * @param  string|ConfigPath $key       A ConfigPath enum case or its string equivalent.
     * @param  string            $sub_path  Optional relative segment appended after a directory separator.
     * @return string                       The resolved absolute path.
     */
    public static function path(string|ConfigPath $key, string $sub_path = ''): string
    {
        return self::instance()->path($key, $sub_path);
    }
    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------
    /**
     * Flush the singleton (use between requests in persistent runtimes).
     *
     * @return void
     */
    public static function flush(): void
    {
        if (self::$instance !== null) {
            self::$instance->flush();
            self::$instance = null;
        }
    }
    /**
     * Full reset including stored paths and guard (useful in tests).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance      = null;
        self::$platform_path = '';
        self::$vendor_path   = '';
        self::$guard         = null;
    }
}
