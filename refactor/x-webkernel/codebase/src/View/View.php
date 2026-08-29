<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

use Webkernel\Composables\ComposableContract;
use Webkernel\View\Compile\Mode;

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
     * @param $name string
     * @param $data array<string, mixed>
     */
    public function __construct(
        private readonly string $name = '',
        private array $data = [],
    ) {
    }

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'view';
    }

    /**
     * @param $template string|null
     * @param $data array<string, mixed>
     *
     * @return self
     */
    public function __invoke(?string $template = null, array $data = []): self
    {
        return $template === null ? $this : self::make($template, $data);
    }

    /**
     * @param $view string
     * @param $data array<string, mixed>
     * @param $merge_data array<string, mixed>
     *
     * @return self
     */
    public static function make(string $view, array $data = [], array $merge_data = []): self
    {
        return new self($view, array_merge($merge_data, $data));
    }

    /**
     * @param $key string|array<string, mixed>
     * @param $value mixed
     *
     * @return void
     */
    public static function share(string|array $key, mixed $value = null): void
    {
        self::engine()->share($key, $value);
    }

    /**
     * @param $view string
     *
     * @return bool
     */
    public static function exists(string $view): bool
    {
        return self::engine()->template_file($view) !== '';
    }

    /**
     * @param $path string
     *
     * @return void
     */
    public static function add_location(string $path): void
    {
        self::engine()->add_template_path($path);
    }

    /**
     * @param $name string
     * @param $handler callable(string|null): string
     *
     * @return void
     */
    public static function directive(string $name, callable $handler): void
    {
        self::compiler()->directive($name, $handler);
    }

    /**
     * Register `@{$name}` / `@else{$name}` / `@end{$name}` plus unless variants.
     *
     * @param $name string
     * @param $callback callable(mixed...): bool
     *
     * @return void
     */
    public static function if(string $name, callable $callback): void
    {
        self::engine()->directives()->condition($name, $callback);
    }

    /**
     * First parameter of $handler must be a type-hinted object.
     *
     * @param $handler callable(object): string
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
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

    /**
     * @return void
     */
    public static function without_double_encoding(): void
    {
        self::$double_encode = false;
    }

    /**
     * @param $mode Mode|null Fast when dump-autoload wrote compiled paths. Pass Auto to compile.
     *
     * @return Engine
     */
    public static function engine(?Mode $mode = null): Engine
    {
        if (self::$engine instanceof Engine) {
            return self::$engine;
        }
        $compiled = self::compiled_views();
        $dirs = $compiled['dirs'] !== [] ? $compiled['dirs'] : self::fallback_dirs($compiled);
        $map = self::compiled_map();
        $mode ??= $map !== [] ? Mode::Fast : Mode::Auto;
        $engine = new Engine(
            $dirs,
            webapp_path('platform/storage/framework/views'),
            $mode,
        );
        $engine->set_echo_format('\\'.self::class.'::echo(%s)');
        self::apply_namespaces($engine, $compiled);
        if ($mode === Mode::Fast && $map !== []) {
            $engine->set_compiled_map($map);
        }

        return self::$engine = $engine;
    }

    /**
     * @return Compiler
     */
    public static function compiler(): Compiler
    {
        return self::engine()->compiler();
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$engine = null;
        self::$stringable = [];
        self::$double_encode = true;
    }

    /**
     * @param $value mixed
     *
     * @return string
     */
    public static function echo(mixed $value): string
    {
        if ($value instanceof Js || $value instanceof AttributeBag) {
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
     * @param $key string|array<string, mixed>
     * @param $value mixed
     *
     * @return self
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
     * @param $template string|null
     * @param $data array<string, mixed>
     *
     * @return string
     */
    public function render(?string $template = null, array $data = []): string
    {
        if ($template !== null) {
            return self::engine()->run($template, $data);
        }

        return self::engine()->run($this->name, $this->data);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @return array<string, string>
     */
    private static function compiled_map(): array
    {
        $loaded = self::load_dump('webkernel_compiled_views.php');
        $map = [];
        foreach ($loaded as $view => $path) {
            if (\is_string($view) && $view !== '' && \is_string($path) && $path !== '') {
                $map[$view] = $path;
            }
        }

        return $map;
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
     * @param $basename string
     *
     * @return array<string, mixed>|list<mixed>
     */
    private static function load_dump(string $basename): array
    {
        $file = vendor_dir('composer/'.$basename);
        if (! is_file($file)) {
            return [];
        }
        $loaded = \function_exists('webkernel_include') ? \webkernel_include($file) : require $file;

        return is_array($loaded) ? $loaded : [];
    }

    /**
     * @param $compiled array{dirs: list<string>, namespaces: array<string, list<string>>, components: array<string, list<string>>}
     *
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
     * @param $engine Engine
     * @param $compiled array{dirs: list<string>, namespaces: array<string, list<string>>, components: array<string, list<string>>}
     *
     * @return void
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
