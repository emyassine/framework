<?php declare(strict_types=1);

namespace Webkernel\Route\Compile;

/**
 * One variable route after parse: regex + placeholder names.
 *
 * @internal
 *
 * @phpstan-import-type Extra from Generator
 * @phpstan-import-type ParsedRoute from Pattern
 */
final class Compiled
{
    public readonly string $regex;

    /** @var array<string, string> */
    public readonly array $variables;

    /**
     * @param ParsedRoute $route_data
     * @param Extra       $extra
     */
    public function __construct(
        public readonly string $http_method,
        array $route_data,
        public readonly mixed $handler,
        public readonly array $extra,
    ) {
        [$this->regex, $this->variables] = self::extract_regex($route_data);
    }

    /**
     * @param ParsedRoute $route_data
     *
     * @return array{string, array<string, string>}
     */
    private static function extract_regex(array $route_data): array
    {
        $regex = '';
        $variables = [];

        foreach ($route_data as $part) {
            if (\is_string($part)) {
                $regex .= \preg_quote($part, '~');
                continue;
            }

            [$var_name, $regex_part] = $part;
            $variables[$var_name] = $var_name;
            $regex .= '('.$regex_part.')';
        }

        return [$regex, $variables];
    }

    public function matches(string $str): bool
    {
        return (bool) \preg_match('~^'.$this->regex.'$~', $str);
    }
}
