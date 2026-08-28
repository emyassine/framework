<?php declare(strict_types=1);

namespace Webkernel\View;

use Webkernel\Composables\ComposableContract;

/**
 * Views. Templates: {name}.view.php
 * Compiled: platform/storage/framework/views/{name}_{hash}.view.php.compiled
 *
 * Namespaced: @include('webkernel::layouts.page'), <x-webkernel::page />.
 */
final class View implements ComposableContract, \Stringable
{
    /** @var array<class-string, callable(object): string> */
    private static array $stringable = [];

    private static bool $double_encode = true;

    private static ?Engine $engine = null;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $name = '',
        private array $data = [],
    ) {
    }

    public static function api_name(): string
    {
        return 'view';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __invoke(?string $template = null, array $data = []): self
    {
        return $template === null ? $this : self::make($template, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $merge_data
     */
    public static function make(string $view, array $data = [], array $merge_data = []): self
    {
        return new self($view, array_merge($merge_data, $data));
    }

    /**
     * @param string|array<string, mixed> $key
     */
    public static function share(string|array $key, mixed $value = null): void
    {
        self::engine()->share($key, $value);
    }

    public static function exists(string $view): bool
    {
        return self::engine()->template_file($view) !== '';
    }

    public static function add_location(string $path): void
    {
        self::engine()->add_template_path($path);
    }

    public static function directive(string $name, callable $handler): void
    {
        self::compiler()->directive($name, $handler);
    }

    public static function if(string $name, callable $callback): void
    {
        $compiler = self::compiler();
        $compiler->if($name, $callback);
        $compiler->directive('unless'.$name, static function (?string $expression) use ($name, $compiler): string {
            $tmp = $compiler->strip_parentheses($expression);

            return ($expression !== null && $expression !== '')
                ? $compiler->php_tag." if (! \$this->check('$name', $tmp)): ?>"
                : $compiler->php_tag." if (! \$this->check('$name')): ?>";
        });
        $compiler->directive('endunless'.$name, static function () use ($compiler): string {
            return $compiler->php_tag.' endif; ?>';
        });
    }

    public static function stringable(callable $handler): void
    {
        $ref = $handler instanceof \Closure
            ? new \ReflectionFunction($handler)
            : new \ReflectionFunction($handler(...));
        $type = $ref->getParameters()[0]->getType() ?? null;
        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            throw new \InvalidArgumentException('View::stringable() requires a type-hinted object parameter.');
        }
        $class = $type->getName();
        self::$stringable[$class] = $handler;
    }

    public static function without_double_encoding(): void
    {
        self::$double_encode = false;
    }

    public static function engine(): Engine
    {
        if (self::$engine instanceof Engine) {
            return self::$engine;
        }
        $compiled = self::compiled_views();
        $dirs = $compiled['dirs'] !== [] ? $compiled['dirs'] : self::fallback_dirs($compiled);
        $engine = new Engine(
            $dirs,
            webapp_path('platform/storage/framework/views'),
            Engine::MODE_AUTO,
        );
        $engine->set_echo_format('\\'.self::class.'::echo(%s)');
        $engine->add_alias_classes('Js', Js::class);
        $engine->add_alias_classes('View', self::class);
        self::apply_namespaces($engine, $compiled);

        return self::$engine = $engine;
    }

    public static function compiler(): Compiler
    {
        return self::engine()->compiler();
    }

    public static function flush(): void
    {
        self::$engine = null;
        self::$stringable = [];
        self::$double_encode = true;
    }

    public static function echo(mixed $value): string
    {
        if ($value instanceof Js) {
            return (string) $value;
        }
        if (is_object($value)) {
            foreach (self::$stringable as $class => $handler) {
                if ($value instanceof $class) {
                    return e($handler($value), self::$double_encode);
                }
            }
        }

        return e($value, self::$double_encode);
    }

    /**
     * @param string|array<string, mixed> $key
     */
    public function with(string|array $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(?string $template = null, array $data = []): string
    {
        if ($template !== null) {
            return self::engine()->run($template, $data);
        }

        return self::engine()->run($this->name, $this->data);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @return array{dirs: list<string>, namespaces: array<string, list<string>>, components: array<string, list<string>>}
     */
    private static function compiled_views(): array
    {
        $views = self::load_dump('webkernel_views.php');
        $components = self::load_dump('webkernel_components.php');

        $dirs = [];
        $namespaces = [];
        if (array_is_list($views)) {
            foreach ($views as $dir) {
                if (is_string($dir) && $dir !== '') {
                    $dirs[] = $dir;
                }
            }
        } else {
            foreach ($views['dirs'] ?? [] as $dir) {
                if (is_string($dir) && $dir !== '') {
                    $dirs[] = $dir;
                }
            }
            foreach ($views['namespaces'] ?? [] as $namespace => $ns_dirs) {
                if (! is_string($namespace) || ! is_array($ns_dirs)) {
                    continue;
                }
                foreach ($ns_dirs as $dir) {
                    if (is_string($dir) && $dir !== '') {
                        $namespaces[$namespace][] = $dir;
                    }
                }
            }
        }

        $component_namespaces = [];
        $component_map = array_is_list($components) ? [] : ($components['namespaces'] ?? $components);
        foreach ($component_map as $namespace => $ns_dirs) {
            if (! is_string($namespace) || ! is_array($ns_dirs)) {
                continue;
            }
            foreach ($ns_dirs as $dir) {
                if (is_string($dir) && $dir !== '') {
                    $component_namespaces[$namespace][] = $dir;
                }
            }
        }

        return [
            'dirs' => $dirs,
            'namespaces' => $namespaces,
            'components' => $component_namespaces,
        ];
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private static function load_dump(string $basename): array
    {
        $file = vendor_dir('composer/'.$basename);
        if (! is_file($file)) {
            return [];
        }
        $loaded = require $file;

        return is_array($loaded) ? $loaded : [];
    }

    /**
     * @param array{dirs: list<string>, namespaces: array<string, list<string>>, components: array<string, list<string>>} $compiled
     * @return list<string>
     */
    private static function fallback_dirs(array $compiled): array
    {
        $dirs = [];
        foreach ($compiled['namespaces'] as $ns_dirs) {
            foreach ($ns_dirs as $dir) {
                if (! in_array($dir, $dirs, true)) {
                    $dirs[] = $dir;
                }
            }
        }

        return $dirs;
    }

    /**
     * @param array{dirs: list<string>, namespaces: array<string, list<string>>, components: array<string, list<string>>} $compiled
     */
    private static function apply_namespaces(Engine $engine, array $compiled): void
    {
        foreach ($compiled['namespaces'] as $namespace => $dirs) {
            if ($namespace === '') {
                continue;
            }
            foreach ($dirs as $dir) {
                $engine->add_view_namespace($namespace, $dir);
            }
        }
        foreach ($compiled['components'] as $namespace => $dirs) {
            foreach ($dirs as $dir) {
                $engine->add_component_namespace($namespace, $dir);
            }
        }
    }
}
