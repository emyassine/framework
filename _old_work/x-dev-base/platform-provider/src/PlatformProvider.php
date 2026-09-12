<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

abstract class PlatformProvider
{
    /** @var list<string> */
    public const ROUTES = [];

    /** @var array<string, string|list<string>> */
    public const VIEWS = [];

    /** @var array<string, string|list<string>> */
    public const COMPONENTS = [];

    /** @var list<string> */
    public const LANG_PATH = [];

    /** @var list<class-string> */
    public const COMMANDS = [];

    /** @var array<string, array{path:string, publish:string, tag?:string}>|list<string> */
    public const CONFIG = [];

    /** @var list<class-string> */
    public const PANELS = [];

    /** @var list<string> */
    public const MIGRATIONS = [];

    /**
     * @return list<mixed>|array<string, mixed>
     */
    public static function declaration(string $constant): array
    {
        $method = strtolower($constant);
        if (defined(static::class.'::'.$constant)) {
            $value = constant(static::class.'::'.$constant);
            if (is_array($value) && $value !== []) {
                return $value;
            }
        }
        if (is_callable([static::class, $method])) {
            $value = static::{$method}();

            return is_array($value) ? $value : [];
        }

        return [];
    }
}
