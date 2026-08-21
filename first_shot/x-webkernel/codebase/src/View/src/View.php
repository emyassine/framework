<?php declare(strict_types=1);

namespace Webkernel\View;

use Webkernel\Composables\ComposableContract;

/**
 * Views. Compiler is BladeOne owned in this package — not a Composer dependency.
 *
 * Templates: {name}.view.php
 * Compiled:  storage/framework/views/{name}_{hash}.view.php.compiled
 *
 * Namespaced: @include('webkernel::layouts.page'), <webkernel::page />.
 * Un-namespaced @extends('layouts.page') stays (host resources/views first).
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

    public static function container_lifetime(): string
    {
        return 'singleton';
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
        $engine = new Engine(self::template_paths(), webapp_path('storage/framework/views'), Engine::MODE_AUTO);
        $engine->set_echo_format('\\'.self::class.'::echo(%s)');
        $engine->add_alias_classes('Js', Js::class);
        $engine->add_alias_classes('View', self::class);
        self::apply_namespaces($engine);

        return self::$engine = $engine;
    }

    public static function compiler(): Compiler
    {
        return self::engine()->compiler();
    }

    /**
     * @return list<string>
     */
    public static function template_paths(): array
    {
        $dirs = webapp()->view_dirs();
        if ($dirs !== []) {
            return $dirs;
        }

        return [webapp_path('resources/views')];
    }

    /** Reset process singleton (tests). */
    public static function flush(): void
    {
        self::$engine = null;
        self::$stringable = [];
        self::$double_encode = true;
        webapp()->container()->forget(self::class);
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

    public function render(): string
    {
        return self::engine()->run($this->name, $this->data);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private static function apply_namespaces(Engine $engine): void
    {
        foreach (webapp()->view_namespaces() as $namespace => $dirs) {
            if ($namespace === '') {
                continue;
            }
            foreach ($dirs as $dir) {
                $engine->add_view_namespace($namespace, $dir);
            }
        }
        foreach (webapp()->component_namespaces() as $namespace => $dirs) {
            foreach ($dirs as $dir) {
                $engine->add_component_namespace($namespace, $dir);
            }
        }
    }
}

require_once dirname(__DIR__).'/functions/view.php';
