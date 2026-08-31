<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview\Extensions;

/**
 * Base class for HTMX extensions.
 *
 * //> Extensions add capabilities to HTMX like SSE, WebSockets, file downloads, etc.
 * //> See: https://four.htmx.org/extensions
 */
abstract class Extension
{
    /**
     * The extension name.
     */
    protected static string $name;

    /**
     * The extension script URL.
     */
    protected static string $script_url;

    /**
     * Whether the extension is enabled by default.
     */
    protected static bool $enabled_by_default = false;

    /**
     * Get the extension name.
     *
     * @return string
     */
    final public static function name(): string
    {
        return static::$name;
    }

    /**
     * Get the extension script URL.
     *
     * @return string
     */
    final public static function script_url(): string
    {
        return static::$script_url;
    }

    /**
     * Check if the extension is enabled by default.
     *
     * @return bool
     */
    final public static function enabled_by_default(): bool
    {
        return static::$enabled_by_default;
    }

    /**
     * Get the script tag for this extension.
     *
     * @return string
     */
    public static function script_tag(): string
    {
        return '<script src="'.static::script_url().'"></script>';
    }

    /**
     * Get the preload tag for this extension.
     *
     * @return string
     */
    public static function preload_tag(): string
    {
        return '<link rel="preload" href="'.static::script_url().'" as="script">';
    }
}
