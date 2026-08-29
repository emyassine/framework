<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

/**
 * Package declaration read by dump-autoload. Constants only on the request.
 * Dump may call a static method of the same name when a path cannot be a constant.
 */
abstract class PlatformProvider
{
    /** @var list<string> */
    public const ROUTES = [];

    /** @var list<string> */
    public const VIEWS = [];

    /** @var list<string> */
    public const COMPONENTS = [];

    /** @var list<class-string> */
    public const COMMANDS = [];

    /** @var list<class-string> */
    public const PANELS = [];

    /**
     * View and component namespace (`billing::invoices.index`, `<x-billing::card />`).
     *
     * //> Empty falls back to composer extra.webkernel.prefix.
     * //> Several packages may share one namespace. Dump concatenates their dirs.
     *
     * @var string
     */
    public const VIEW_NAMESPACE = '';

    /**
     * @return list<mixed>
     */
    public static function declaration(string $constant): array
    {
        $method = \strtolower($constant);
        if (\defined(static::class.'::'.$constant)) {
            $value = \constant(static::class.'::'.$constant);
            if (\is_array($value) && $value !== []) {
                return $value;
            }
        }
        if (\is_callable([static::class, $method])) {
            $value = static::{$method}();

            return \is_array($value) ? $value : [];
        }

        return [];
    }

    /**
     * @param $fallback string
     *
     * @return string
     */
    public static function view_namespace(string $fallback = ''): string
    {
        if (\defined(static::class.'::VIEW_NAMESPACE')) {
            $namespace = \constant(static::class.'::VIEW_NAMESPACE');
            if (\is_string($namespace) && $namespace !== '') {
                return $namespace;
            }
        }

        return $fallback;
    }
}
