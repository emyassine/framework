<?php declare(strict_types=1);

namespace Webkernel\Route\Compile;

use Webkernel\Route\Exception\BadRoute;

/**
 * Parses "/user/{name}[/{id:[0-9]+}]" into static / variable parts.
 *
 * @phpstan-type ParsedRoute list<string|array{string, string}>
 * @phpstan-type ParsedRoutes list<ParsedRoute>
 * @final
 */
class Pattern
{
    public const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

    public const DEFAULT_DISPATCH_REGEX = '[^/]+';

    private const CAPTURING_GROUPS_REGEX = '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \? (?! <(?![!=]) | P< | \' )
                  | \*
                )
            ~x';

    /**
     * @return ParsedRoutes
     */
    public function parse(string $route): array
    {
        $without_closing = \rtrim($route, ']');
        $num_optionals = \strlen($route) - \strlen($without_closing);

        $segments = \preg_split('~'.self::VARIABLE_REGEX.'(*SKIP)(*F) | \[~x', $without_closing);
        \assert(\is_array($segments));

        if ($num_optionals !== \count($segments) - 1) {
            if (\preg_match('~'.self::VARIABLE_REGEX.'(*SKIP)(*F) | \]~x', $without_closing) === 1) {
                throw new BadRoute('Optional segments can only occur at the end of a route');
            }

            throw new BadRoute("Number of opening '[' and closing ']' does not match");
        }

        $current = '';
        $parsed = [];

        foreach ($segments as $n => $segment) {
            if ($segment === '' && $n !== 0) {
                throw new BadRoute('Empty optional part');
            }

            $current .= $segment;
            $parsed[] = $this->placeholders($current);
        }

        return $parsed;
    }

    /**
     * @return ParsedRoute
     */
    private function placeholders(string $route): array
    {
        if ((int) \preg_match_all('~'.self::VARIABLE_REGEX.'~x', $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === 0) {
            return [$route];
        }

        $offset = 0;
        $route_data = [];
        $names = [];

        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $route_data[] = \substr($route, $offset, $set[0][1] - $offset);
            }

            if (\in_array($set[1][0], $names, true)) {
                throw BadRoute::placeholder_already_defined($set[1][0]);
            }

            if (isset($set[2])) {
                $this->guard_capturing_group(\trim($set[2][0]), $set[1][0]);
            }

            $names[] = $set[1][0];
            $route_data[] = [
                $set[1][0],
                isset($set[2]) ? \trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX,
            ];
            $offset = $set[0][1] + \strlen($set[0][0]);
        }

        if ($offset !== \strlen($route)) {
            $route_data[] = \substr($route, $offset);
        }

        return $route_data;
    }

    private function guard_capturing_group(string $regex, string $variable_name): void
    {
        if (! \str_contains($regex, '(')) {
            return;
        }
        if (\preg_match(self::CAPTURING_GROUPS_REGEX, $regex) !== 1) {
            return;
        }

        throw BadRoute::variable_with_capture_group($regex, $variable_name);
    }
}
