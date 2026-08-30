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

    /**
     * View directories keyed by namespace (`billing::invoices.index`).
     *
     * //> Several packages may share one namespace. Dump concatenates their dirs.
     *
     * @var array<string, string|list<string>>
     */
    public const VIEWS = [];

    /**
     * Component directories keyed by namespace (`<x-billing::card />`).
     *
     * //> Same merge rule as VIEWS.
     *
     * @var array<string, string|list<string>>
     */
    public const COMPONENTS = [];

    /**
     * Translation directories (`{dir}/{locale}/translations.php`).
     *
     * @var list<string>
     */
    public const LANG_PATH = [];

    /** @var list<class-string> */
    public const COMMANDS = [];

    /** @var list<class-string> */
    public const PANELS = [];

    /**
     * Migration directories. `webkernel migrate` discovers these from providers.
     *
     * @var list<string>
     */
    public const MIGRATIONS = [];

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
}
