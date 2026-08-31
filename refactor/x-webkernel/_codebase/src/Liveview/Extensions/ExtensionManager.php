<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview\Extensions;

/**
 * Manager for HTMX extensions.
 *
 * //> Register and manage HTMX extensions for use with Liveview components.
 */
final class ExtensionManager
{
    /** @var array<string, class-string<Extension>> */
    private static array $extensions = [];

    /** @var array<string, bool> */
    private static array $enabled = [];

    /**
     * Register an extension.
     *
     * @param class-string<Extension> $extension_class
     * @return void
     */
    public static function register(string $extension_class): void
    {
        $name = $extension_class::name();
        static::$extensions[$name] = $extension_class;
        static::$enabled[$name] = $extension_class::enabled_by_default();
    }

    /**
     * Enable an extension.
     *
     * @param string $name
     * @return void
     */
    public static function enable(string $name): void
    {
        static::$enabled[$name] = true;
    }

    /**
     * Disable an extension.
     *
     * @param string $name
     * @return void
     */
    public static function disable(string $name): void
    {
        static::$enabled[$name] = false;
    }

    /**
     * Check if an extension is enabled.
     *
     * @param string $name
     * @return bool
     */
    public static function is_enabled(string $name): bool
    {
        return static::$enabled[$name] ?? false;
    }

    /**
     * Get all registered extensions.
     *
     * @return array<string, class-string<Extension>>
     */
    public static function all(): array
    {
        return static::$extensions;
    }

    /**
     * Get all enabled extensions.
     *
     * @return array<string, class-string<Extension>>
     */
    public static function enabled(): array
    {
        $result = [];
        foreach (static::$extensions as $name => $class) {
            if (static::is_enabled($name)) {
                $result[$name] = $class;
            }
        }
        return $result;
    }

    /**
     * Get the script tags for all enabled extensions.
     *
     * @return string
     */
    public static function script_tags(): string
    {
        $tags = [];
        foreach (static::enabled() as $name => $class) {
            $tags[] = $class::script_tag();
        }
        return implode('\n', $tags);
    }

    /**
     * Get the preload tags for all enabled extensions.
     *
     * @return string
     */
    public static function preload_tags(): string
    {
        $tags = [];
        foreach (static::enabled() as $name => $class) {
            $tags[] = $class::preload_tag();
        }
        return implode('\n', $tags);
    }

    /**
     * Get the HTMX extensions attribute value for the htmx config.
     *
     * @return string
     */
    public static function extensions_attr(): string
    {
        return implode(',', array_keys(static::enabled()));
    }

    /**
     * Reset the manager (useful for testing).
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$extensions = [];
        static::$enabled = [];
    }
}
