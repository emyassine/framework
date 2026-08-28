<?php declare(strict_types=1);

namespace Webkernel\Route\Dispatch;

use Webkernel\Route\Compile\Generator;

/**
 * MarkBased dispatcher (FastRoute). One strategy only.
 *
 * @internal
 *
 * @phpstan-import-type StaticRoutes from Generator
 * @phpstan-import-type DynamicRouteChunks from Generator
 * @phpstan-import-type DynamicRoutes from Generator
 * @phpstan-import-type RouteData from Generator
 */
final class Dispatcher
{
    /** @var StaticRoutes */
    private array $static_map;

    /** @var DynamicRoutes */
    private array $variable_data;

    /** @param RouteData $data */
    public function __construct(array $data)
    {
        [$this->static_map, $this->variable_data] = $data;
    }

    public function dispatch(string $http_method, string $uri): Matched|NotMatched|MethodNotAllowed
    {
        $hit = $this->match_method($http_method, $uri);
        if ($hit !== null) {
            return $hit;
        }

        if ($http_method === 'HEAD') {
            $hit = $this->match_method('GET', $uri);
            if ($hit !== null) {
                return $hit;
            }
        }

        $hit = $this->match_method('*', $uri);
        if ($hit !== null) {
            return $hit;
        }

        $allowed = [];
        foreach ($this->static_map as $method => $uri_map) {
            if ($method !== $http_method && isset($uri_map[$uri])) {
                $allowed[] = $method;
            }
        }
        foreach ($this->variable_data as $method => $route_data) {
            if ($method === $http_method) {
                continue;
            }
            if ($this->dispatch_variable($route_data, $uri) !== null) {
                $allowed[] = $method;
            }
        }

        if ($allowed !== []) {
            /** @var non-empty-list<string> $allowed */
            return new MethodNotAllowed($allowed);
        }

        return new NotMatched();
    }

    private function match_method(string $http_method, string $uri): ?Matched
    {
        if (isset($this->static_map[$http_method][$uri])) {
            [$handler, $extra] = $this->static_map[$http_method][$uri];

            return new Matched($handler, [], $extra);
        }

        if (isset($this->variable_data[$http_method])) {
            return $this->dispatch_variable($this->variable_data[$http_method], $uri);
        }

        return null;
    }

    /**
     * @param DynamicRouteChunks $route_data
     */
    private function dispatch_variable(array $route_data, string $uri): ?Matched
    {
        foreach ($route_data as $data) {
            if (preg_match($data['regex'], $uri, $matches) !== 1) {
                continue;
            }

            [$handler, $var_names, $extra] = $data['routeMap'][$matches['MARK']];
            $vars = [];
            $i = 0;
            foreach ($var_names as $var_name) {
                $vars[$var_name] = $matches[++$i];
            }

            return new Matched($handler, $vars, $extra);
        }

        return null;
    }
}
