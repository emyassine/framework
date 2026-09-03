<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config;

/**
 * Ultra-fast configuration access with automatic compilation.
 *
 * Provides C-like static performance through pre-compiled flat arrays.
 * Zero runtime parsing — direct hash table lookups.
 * Automatic recompilation when source files change (filemtime-based).
 *
 * Usage:
 *   ConfigQuickAccess::get('app.name')        // O(1) lookup
 *   ConfigQuickAccess::recompile()            // Force recompilation
 *   ConfigQuickAccess::invalidate()          // Invalidate cache
 *
 * Or use the global function:
 *   cfg('app.name')                          // Even faster - direct static call
 */
final class ConfigQuickAccess
{
    private static string $cache_dir = '';
    private static string $cache_file = '';
    private static string $fingerprint_file = '';
    private static string $platform_path = '';
    private static string $vendor_path = '';

    /** @var array<string, mixed> */
    private static array $compiled = [];
    private static bool $loaded = false;
    private static bool $fingerprint_valid = false;

    private function __construct() {}
    private function __clone() {}

    /**
     * Initialize paths. Called automatically.
     */
    private static function init(): void
    {
        if (self::$platform_path !== '') {
            return;
        }

        self::$platform_path = \function_exists('base_path') ? \base_path() : getcwd();
        self::$vendor_path = \function_exists('vendor_path') ? \vendor_path() : self::$platform_path . '/vendor';
        self::$cache_dir = self::$platform_path . '/storage/framework/cache';
        self::$cache_file = self::$cache_dir . '/config_quick.php';
        self::$fingerprint_file = self::$cache_dir . '/config_quick.fingerprint';

        // Ensure cache directory exists
        if (!\is_dir(self::$cache_dir)) {
            @\mkdir(self::$cache_dir, 0755, true);
        }
    }

    /**
     * Get a configuration value by key.
     *
     * O(1) hash table lookup when cache is available.
     * Automatically recompiles if source files have changed.
     *
     * @param string $key Dot-notation configuration key
     * @param mixed $default Default value if key not found
     * @return mixed The configuration value or default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        self::load_if_needed();

        return self::$compiled[$key] ?? $default;
    }

    /**
     * Check if a configuration key exists.
     *
     * @param string $key Dot-notation configuration key
     * @return bool True if key exists
     */
    public static function has(string $key): bool
    {
        self::init();
        self::load_if_needed();

        return isset(self::$compiled[$key]);
    }

    /**
     * Get the entire compiled configuration array.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        self::init();
        self::load_if_needed();

        return self::$compiled;
    }

    /**
     * Force recompilation of the configuration cache.
     */
    public static function recompile(): void
    {
        self::init();
        self::do_compile();
    }

    /**
     * Invalidate the cache, forcing recompilation on next access.
     */
    public static function invalidate(): void
    {
        self::init();

        if (\is_file(self::$cache_file)) {
            @\unlink(self::$cache_file);
        }
        if (\is_file(self::$fingerprint_file)) {
            @\unlink(self::$fingerprint_file);
        }

        self::$compiled = [];
        self::$loaded = false;
        self::$fingerprint_valid = false;
    }

    /**
     * Set custom cache directory.
     */
    public static function set_cache_dir(string $dir): void
    {
        self::$cache_dir = \rtrim($dir, '/\\');
        self::$cache_file = self::$cache_dir . '/config_quick.php';
        self::$fingerprint_file = self::$cache_dir . '/config_quick.fingerprint';
    }

    /**
     * Load cache if needed, checking fingerprint validity.
     */
    private static function load_if_needed(): void
    {
        if (self::$loaded) {
            return;
        }

        self::check_fingerprint();

        if (self::$fingerprint_valid && \is_file(self::$cache_file)) {
            self::$compiled = require self::$cache_file;
            self::$loaded = true;
            return;
        }

        // Cache is invalid, compile
        self::do_compile();
    }

    /**
     * Check if the current fingerprint matches the stored fingerprint.
     */
    private static function check_fingerprint(): void
    {
        if (!\is_file(self::$fingerprint_file)) {
            self::$fingerprint_valid = false;
            return;
        }

        $stored_fingerprint = \trim(\file_get_contents(self::$fingerprint_file) ?? '');
        $current_fingerprint = self::create_fingerprint();

        self::$fingerprint_valid = ($stored_fingerprint === $current_fingerprint);
    }

    /**
     * Create fingerprint from all potential config source files.
     */
    private static function create_fingerprint(): string
    {
        $files = self::discover_config_files();
        $data = '';

        foreach ($files as $file) {
            if (\is_file($file)) {
                $data .= $file . ':' . \filemtime($file) . ':' . \filesize($file) . ';';
            }
        }

        return \md5($data);
    }

    /**
     * Discover all configuration files that should be compiled.
     * Scans standard locations without hardcoding specific paths.
     *
     * @return array<string>
     */
    private static function discover_config_files(): array
    {
        $files = [];

        // Standard config directories
        $config_dirs = [
            self::$platform_path . '/config',
            self::$platform_path . '/internal',
            self::$vendor_path . '/composer',
        ];

        foreach ($config_dirs as $dir) {
            if (\is_dir($dir)) {
                self::scan_directory($dir, $files);
            }
        }

        // Also check for package configs via provider manifest
        $manifest_path = self::$vendor_path . '/composer/webkernel_providers.php';
        if (\is_file($manifest_path)) {
            self::discover_provider_configs($manifest_path, $files);
        }

        return \array_unique($files);
    }

    /**
     * Scan a directory for PHP files.
     * @param $files
     */
    private static function scan_directory(string $dir, array &$files): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getRealPath() ?: $file->getPathname();
            }
        }
    }

    /**
     * Discover config files from provider manifest.
     * @param $file
     */
    private static function discover_provider_configs(string $manifest_path, array &$files): void
    {
        $providers = require $manifest_path;

        if (!\is_array($providers)) {
            return;
        }

        foreach ($providers as $class) {
            if (!\is_string($class) || !\class_exists($class)) {
                continue;
            }

            foreach ($class::declaration('CONFIG') as $path) {
                if (\is_string($path) && $path !== '' && \is_file($path)) {
                    $files[] = \realpath($path) ?: $path;
                }
            }
        }
    }

    /**
     * Perform the actual compilation.
     */
    private static function do_compile(): void
    {
        $files = self::discover_config_files();
        $merged = [];

        // Load all config files and merge them
        foreach ($files as $file) {
            if (\is_file($file)) {
                $contents = require $file;
                if (\is_array($contents)) {
                    $merged = \array_replace_recursive($merged, $contents);
                }
            }
        }

        // Compile to flat array
        $flat = self::flatten_array($merged);

        // Generate PHP code
        $php_code = self::generate_php_code($flat);

        // Write cache file
        \file_put_contents(self::$cache_file, $php_code);

        // Write fingerprint
        \file_put_contents(self::$fingerprint_file, self::create_fingerprint());

        // Reload
        self::$compiled = $flat;
        self::$loaded = true;
        self::$fingerprint_valid = true;
    }

    /**
     * Flatten nested array to dot-notation keys.
     *
     * @param array<string, mixed> $array
     * @param string $prefix
     * @return array<string, mixed>
     */
    private static function flatten_array(array $array, string $prefix = ''): array
    {
        $flat = [];

        foreach ($array as $key => $value) {
            $full_key = $prefix === '' ? $key : $prefix . '.' . $key;

            if (\is_array($value) && $value !== []) {
                // Store the nested array for backward compatibility
                $flat[$full_key] = $value;
                // Also flatten the nested values
                $flat += self::flatten_array($value, $full_key);
            } else {
                $flat[$full_key] = $value;
            }
        }

        return $flat;
    }

    /**
     * Generate PHP code for the cache file.
     *
     * @param array<string, mixed> $flat
     * @return string
     */
    private static function generate_php_code(array $flat): string
    {
        $code = '<?php declare(strict_types=1);' . "\n";
        $code .= '/**' . "\n";
        $code .= ' * Auto-generated compiled configuration cache.' . "\n";
        $code .= ' * DO NOT EDIT — regenerated automatically.' . "\n";
        $code .= ' * Static flat array for O(1) key lookup.' . "\n";
        $code .= ' */' . "\n";
        $code .= "\n";
        $code .= 'return ' . self::export_array($flat) . ';' . "\n";

        return $code;
    }

    /**
     * Export array to PHP code.
     *
     * @param array<string, mixed> $array
     * @return string
     */
    private static function export_array(array $array): string
    {
        $parts = [];

        foreach ($array as $key => $value) {
            $key_part = self::export_key($key);
            $value_part = self::export_value($value);
            $parts[] = $key_part . ' => ' . $value_part;
        }

        return '[' . \implode(', ', $parts) . ']';
    }

    /**
     * Export a key to PHP code.
     */
    private static function export_key(string|int $key): string
    {
        if (\is_int($key)) {
            return (string) $key;
        }

        if (\preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
            return (string) $key;
        }

        return '\'' . \addcslashes((string) $key, '\\\'') . '\'';
    }

    /**
     * Export a value to PHP code.
     */
    private static function export_value(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_int($value)) {
            return (string) $value;
        }

        if (\is_float($value)) {
            if (\is_infinite($value)) {
                return $value > 0 ? 'INF' : '-INF';
            }
            if (\is_nan($value)) {
                return 'NAN';
            }
            return \rtrim(\rtrim(\sprintf('%.10F', $value), '0'), '.');
        }

        if (\is_string($value)) {
            return '\'' . \addcslashes($value, '\\\'') . '\'';
        }

        if (\is_array($value)) {
            return self::export_array($value);
        }

        return 'null';
    }
}
