<?php declare(strict_types=1);

namespace Webkernel\Route\Exception;

use LogicException;

/** @final */
class BadRoute extends LogicException
{
    public static function already_registered(string $route, string $method): self
    {
        return new self(\sprintf('Cannot register two routes matching "%s" for method "%s"', $route, $method));
    }

    public static function named_route_already_defined(string $name): self
    {
        return new self(\sprintf('Cannot register two routes under the name "%s"', $name));
    }

    public static function invalid_route_name(mixed $name): self
    {
        return new self(\sprintf('Route name must be a non-empty string, "%s" given', \var_export($name, true)));
    }

    public static function shadowed_by_variable_route(string $route, string $shadowed_regex, string $method): self
    {
        return new self(\sprintf(
            'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
            $route,
            $shadowed_regex,
            $method,
        ));
    }

    public static function placeholder_already_defined(string $name): self
    {
        return new self(\sprintf('Cannot use the same placeholder "%s" twice', $name));
    }

    public static function variable_with_capture_group(string $regex_part, string $name): self
    {
        return new self(\sprintf('Regex "%s" for parameter "%s" contains a capturing group', $regex_part, $name));
    }
}
