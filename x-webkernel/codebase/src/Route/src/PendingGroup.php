<?php declare(strict_types=1);

namespace Webkernel\Route;

/**
 * Accumulates group attributes until group() or a verb registers a route.
 */
final class PendingGroup
{
    /** @var list<string> */
    private array $middleware = [];

    /** @var array<string, string> */
    private array $wheres = [];

    public function __construct(
        private Route $router,
        private string $prefix = '',
        private string $name = '',
        private string $domain = '',
    ) {
    }

    /**
     * @param array{prefix?: string, as?: string, name?: string, domain?: string, middleware?: string|list<string>, where?: array<string, string>} $attributes
     */
    public static function from_attributes(Route $router, array $attributes): self
    {
        $group = new self($router);
        if (isset($attributes['prefix'])) {
            $group->prefix((string) $attributes['prefix']);
        }
        if (isset($attributes['as'])) {
            $group->name((string) $attributes['as']);
        } elseif (isset($attributes['name'])) {
            $group->name((string) $attributes['name']);
        }
        if (isset($attributes['domain'])) {
            $group->domain((string) $attributes['domain']);
        }
        if (isset($attributes['middleware'])) {
            $group->middleware($attributes['middleware']);
        }
        if (isset($attributes['where'])) {
            $group->where($attributes['where']);
        }

        return $group;
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = $this->prefix === ''
            ? $prefix
            : rtrim($this->prefix, '/').'/'.ltrim($prefix, '/');

        return $this;
    }

    public function name(string $name): self
    {
        $this->name .= $name;

        return $this;
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Recorded, not run — add a pipeline when auth exists.
     *
     * @param  string|list<string>  $middleware
     */
    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_values(array_unique(array_merge(
            $this->middleware,
            self::normalize_middleware($middleware),
        )));

        return $this;
    }

    /**
     * @param  string|array<string, string>  $parameter
     */
    public function where(string|array $parameter, ?string $pattern = null): self
    {
        if (is_array($parameter)) {
            foreach ($parameter as $name => $regex) {
                $this->wheres[$name] = $regex;
            }
        } elseif ($pattern !== null) {
            $this->wheres[$parameter] = $pattern;
        }

        return $this;
    }

    public function group(callable $routes): void
    {
        $this->router->push_group($this, $routes);
    }

    public function get(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::get($uri, $action));
    }

    public function post(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::post($uri, $action));
    }

    public function put(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::put($uri, $action));
    }

    public function patch(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::patch($uri, $action));
    }

    public function delete(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::delete($uri, $action));
    }

    public function options(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::options($uri, $action));
    }

    public function any(string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::any($uri, $action));
    }

    /**
     * @param list<string> $methods
     */
    public function match(array $methods, string $uri, mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::match($methods, $uri, $action));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function view(string $uri, string $view, array $data = [], int $status = 200): Binding
    {
        return $this->tap(static fn (): Binding => Route::view($uri, $view, $data, $status));
    }

    public function redirect(string $uri, string $destination, int $status = 302): Binding
    {
        return $this->tap(static fn (): Binding => Route::redirect($uri, $destination, $status));
    }

    public function permanentRedirect(string $uri, string $destination): Binding
    {
        return $this->tap(static fn (): Binding => Route::permanentRedirect($uri, $destination));
    }

    public function fallback(mixed $action): Binding
    {
        return $this->tap(static fn (): Binding => Route::fallback($action));
    }

    public function prefix_value(): string
    {
        return $this->prefix;
    }

    public function name_value(): string
    {
        return $this->name;
    }

    public function domain_value(): string
    {
        return $this->domain;
    }

    /** @return list<string> */
    public function middleware_value(): array
    {
        return $this->middleware;
    }

    /** @return array<string, string> */
    public function wheres_value(): array
    {
        return $this->wheres;
    }

    /**
     * @param  string|list<string>  $middleware
     *
     * @return list<string>
     */
    public static function normalize_middleware(string|array $middleware): array
    {
        if (is_string($middleware)) {
            $middleware = explode('|', $middleware);
        }
        $out = [];
        foreach ($middleware as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $out[] = $item;
            }
        }

        return $out;
    }

    private function tap(callable $register): Binding
    {
        $out = null;
        $this->router->push_group($this, static function () use ($register, &$out): void {
            $out = $register();
        });
        assert($out instanceof Binding);

        return $out;
    }
}
