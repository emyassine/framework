<?php declare(strict_types=1);

namespace Webkernel\Route\Compile;

use Webkernel\Route\Exception\BadRoute;

/**
 * MarkBased data generator (FastRoute). One strategy only.
 *
 * @internal
 *
 * @phpstan-import-type ParsedRoute from Pattern
 * @phpstan-type Extra array<string, string|int|bool|float>
 * @phpstan-type StaticRoutes array<string, array<string, array{mixed, Extra}>>
 * @phpstan-type DynamicRouteChunk array{regex: string, routeMap: array<string, array{mixed, array<string, string>, Extra}>}
 * @phpstan-type DynamicRouteChunks list<DynamicRouteChunk>
 * @phpstan-type DynamicRoutes array<string, DynamicRouteChunks>
 * @phpstan-type RouteData array{StaticRoutes, DynamicRoutes}
 */
final class Generator
{
    /** @var StaticRoutes */
    private array $static_routes = [];

    /** @var array<string, array<string, Compiled>> */
    private array $method_to_regex = [];

    /**
     * @param ParsedRoute $route_data
     * @param Extra       $extra
     */
    public function add_route(string $http_method, array $route_data, mixed $handler, array $extra = []): void
    {
        if (count($route_data) === 1 && is_string($route_data[0])) {
            $this->add_static($http_method, $route_data[0], $handler, $extra);

            return;
        }

        $this->add_variable($http_method, $route_data, $handler, $extra);
    }

    /**
     * @return RouteData
     */
    public function get_data(): array
    {
        if ($this->method_to_regex === []) {
            return [$this->static_routes, []];
        }

        $data = [];
        foreach ($this->method_to_regex as $method => $regex_to_routes) {
            $chunk_size = $this->chunk_size(count($regex_to_routes));
            $chunks = array_chunk($regex_to_routes, $chunk_size, true);
            $data[$method] = array_map($this->process_chunk(...), $chunks);
        }

        return [$this->static_routes, $data];
    }

    /**
     * @param Extra $extra
     */
    private function add_static(string $http_method, string $route_str, mixed $handler, array $extra): void
    {
        if (isset($this->static_routes[$http_method][$route_str])) {
            throw BadRoute::already_registered($route_str, $http_method);
        }

        if (isset($this->method_to_regex[$http_method])) {
            foreach ($this->method_to_regex[$http_method] as $route) {
                if ($route->matches($route_str)) {
                    throw BadRoute::shadowed_by_variable_route($route_str, $route->regex, $http_method);
                }
            }
        }

        $this->static_routes[$http_method][$route_str] = [$handler, $extra];
    }

    /**
     * @param ParsedRoute $route_data
     * @param Extra       $extra
     */
    private function add_variable(string $http_method, array $route_data, mixed $handler, array $extra): void
    {
        $compiled = new Compiled($http_method, $route_data, $handler, $extra);
        $regex = $compiled->regex;

        if (isset($this->method_to_regex[$http_method][$regex])) {
            throw BadRoute::already_registered($regex, $http_method);
        }

        $this->method_to_regex[$http_method][$regex] = $compiled;
    }

    /** @return positive-int */
    private function chunk_size(int $count): int
    {
        $num_parts = max(1, (int) round($count / 30));
        $size = (int) ceil($count / $num_parts);
        assert($size > 0);

        return $size;
    }

    /**
     * @param array<string, Compiled> $regex_to_routes
     *
     * @return DynamicRouteChunk
     */
    private function process_chunk(array $regex_to_routes): array
    {
        $route_map = [];
        $regexes = [];
        $mark = 'a';

        foreach ($regex_to_routes as $regex => $route) {
            $regexes[] = $regex.'(*MARK:'.$mark.')';
            $route_map[$mark] = [$route->handler, $route->variables, $route->extra];
            ++$mark;
        }

        return [
            'regex' => '~^(?|'.implode('|', $regexes).')$~',
            'routeMap' => $route_map,
        ];
    }
}
