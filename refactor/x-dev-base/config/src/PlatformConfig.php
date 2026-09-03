<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config;

use Webkernel\PlatformProvider;
use Webkernel\Config\Enums\ConfigPath;
use Webkernel\Config\Guards\ConfigGuard;
use Webkernel\Config\Exceptions\ConfigGuardException;

/**
 * Platform configuration manager.
 *
 * Handles the boot sequence, path resolution, provider package discovery,
 * and guarded runtime updates.
 *
 * Dependencies (supplied externally — no hardcodes inside this class):
 *  - platform_path()      global function provided by the Composer plugin
 *  - vendor_path()        global function provided by the Composer plugin
 *  - PlatformProvider     abstract base for package providers
 *
 * The vendor directory path is NEVER hardcoded here. It comes from:
 *  1. The $vendor_path constructor argument (explicit injection), OR
 *  2. The vendor_path() global function (Composer plugin), OR
 *  3. $root_path . '/vendor'  (last-resort fallback only)
 *
 * --- KEY FORMAT ---
 * Every config file is stored in the tree under its filename stem so that
 * dot-notation always starts with the filename:
 *
 *   config/platform.php       → $tree['platform'][...]
 *   config/app.php            → $tree['app'][...]
 *   config/platform-paths.php → $tree['platform-paths'][...]
 *
 * Access examples:
 *   Config::get('platform.id')
 *   Config::get('app.name')
 *   Config::get('database.connections.mysql.host')
 */
class PlatformConfig extends BaseConfig
{
    private ?ConfigGuard $guard;

    public function __construct(
        string $platform_path,
        protected string $vendor_path = '',
        ?ConfigGuard $guard = null,
    ) {
        parent::__construct($platform_path);

        // Resolve vendor path — never hardcoded, always runtime-derived
        if ($this->vendor_path === '') {
            $this->vendor_path = \function_exists('vendor_path')
                ? \vendor_path()
                : $this->root_path . '/vendor';
        } else {
            $this->vendor_path = \rtrim($this->vendor_path, '/\\');
        }

        $this->guard = $guard;
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function get_vendor_path(): string
    {
        return $this->vendor_path;
    }

    public function get_guard(): ?ConfigGuard
    {
        return $this->guard;
    }

    /**
     * Replace (or remove) the guard after construction.
     *
     * Passing null removes all protection.
     */
    public function set_guard(?ConfigGuard $guard): static
    {
        $this->guard = $guard;
        return $this;
    }

    public function boot(): static
    {
        if ($this->booted) {
            return $this;
        }

        $tree = [];

        // 1. Foundation paths first — avoids bootstrap chicken-and-egg deadlock.
        //    Wrapped under 'platform-paths' key:
        //      config('platform-paths.some_key')
        $paths_file = $this->resolve_file(ConfigPath::PlatformPaths->value);
        $tree       = \array_replace_recursive($tree, self::require_file_under_key($paths_file));

        // 2. Discover and merge package provider configurations.
        //    Each provider file is wrapped under its own stem key.
        $tree = \array_replace_recursive($tree, $this->load_package_configs());

        // 3. Platform and application base configurations, each under their stem:
        //      config/platform.php → $tree['platform']
        //      config/app.php      → $tree['app']
        foreach ([ConfigPath::PlatformConfig->value, ConfigPath::AppConfig->value] as $rel) {
            $abs  = $this->resolve_file($rel);
            $tree = \array_replace_recursive($tree, self::require_file_under_key($abs));
        }

        // 4. Layer runtime overrides (written by set()).
        //    The runtime file already stores dot-expanded keys (written by set_dot()),
        //    so it is merged FLAT — no extra stem wrapping — directly into the tree.
        $runtime_file = $this->resolve_file(ConfigPath::RuntimeConfig->value);
        $tree         = \array_replace_recursive($tree, self::require_array($runtime_file));

        $this->tree   = $tree;
        $this->booted = true;

        return $this;
    }

    /**
     * Set a config key at runtime, persisting it to the runtime config file.
     *
     * The key must use the canonical dot-notation format: <filename>.<key>.<nested_key>
     * e.g. Config::set('platform.debug', true)
     *
     * @throws ConfigGuardException when the key (or its prefix) is protected.
     */
    public function set(string $key, mixed $value): static
    {
        $this->guard?->assert($key);

        if (! $this->booted) {
            $this->boot();
        }

        $runtime_file = $this->resolve_file(ConfigPath::RuntimeConfig->value);
        $current      = self::require_array($runtime_file);
        $next         = \array_replace_recursive($current, self::dot_to_tree($key, $value));
        ConfigWriter::write($runtime_file, $next);
        $this->set_dot($this->tree, $key, $value);

        return $this;
    }

    /**
     * Resolve a platform-relative path to absolute.
     * Input is a config key whose value is a relative path,
     * OR a ConfigPath enum case.
     */
    public function path(string|ConfigPath $key, string $sub_path = ''): string
    {
        $path_key = $key instanceof ConfigPath ? $key->value : $key;
        $resolved = $this->get($path_key);

        if (! \is_string($resolved) || $resolved === '') {
            $resolved = $path_key;
        }

        $full = $this->resolve_file($resolved);

        if ($sub_path !== '') {
            $full .= '/' . \ltrim($sub_path, '/\\');
        }

        return $full;
    }

    /**
     * Resolve a platform-relative file path to absolute.
     */
    public function resolve_file(string $relative_path): string
    {
        return $this->root_path . '/' . \ltrim($relative_path, '/\\');
    }

    // -------------------------------------------------------------------------
    // Internal boot helpers
    // -------------------------------------------------------------------------

    /**
     * Load config arrays declared by all registered PlatformProvider packages.
     *
     * Each provider config file is wrapped under its filename stem, exactly
     * like the platform and app config files in boot().
     *
     * Provider manifest is looked up relative to vendor_path() and never hardcoded.
     *
     * @return array<string, mixed>
     */
    protected function load_package_configs(): array
    {
        // ProvidersManifest value is relative to vendor_path(), not platform_path()
        $manifest = $this->vendor_path . '/' . ConfigPath::ProvidersManifest->value;

        if (! \is_file($manifest)) {
            return [];
        }

        $providers = require $manifest;

        if (! \is_array($providers)) {
            return [];
        }

        $tree = [];

        foreach ($providers as $class) {
            if (
                ! \is_string($class)
                || ! \class_exists($class)
                || ! \is_a($class, PlatformProvider::class, true)
            ) {
                continue;
            }

            foreach ($class::declaration('CONFIG') as $path) {
                if (\is_string($path) && $path !== '') {
                    // Wrap under the file's stem key, consistent with boot()
                    $tree = \array_replace_recursive($tree, self::require_file_under_key($path));
                }
            }
        }

        return $tree;
    }
}
