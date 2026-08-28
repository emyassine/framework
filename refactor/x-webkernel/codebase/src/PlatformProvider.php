<?php declare(strict_types=1);

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
     * @return list<mixed>
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
