<?php declare(strict_types=1);

namespace Webkernel\Route\Uri;

use LogicException;

final class UriException extends LogicException
{
    public static function undefined(string $name): self
    {
        return new self('There is no route with name "'.$name.'" defined');
    }

    public static function parameter_mismatch(string $route, string $parameter, string $pattern): self
    {
        return new self(\sprintf(
            'Route "%s" expects the parameter [%s] to match the regex `%s`',
            $route,
            $parameter,
            $pattern,
        ));
    }

    /**
     * @param non-empty-list<string> $missing
     * @param list<string>           $given
     */
    public static function insufficient(string $route, array $missing, array $given): self
    {
        return new self(\sprintf(
            'Route "%s" expects at least parameter values for [%s], but received %s',
            $route,
            \implode(',', $missing),
            $given === [] ? 'none' : '['.\implode(',', $given).']',
        ));
    }
}
