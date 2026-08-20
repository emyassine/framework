<?php declare(strict_types=1);

namespace Webkernel\Route;

/**
 * Application router. FastRoute MarkBased engine, owned in this package.
 *
 * Extra keys bind a URI to the platform tree (panel / cluster / resource / page)
 * and the permission that page requires. Auth is not enforced here.
 *
 * @phpstan-import-type Extra from Generator
 * @phpstan-import-type RouteData from Generator
 * @phpstan-import-type ParsedRoutes from Pattern
 * @phpstan-type NamedRoutes array<string, ParsedRoutes>
 * @phpstan-type Processed array{0: RouteData[0], 1: RouteData[1], 2: NamedRoutes}
 */
final class Route
{
    public const NAME = '_name';

    public const PANEL = '_panel';

    public const CLUSTER = '_cluster';

    public const RESOURCE = '_resource';

    public const PAGE = '_page';

    public const PERMISSION = '_permission';

    public const REGEX = '_route';

    private static bool $declared_loaded = false;

    private static ?self $app = null;

    private string $group_prefix = '';

    private string $group_name = '';

    /** @var NamedRoutes */
    private array $named = [];

    private ?Dispatcher $dispatcher = null;

    private ?Uri $uris = null;

    private function __construct(
        private readonly Pattern $parser = new Pattern(),
        private readonly Generator $generator = new Generator(),
    ) {
    }

    public static function app(): self
    {
        $created = self::$app === null;
        self::$app ??= new self();
        if ($created) {
            self::load_declared();
        }

        return self::$app;
    }

    /** Reset process singleton (tests). */
    public static function flush(): void
    {
        self::$app = null;
        self::$declared_loaded = false;
    }

    /**
     * @param Extra $extra
     */
    public static function get(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add(['GET', 'HEAD'], $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function post(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('POST', $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function put(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('PUT', $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function patch(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('PATCH', $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function delete(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('DELETE', $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function options(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('OPTIONS', $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function any(string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add('*', $uri, $action, $extra);
    }

    /**
     * @param list<string> $methods
     * @param Extra        $extra
     */
    public static function match(array $methods, string $uri, mixed $action, array $extra = []): void
    {
        self::app()->add(array_map(strtoupper(...), $methods), $uri, $action, $extra);
    }

    /**
     * @param Extra $extra
     */
    public static function view(string $uri, string $view, array $data = [], array $extra = []): void
    {
        self::get($uri, static fn (): \Webkernel\View\View => \Webkernel\View\View::make($view, $data), $extra);
    }

    public static function redirect(string $uri, string $destination, int $status = 302): void
    {
        self::get($uri, static function () use ($destination, $status): string {
            http_response_code($status);
            header('Location: '.$destination, true, $status);

            return '';
        });
    }

    public static function permanentRedirect(string $uri, string $destination): void
    {
        self::redirect($uri, $destination, 301);
    }

    /**
     * @param Extra $extra
     */
    public static function fallback(mixed $action, array $extra = []): void
    {
        self::any('/{path:.*}', $action, $extra);
    }

    /**
     * @param string|array{prefix?: string, as?: string, name?: string} $prefix
     */
    public static function group(string|array $prefix, callable $routes): void
    {
        self::app()->push_group($prefix, $routes);
    }

    public static function prefix(string $prefix, callable $routes): void
    {
        self::group(['prefix' => $prefix], $routes);
    }

    /**
     * @param array<string, string> $parameters
     */
    public static function url(string $name, array $parameters = []): string
    {
        return self::app()->uri_generator()->for_name($name, $parameters);
    }

    public static function dispatch(?string $method = null, ?string $uri = null): mixed
    {
        $method ??= $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri ??= $_SERVER['REQUEST_URI'] ?? '/';
        if (false !== $q = strpos($uri, '?')) {
            $uri = substr($uri, 0, $q);
        }
        $uri = rawurldecode($uri);

        $result = self::app()->dispatcher()->dispatch($method, $uri);

        if ($result instanceof NotMatched) {
            http_response_code(404);

            return '';
        }
        if ($result instanceof MethodNotAllowed) {
            http_response_code(405);
            header('Allow: '.implode(', ', $result->allowed));

            return '';
        }

        $out = self::invoke($result->handler, $result->variables);
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
     * @param string|list<string> $http_method
     * @param Extra               $extra
     */
    private function add(string|array $http_method, string $route, mixed $handler, array $extra): void
    {
        $route = $this->group_prefix.$route;
        $parsed = $this->parser->parse($route);
        $extra = [self::REGEX => $route] + $extra;

        if ($this->group_name !== '' && isset($extra[self::NAME]) && is_string($extra[self::NAME])) {
            $extra[self::NAME] = $this->group_name.$extra[self::NAME];
        }

        foreach ((array) $http_method as $method) {
            foreach ($parsed as $parsed_route) {
                $this->generator->add_route($method, $parsed_route, $handler, $extra);
            }
        }

        if (array_key_exists(self::NAME, $extra)) {
            $this->register_name($extra[self::NAME], $parsed);
        }

        $this->dispatcher = null;
        $this->uris = null;
    }

    /**
     * @param string|array{prefix?: string, as?: string, name?: string} $attributes
     */
    private function push_group(string|array $attributes, callable $routes): void
    {
        $prefix = is_string($attributes)
            ? $attributes
            : (string) ($attributes['prefix'] ?? '');
        $as = is_string($attributes)
            ? ''
            : (string) ($attributes['as'] ?? $attributes['name'] ?? '');

        $previous_prefix = $this->group_prefix;
        $previous_name = $this->group_name;
        $this->group_prefix = $previous_prefix.$prefix;
        $this->group_name = $previous_name.$as;
        $routes();
        $this->group_prefix = $previous_prefix;
        $this->group_name = $previous_name;
    }

    /**
     * @param ParsedRoutes $parsed
     */
    private function register_name(mixed $name, array $parsed): void
    {
        if (! is_string($name) || $name === '') {
            throw BadRoute::invalid_route_name($name);
        }
        if (array_key_exists($name, $this->named)) {
            throw BadRoute::named_route_already_defined($name);
        }

        $this->named[$name] = array_reverse($parsed);
    }

    private function dispatcher(): Dispatcher
    {
        return $this->dispatcher ??= new Dispatcher($this->generator->get_data());
    }

    private function uri_generator(): Uri
    {
        return $this->uris ??= new Uri($this->named);
    }

    /**
     * @param array<string, string> $vars
     */
    private static function invoke(mixed $handler, array $vars): mixed
    {
        if (is_callable($handler)) {
            return $handler(...$vars);
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
     * Require route files collected at dump-autoload. Not called until Route is used.
     */
    private static function load_declared(): void
    {
        if (self::$declared_loaded) {
            return;
        }
        self::$declared_loaded = true;

        $file = vendor_dir('composer/webkernel_routes.php');
        if (! is_file($file)) {
            return;
        }
        $routes = require $file;
        if (! is_array($routes)) {
            return;
        }
        foreach ($routes as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                require $path;
            }
        }
    }
}
