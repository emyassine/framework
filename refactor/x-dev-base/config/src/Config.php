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
     * @param list<string> $protected_keys
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

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::instance()->get($key, $default);
    }

    /**
     * @throws ConfigGuardException when the key is protected.
     */
    public static function set(string $key, mixed $value): PlatformConfig
    {
        return self::instance()->set($key, $value);
    }

    public static function has(string $key): bool
    {
        return self::instance()->has($key);
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        return self::instance()->all();
    }

    public static function path(string|ConfigPath $key, string $sub_path = ''): string
    {
        return self::instance()->path($key, $sub_path);
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /**
     * Flush the singleton (use between requests in persistent runtimes).
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
     */
    public static function reset(): void
    {
        self::$instance      = null;
        self::$platform_path = '';
        self::$vendor_path   = '';
        self::$guard         = null;
    }
}
