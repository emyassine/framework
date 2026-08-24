<?php declare(strict_types=1);

namespace Webkernel\Route;

use Webkernel\Composables\ComposableContract;
use Webkernel\Route\Action\RedirectAction;
use Webkernel\Route\Action\ViewAction;
use Webkernel\Route\Compile\Cache;
use Webkernel\Route\Compile\Generator;
use Webkernel\Route\Compile\Pattern;
use Webkernel\Route\Dispatch\Dispatcher;
use Webkernel\Route\Dispatch\Matched;
use Webkernel\Route\Dispatch\MethodNotAllowed;
use Webkernel\Route\Dispatch\NotMatched;
use Webkernel\Route\Group\PendingGroup;
use Webkernel\Route\Uri\Uri;

/**
 * Application router. FastRoute MarkBased engine, owned in this package.
 *
 * Registration is fluent: Route::view(...)->name('dashboard'). Extra keys still
 * bind a URI to the platform tree. Permission and middleware are recorded, not
 * enforced, until auth exists.
 *
 * @phpstan-import-type Extra from Generator
 * @phpstan-import-type RouteData from Generator
 * @phpstan-import-type ParsedRoutes from Pattern
 * @phpstan-type NamedRoutes array<string, ParsedRoutes>
 */
final class Route implements ComposableContract
{
    public const NAME = '_name';
    public const PANEL = '_panel';
    public const CLUSTER = '_cluster';
    public const RESOURCE = '_resource';
    public const PAGE = '_page';
    public const PERMISSION = '_permission';
    public const REGEX = '_route';
    public const VIEW = '_view';
    public const DOMAIN = '_domain';
    public const MIDDLEWARE = '_middleware';

    private static bool $declared_loaded = false;
    private string $group_prefix = '';
    private string $group_name = '';
    private string $group_domain = '';

    /** @var list<string> */
    private array $group_middleware = [];

    /** @var array<string, string> */
    private array $group_wheres = [];

    /** @var list<Binding> */
    private array $bindings = [];

    private ?Uri $uris = null;

    public static function api_name(): string
    {
        return 'route';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public static function app(): self
    {
        $route = webapp()->route();
        $route->ensure_declared();

        return $route;
    }

    /** Reset process singleton (tests). Does not reload host route files. */
    public static function flush(): void
    {
        self::$declared_loaded = true;
        webapp()->container()->forget(self::class);
    }

    public static function get(string $uri, mixed $action): Binding
    {
        return self::app()->add(['GET', 'HEAD'], $uri, $action);
    }

    public static function post(string $uri, mixed $action): Binding
    {
        return self::app()->add('POST', $uri, $action);
    }

    public static function put(string $uri, mixed $action): Binding
    {
        return self::app()->add('PUT', $uri, $action);
    }

    public static function patch(string $uri, mixed $action): Binding
    {
        return self::app()->add('PATCH', $uri, $action);
    }

    public static function delete(string $uri, mixed $action): Binding
    {
        return self::app()->add('DELETE', $uri, $action);
    }

    public static function options(string $uri, mixed $action): Binding
    {
        return self::app()->add('OPTIONS', $uri, $action);
    }

    public static function query(string $uri, mixed $action): Binding
    {
        return self::app()->add('QUERY', $uri, $action);
    }

    public static function any(string $uri, mixed $action): Binding
    {
        return self::app()->add('*', $uri, $action);
    }

    /**
     * @param list<string> $methods
     */
    public static function match(array $methods, string $uri, mixed $action): Binding
    {
        return self::app()->add(array_map(strtoupper(...), $methods), $uri, $action);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function view(string $uri, string $view, array $data = [], int $status = 200): Binding
    {
        return self::get($uri, new ViewAction($view, $data, $status))->as_view($view);
    }

    public static function redirect(string $uri, string $destination, int $status = 302): Binding
    {
        return self::get($uri, new RedirectAction($destination, $status));
    }

    public static function permanent_redirect(string $uri, string $destination): Binding
    {
        return self::redirect($uri, $destination, 301);
    }

    public static function fallback(mixed $action): Binding
    {
        return self::any('/{path:.*}', $action);
    }

    public static function prefix(string $prefix): PendingGroup
    {
        return (new PendingGroup(self::app()))->prefix($prefix);
    }

    public static function name(string $name): PendingGroup
    {
        return (new PendingGroup(self::app()))->name($name);
    }

    /**
     * @param  string|list<string>  $middleware
     */
    public static function middleware(string|array $middleware): PendingGroup
    {
        return (new PendingGroup(self::app()))->middleware($middleware);
    }

    public static function domain(string $domain): PendingGroup
    {
        return (new PendingGroup(self::app()))->domain($domain);
    }

    /**
     * @param array{prefix?: string, as?: string, name?: string, domain?: string, middleware?: string|list<string>, where?: array<string, string>}|callable $attributes
     */
    public static function group(array|callable $attributes, ?callable $routes = null): void
    {
        if (is_callable($attributes)) {
            $attributes();

            return;
        }
        if ($routes === null) {
            throw new \InvalidArgumentException('Route group requires a callback.');
        }
        PendingGroup::from_attributes(self::app(), $attributes)->group($routes);
    }

    /**
     * @param array<string, string> $parameters
     */
    public static function url(string $name, array $parameters = []): string
    {
        return self::app()->uri_generator()->for_name($name, $parameters);
    }

    /**
     * Build FastRoute data from declared route files. Cold path only.
     *
     * @return RouteData
     */
    public static function compile_for_cache(string $host = ''): array
    {
        $route = self::app();

        return $route->build_data($host);
    }

    public static function dispatch(\Psr\Http\Message\ServerRequestInterface|string|null $method = null, ?string $uri = null, ?string $host = null): mixed
    {
        if ($method instanceof \Psr\Http\Message\ServerRequestInterface) {
            $request = $method;
            $body = self::dispatch($request->getMethod(), $request->getUri()->getPath(), $request->getUri()->getHost());
            if ($body instanceof \Psr\Http\Message\ResponseInterface) {
                return $body;
            }

            return webapp()->response()->html((string) $body);
        }
        $method ??= $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri ??= $_SERVER['REQUEST_URI'] ?? '/';
        if (false !== $q = strpos($uri, '?')) {
            $uri = substr($uri, 0, $q);
        }
        $uri = rawurldecode($uri);
        $host = self::normalize_host($host ?? (string) ($_SERVER['HTTP_HOST'] ?? ''));

        $route = webapp()->route();
        $data = $route->bindings === [] ? Cache::read() : null;
        $data = is_array($data) ? $data : $route->compiled_data($host);
        $result = self::match_static($data, $method, $uri)
            ?? (new Dispatcher($data))->dispatch($method, $uri);

        if ($result instanceof NotMatched) {
            http_response_code(404);

            return '';
        }
        if ($result instanceof MethodNotAllowed) {
            http_response_code(405);
            header('Allow: '.implode(', ', $result->allowed));

            return '';
        }

        $vars = $result->variables;
        $domain = $result->extra[self::DOMAIN] ?? null;
        if (is_string($domain) && $domain !== '' && str_contains($domain, '{')) {
            $vars = Binding::domain_variables($result->extra, $host) + $vars;
        }
        $out = self::invoke($result->handler, $vars);
        if ($out instanceof \Stringable) {
            return (string) $out;
        }

        return $out;
    }

    public static function run(): void
    {
        echo self::dispatch();
    }

    /**
     * Registered routes, methods merged per URI (GET|HEAD, …).
     *
     * @return list<array{methods: list<string>, uri: string, name: string, action: string}>
     */
    public static function list(): array
    {
        return self::app()->listed();
    }

    public function invalidate(): void
    {
        $this->uris = null;
    }

    public function push_group(PendingGroup $group, callable $routes): void
    {
        $previous_prefix = $this->group_prefix;
        $previous_name = $this->group_name;
        $previous_domain = $this->group_domain;
        $previous_middleware = $this->group_middleware;
        $previous_wheres = $this->group_wheres;

        $this->group_prefix = $group->prefix_value() === ''
            ? $previous_prefix
            : $this->join_uri($previous_prefix, $group->prefix_value());
        $this->group_name = $previous_name.$group->name_value();
        $this->group_domain = $group->domain_value() !== '' ? $group->domain_value() : $previous_domain;
        $this->group_middleware = array_values(array_unique(array_merge($previous_middleware, $group->middleware_value())));
        $this->group_wheres = $group->wheres_value() + $previous_wheres;
        $routes();
        $this->group_prefix = $previous_prefix;
        $this->group_name = $previous_name;
        $this->group_domain = $previous_domain;
        $this->group_middleware = $previous_middleware;
        $this->group_wheres = $previous_wheres;
    }

    /**
     * @param string|list<string> $http_method
     */
    private function add(string|array $http_method, string $route, mixed $handler): Binding
    {
        $methods = array_values(array_map(strtoupper(...), (array) $http_method));
        $binding = new Binding(
            $this,
            $methods,
            $this->join_uri($this->group_prefix, $route),
            $handler,
            $this->group_name,
            $this->group_domain,
            $this->group_middleware,
            $this->group_wheres,
        );
        $this->bindings[] = $binding;
        $this->invalidate();

        return $binding;
    }

    /**
     * @param RouteData $data
     */
    private static function match_static(array $data, string $method, string $uri): ?Matched
    {
        $static = $data[0] ?? [];
        foreach ([$method, $method === 'HEAD' ? 'GET' : null, '*'] as $try) {
            if (! is_string($try)) {
                continue;
            }
            $row = $static[$try][$uri] ?? null;
            if (! is_array($row) || ! array_key_exists(0, $row)) {
                continue;
            }
            $extra = $row[1] ?? [];

            return new Matched($row[0], [], is_array($extra) ? $extra : []);
        }

        return null;
    }

    /**
     * @return RouteData
     */
    private function compiled_data(string $host): array
    {
        if ($this->bindings !== []) {
            return $this->build_data($host);
        }
        $cached = Cache::read();
        if (is_array($cached) && (($cached[0] ?? []) !== [] || ($cached[1] ?? []) !== [])) {
            return $cached;
        }
        $this->ensure_declared();
        $data = $this->build_data($host);
        if (($data[0] ?? []) !== [] || ($data[1] ?? []) !== []) {
            Cache::write(Cache::path(), $data, [
                'compiled_at' => time(),
                'host' => $host,
                'files' => \Webkernel\Cache\CompilationManifest::fingerprints(),
            ]);
        }

        return $data;
    }

    /**
     * @return RouteData
     */
    private function build_data(string $host): array
    {
        $specific = [];
        $general = [];
        foreach ($this->bindings as $binding) {
            if ($binding->domain_pattern() !== '') {
                if ($binding->matches_host($host)) {
                    $specific[] = $binding;
                }
                continue;
            }
            $general[] = $binding;
        }

        $generator = new Generator();
        $occupied = [];
        foreach ($specific as $binding) {
            $occupied[$binding->uri()] = true;
            $binding->compile($generator);
        }
        foreach ($general as $binding) {
            if (isset($occupied[$binding->uri()])) {
                continue;
            }
            $binding->compile($generator);
        }

        return $generator->get_data();
    }

    private function uri_generator(): Uri
    {
        if ($this->uris !== null) {
            return $this->uris;
        }
        $named = [];
        foreach ($this->bindings as $binding) {
            $binding->register_named($named);
        }

        return $this->uris = new Uri($named);
    }

    /**
     * @param array<string, string> $vars
     */
    private static function invoke(mixed $handler, array $vars): mixed
    {
        if (is_callable($handler)) {
            return $handler(...$vars);
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
     * @return list<array{methods: list<string>, uri: string, name: string, action: string}>
     */
    private function listed(): array
    {
        $rows = [];
        foreach ($this->bindings as $binding) {
            $methods = $binding->methods();
            $order = ['GET' => 0, 'HEAD' => 1, 'QUERY' => 2, 'POST' => 3, 'PUT' => 4, 'PATCH' => 5, 'DELETE' => 6, 'OPTIONS' => 7];
            usort($methods, static fn (string $a, string $b): int => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));
            $rows[] = [
                'methods' => $methods,
                'uri' => $binding->uri(),
                'name' => $binding->resolved_name(),
                'action' => $binding->action_label(),
            ];
        }
        usort($rows, static function (array $a, array $b): int {
            return [$a['uri'], $a['name']] <=> [$b['uri'], $b['name']];
        });

        return $rows;
    }

    private function join_uri(string $left, string $right): string
    {
        $left = trim($left, '/');
        $right = trim($right, '/');
        if ($left === '' && $right === '') {
            return '/';
        }
        if ($left === '') {
            return '/'.$right;
        }
        if ($right === '') {
            return '/'.$left;
        }

        return '/'.$left.'/'.$right;
    }

    private static function normalize_host(string $host): string
    {
        $host = strtolower($host);
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

    /**
     * Require route files declared by providers / host, dump-autoload list as fallback.
     */
    private function ensure_declared(): void
    {
        if (self::$declared_loaded) {
            return;
        }
        self::$declared_loaded = true;

        foreach (webapp()->route_files() as $path) {
            if (is_file($path)) {
                require $path;
            }
        }
    }
}

require_once __DIR__.'/functions/route.php';
