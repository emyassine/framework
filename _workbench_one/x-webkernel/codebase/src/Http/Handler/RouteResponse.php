<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Webkernel\Route\Dispatch\Dispatcher;
use Webkernel\Route\Dispatch\MethodNotAllowed;
use Webkernel\Route\Dispatch\NotMatched;

/**
 * Dispatch through the compiled route map. Does not boot WebApp or parse routes.
 */
final class RouteResponse
{
    /**
     * @param array{0?: mixed, 1?: mixed} $route_map
     */
    public static function from_map(array $route_map, string $method, string $content_type = 'text/html; charset=UTF-8'): Response
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (false !== $q = strpos($uri, '?')) {
            $uri = substr($uri, 0, $q);
        }
        $out = self::dispatch_map($route_map, strtoupper($method), rawurldecode($uri));

        return self::wrap($out, $content_type);
    }

    public static function dispatch(string $method, string $content_type = 'text/html; charset=UTF-8'): Response
    {
        $out = \Webkernel\Route\Route::dispatch($method);

        return self::wrap($out, $content_type);
    }

    private static function wrap(mixed $out, string $content_type): Response
    {
        $status = http_response_code();
        if (! is_int($status) || $status < 100) {
            $status = 200;
        }
        if ($out instanceof PsrResponse) {
            $headers = [];
            foreach ($out->getHeaders() as $name => $values) {
                $headers[$name] = implode(', ', $values);
            }

            return new Response($out->getStatusCode(), $headers, (string) $out->getBody());
        }
        if ($out instanceof \Stringable) {
            $out = (string) $out;
        }

        return new Response($status, ['Content-Type' => $content_type], (string) $out);
    }

    /**
     * @param array{0?: mixed, 1?: mixed} $data
     */
    private static function dispatch_map(array $data, string $method, string $uri): mixed
    {
        $static = is_array($data[0] ?? null) ? $data[0] : [];
        foreach ([$method, $method === 'HEAD' ? 'GET' : null, '*'] as $try) {
            if (! is_string($try)) {
                continue;
            }
            $row = $static[$try][$uri] ?? null;
            if (! is_array($row) || ! array_key_exists(0, $row)) {
                continue;
            }

            return self::invoke($row[0], self::domain_vars(is_array($row[1] ?? null) ? $row[1] : [], []));
        }

        $result = (new Dispatcher($data))->dispatch($method, $uri);
        if ($result instanceof NotMatched) {
            http_response_code(404);

            return '';
        }
        if ($result instanceof MethodNotAllowed) {
            http_response_code(405);
            header('Allow: '.implode(', ', $result->allowed));

            return '';
        }

        return self::invoke($result->handler, self::domain_vars($result->extra, $result->variables));
    }

    /**
     * @param array<string, mixed> $extra
     * @param array<string, string> $vars
     * @return array<string, string>
     */
    private static function domain_vars(array $extra, array $vars): array
    {
        $domain = $extra['_domain'] ?? null;
        if (! is_string($domain) || $domain === '' || ! str_contains($domain, '{')) {
            return $vars;
        }

        return \Webkernel\Route\Binding::domain_variables($extra, self::request_host()) + $vars;
    }

    /**
     * @param array<string, string> $vars
     */
    private static function invoke(mixed $handler, array $vars): mixed
    {
        if (is_callable($handler)) {
            return $handler(...self::accepted_vars($handler, $vars));
        }
        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0]) && is_string($handler[1]) && class_exists($handler[0])) {
            $controller = new $handler[0]();
            $method = $handler[1];
            if (! is_callable([$controller, $method])) {
                throw new \RuntimeException('Invalid route action.');
            }

            return $controller->{$method}(...$vars);
        }
        if (is_string($handler) && class_exists($handler)) {
            $page = new $handler();
            if (is_callable($page)) {
                return $page(...$vars);
            }
        }

        throw new \RuntimeException('Invalid route action.');
    }

    /**
     * @param array<string, string> $vars
     * @return array<string, string>
     */
    private static function accepted_vars(callable $handler, array $vars): array
    {
        if ($vars === []) {
            return [];
        }
        $ref = $handler instanceof \Closure
            ? new \ReflectionFunction($handler)
            : new \ReflectionFunction($handler(...));
        if ($ref->getNumberOfParameters() === 0) {
            return [];
        }
        $out = [];
        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $vars)) {
                $out[$name] = $vars[$name];
            }
        }

        return $out;
    }

    private static function request_host(): string
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            return '';
        }
        if (str_starts_with($host, '[')) {
            $end = strpos($host, ']');

            return $end === false ? $host : substr($host, 0, $end + 1);
        }
        $colon = strrpos($host, ':');
        if ($colon === false) {
            return $host;
        }

        return substr($host, 0, $colon);
    }
}
