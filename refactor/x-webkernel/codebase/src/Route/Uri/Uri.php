<?php declare(strict_types=1);

namespace Webkernel\Route\Uri;

use Webkernel\Route\Compile\Pattern;
use Webkernel\Route\Route;

/**
 * Named-route URI builder (FastRoute GenerateUri, string only — no PSR-7).
 *
 * @internal
 *
 * @phpstan-import-type ParsedRoute from Pattern
 * @phpstan-import-type NamedRoutes from Route
 */
final class Uri
{
    /** @param NamedRoutes $named */
    public function __construct(
        private readonly array $named,
    ) {
    }

    /**
     * @param array<string, string> $substitutions
     */
    public function for_name(string $name, array $substitutions = []): string
    {
        if (! array_key_exists($name, $this->named)) {
            throw UriException::undefined($name);
        }

        $missing = [];
        foreach ($this->named[$name] as $parsed_route) {
            $missing = $this->missing($parsed_route, $substitutions);
            if ($missing === []) {
                return $this->build($name, $parsed_route, $substitutions);
            }
        }

        assert($missing !== []);

        throw UriException::insufficient($name, $missing, array_keys($substitutions));
    }

    /**
     * @param ParsedRoute           $parts
     * @param array<string, string> $substitutions
     *
     * @return list<string>
     */
    private function missing(array $parts, array $substitutions): array
    {
        $missing = [];
        foreach ($parts as $part) {
            if (is_string($part) || array_key_exists($part[0], $substitutions)) {
                continue;
            }
            $missing[] = $part[0];
        }

        return $missing;
    }

    /**
     * @param ParsedRoute           $parsed_route
     * @param array<string, string> $substitutions
     */
    private function build(string $route, array $parsed_route, array $substitutions): string
    {
        $path = '';
        foreach ($parsed_route as $part) {
            if (is_string($part)) {
                $path .= $part;
                continue;
            }

            [$parameter, $regex] = $part;
            if (preg_match('~^'.$regex.'$~u', $substitutions[$parameter]) !== 1) {
                throw UriException::parameter_mismatch($route, $parameter, $regex);
            }
            $path .= $substitutions[$parameter];
        }

        assert($path !== '');

        return $path;
    }
}
