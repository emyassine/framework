<?php declare(strict_types=1);

namespace Webkernel;

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

//>>>---> TODO : Fix Phpactor/Phpantom static analysis warnings:
//>>>---> TODO : - Add @method annotations on WebApp for dynamic __call magic (console(), route(), etc.)
//>>>---> TODO : - Fix type mismatches: ?Middleware nullable argument & class-string<T> generic constraints
//>>>---> TODO : - Fix Psr\Http\Message\ResponseInterface method resolution (getHeaders)

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Webkernel\Composables\ComposableContract;
use Webkernel\Composables\ConfigComposable;
use Webkernel\Composables\PanelComposable;
use Webkernel\Console\Input\ArgvInput;
use Webkernel\Container\Container;
use Webkernel\Http\Request;
use Webkernel\Platform\Exceptions;
use Webkernel\Platform\Middleware;

/**
 * Host application. Fluent segments are composables from the dump-autoload map
 * (`api_name => FQCN`). Do not add one method per segment here.
 *
 * @see vendor/composer/webkernel_composables.php
 * @see src/DevEnv/_ide_webapp.php
 * @method getStatusCode() \Psr\Http\Message\ResponseInterface
 */
final class WebApp
{
    private static ?self $instance = null;

    private Container $container;

    private ?ConfigComposable $config_composable = null;

    /** @var array<string, class-string<ComposableContract>> */
    private array $composables = [];

    /** @var list<class-string<PlatformProvider>> */
    private array $providers = [];

    /** @var array<string, list<string>> */
    private array $view_namespaces = [];

    /** @var array<string, list<string>> */
    private array $component_namespaces = [];

    /** @var list<string> */
    private array $route_files = [];

    /** @var list<class-string> */
    private array $command_classes = [];

    private ?Middleware $middleware = null;

    private ?Exceptions $exceptions = null;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(ContainerInterface::class, $this->container);
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public static function configure(): self
    {
        return self::get();
    }

    /** Reset process singleton (tests). */
    public static function flush(): void
    {
        self::$instance = null;
        PanelComposable::flush();
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function env(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            $all = getenv();

            return is_array($all) ? $all : $default;
        }
        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    public function is_production(): bool
    {
        $env = $this->env('WEBKERNEL_ENV', $this->env('APP_ENV'));

        return $env === 'production' || $env === 'prod';
    }

    public function is_debug(): bool
    {
        return ! $this->is_production();
    }

    /**
     * First primitive. Loaded before the composable map.
     *
     * @return ($key is null ? ConfigComposable : mixed)
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $this->config_composable ??= ConfigComposable::load();
        if (! $this->container->has(ConfigComposable::class)) {
            $this->container->instance(ConfigComposable::class, $config);
        }
        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }

    /**
     * @param callable(Middleware): void $callback
     */
    public function with_middleware(callable $callback): self
    {
        $callback($this->middleware());

        return $this;
    }

    /**
     * @param callable(Exceptions): void $callback
     */
    public function with_exceptions(callable $callback): self
    {
        $this->exceptions ??= new Exceptions();
        $callback($this->exceptions);

        return $this;
    }

    public function with_routes(?string $web = null): self
    {
        $web ??= webapp_path('routes/web.php');
        if (is_file($web)) {
            $this->declare('routes', [$web]);
        }

        return $this;
    }

    public function create(): self
    {
        return $this->boot();
    }

    public function handle_request(Request $request): void
    {
        $this->boot();
        $this->container->instance(Request::class, $request);

        $stack = $this->middleware()->stack();
        $next = function () use ($request): mixed {
            return $this->route()::dispatch($request->psr());
        };
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $mw = $stack[$i];
            $previous = $next;
            $next = static function () use ($mw, $previous, $request): mixed {
                if (is_callable($mw)) {
                    return $mw($request, $previous);
                }
                if (is_string($mw) && class_exists($mw)) {
                    $object = new $mw();
                    if (is_callable($object)) {
                        return $object($request, $previous);
                    }
                }

                return $previous();
            };
        }

        $this->emit($next());
    }

    public function handle_command(ArgvInput $input): int
    {
        $this->boot();
        $this->container->instance(ArgvInput::class, $input);

        return $this->console()->handle($input)->value;
    }

    public function middleware(): Middleware
    {
        $this->middleware ??= new Middleware();
        if (! $this->container->has(Middleware::class)) {
            $this->container->instance(Middleware::class, $this->middleware);
        }

        return $this->middleware;
    }

    public function exceptions(): ?Exceptions
    {
        return $this->exceptions;
    }

    /**
     * @param 'providers'|'routes'|'commands' $key
     * @param list<string>|string  $value
     */
    public function declare(string $key, mixed $value): self
    {
        if ($key === 'providers') {
            foreach ((array) $value as $class) {
                if (! is_string($class) || $class === '' || in_array($class, $this->providers, true)) {
                    continue;
                }
                $this->providers[] = $class;
            }

            return $this;
        }
        if ($key === 'routes') {
            foreach ((array) $value as $path) {
                if (! is_string($path) || $path === '' || in_array($path, $this->route_files, true)) {
                    continue;
                }
                $this->route_files[] = $path;
            }

            return $this;
        }
        if ($key === 'commands') {
            foreach ((array) $value as $class) {
                if (! is_string($class) || $class === '' || in_array($class, $this->command_classes, true)) {
                    continue;
                }
                $this->command_classes[] = $class;
            }

            return $this;
        }

        throw new \InvalidArgumentException('Unknown declare key ['.$key.'].');
    }

    public function declare_view(string $namespace, string $dir): self
    {
        $dir = rtrim($dir, '/\\');
        $this->view_namespaces[$namespace] ??= [];
        if (! in_array($dir, $this->view_namespaces[$namespace], true)) {
            $this->view_namespaces[$namespace][] = $dir;
        }

        return $this;
    }

    public function declare_component(string $namespace, string $dir): self
    {
        $dir = rtrim($dir, '/\\');
        $this->component_namespaces[$namespace] ??= [];
        if (! in_array($dir, $this->component_namespaces[$namespace], true)) {
            $this->component_namespaces[$namespace][] = $dir;
        }

        return $this;
    }

    /**
     * @return array<string, list<string>>
     */
    public function view_namespaces(): array
    {
        return $this->view_namespaces;
    }

    /**
     * @return array<string, list<string>>
     */
    public function component_namespaces(): array
    {
        return $this->component_namespaces;
    }

    /**
     * @return list<string>
     */
    public function view_dirs(): array
    {
        $this->boot();
        $dirs = $this->view_namespaces[''] ?? [];
        if ($dirs !== []) {
            return $dirs;
        }
        foreach ($this->dumped_paths('webkernel_views.php') as $dir) {
            if (is_dir($dir) && ! in_array($dir, $dirs, true)) {
                $dirs[] = $dir;
            }
        }

        return $dirs;
    }

    /**
     * @return list<string>
     */
    public function route_files(): array
    {
        $this->boot();
        $files = $this->route_files;
        if ($files !== []) {
            return $files;
        }

        return $this->dumped_paths('webkernel_routes.php');
    }

    /**
     * @return list<class-string>
     */
    public function command_classes(): array
    {
        return $this->command_classes;
    }

    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        }
        $this->booted = true;
        $this->config();
        $this->config_composable?->stamp_identity();
        $this->load_dumped();
        $host_views = webapp_path('resources/views');
        if (is_dir($host_views)) {
            $this->declare_view('', $host_views);
        }
        foreach ($this->providers as $class) {
            if (! is_a($class, PlatformProvider::class, true)) {
                throw new \RuntimeException($class.' is not a PlatformProvider.');
            }
            (new $class())->register($this);
        }

        return $this;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $instance = $this->resolve_named($name);
        if ($arguments === []) {
            return $instance;
        }
        if (is_callable($instance)) {
            return $instance(...$arguments);
        }

        throw new \BadMethodCallException('webapp()->'.$name.'() does not take arguments.');
    }

    /**
     * @template T of ComposableContract
     * @param class-string<T> $class
     * @return T
     */
    private function resolve_composable(string $class): object
    {
        if (! $this->container->has($class)) {
            $lifetime = $class::container_lifetime();
            if ($lifetime !== 'singleton' && $lifetime !== 'bind' && $lifetime !== 'scoped') {
                throw new \RuntimeException($class.'::container_lifetime() must be singleton|bind|scoped.');
            }
            $this->container->{$lifetime}($class);
        }

        /** @var T $resolved */
        $resolved = $this->container->make($class);

        return $resolved;
    }

    private function resolve_named(string $name): object
    {
        $this->boot();
        $class = $this->composables[$name] ?? null;
        if (! is_string($class) || $class === '') {
            throw new \BadMethodCallException('Unknown composable ['.$name.'].');
        }
        if ($name === 'middleware') {
            return $this->middleware();
        }
        if ($name === 'request' && ! $this->container->has($class)) {
            $this->container->instance($class, Request::capture());
        }

        return $this->resolve_composable($class);
    }

    private function emit(mixed $out): void
    {
        if ($out instanceof ResponseInterface) {
            http_response_code($out->getStatusCode());
            foreach ($out->getHeaders() as $name => $values) {
                $first = true;
                foreach ($values as $value) {
                    header($name.': '.$value, $first);
                    $first = false;
                }
            }
            echo (string) $out->getBody();

            return;
        }
        echo $out;
    }

    private function load_dumped(): void
    {
        $composables = vendor_dir('composer/webkernel_composables.php');
        if (is_file($composables)) {
            $map = require $composables;
            if (is_array($map)) {
                foreach ($map as $name => $class) {
                    if (is_string($name) && is_string($class) && $class !== '') {
                        $this->composables[$name] = $class;
                    }
                }
            }
        }
        $providers = vendor_dir('composer/webkernel_providers.php');
        if (is_file($providers)) {
            $list = require $providers;
            if (is_array($list)) {
                $this->declare('providers', $list);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function dumped_paths(string $basename): array
    {
        $file = vendor_dir('composer/'.$basename);
        if (! is_file($file)) {
            return [];
        }
        $loaded = require $file;
        if (! is_array($loaded)) {
            return [];
        }
        $out = [];
        foreach ($loaded as $path) {
            if (is_string($path) && $path !== '') {
                $out[] = $path;
            }
        }

        return $out;
    }
}
