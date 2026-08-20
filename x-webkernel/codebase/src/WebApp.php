<?php declare(strict_types=1);

namespace Webkernel;

use Psr\Container\ContainerInterface;
use Webkernel\Container\Container;
use Webkernel\Http\Request;
use Webkernel\Platform\Exceptions;
use Webkernel\Platform\Middleware;
use Webkernel\Composables\ComposableContract;

/**
 * Host application facade. Composables are lazy API segments
 * (webapp()->view(), webapp()->route()). Providers declare paths at boot.
 *
 * @method \Webkernel\View\View view()
 * @method \Webkernel\Route\Route route()
 */
final class WebApp
{
    private static ?self $instance = null;

    private Container $container;

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
    }

    public function container(): Container
    {
        return $this->container;
    }

    /**
     * @param callable(Middleware): void $callback
     */
    public function with_middleware(callable $callback): self
    {
        $this->middleware ??= new Middleware();
        $callback($this->middleware);

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
        $this->container->instance(Request::class, $request);
        echo $this->route()::dispatch($request->method(), $request->uri(), $request->host());
    }

    public function middleware(): ?Middleware
    {
        return $this->middleware;
    }

    public function exceptions(): ?Exceptions
    {
        return $this->exceptions;
    }

    /**
     * @param 'providers'|'routes' $key
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
     * Unnamed view dirs (host first) plus dump-autoload fallback.
     *
     * @return list<string>
     */
    public function view_dirs(): array
    {
        $this->boot();
        $dirs = $this->view_namespaces[''] ?? [];
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

    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        }
        $this->booted = true;
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
        foreach ($this->composables as $class) {
            $lifetime = $class::container_lifetime();
            if ($lifetime !== 'singleton' && $lifetime !== 'bind' && $lifetime !== 'scoped') {
                throw new \RuntimeException($class.'::container_lifetime() must be singleton|bind|scoped.');
            }
            $this->container->{$lifetime}($class);
        }

        return $this;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->boot();
        $class = $this->composables[$name] ?? null;
        if ($class === null) {
            throw new \BadMethodCallException('Unknown composable ['.$name.'].');
        }
        if ($arguments !== []) {
            throw new \BadMethodCallException('webapp()->'.$name.'() does not take arguments.');
        }

        return $this->container->make($class);
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
