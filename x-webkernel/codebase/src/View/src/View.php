<?php declare(strict_types=1);

namespace Webkernel\View;

/**
 * Blade views. Compiler is BladeOne owned in this package — not a Composer dependency.
 *
 * Templates: resources/views/{name}.blade.php
 * Compiled:  storage/framework/views
 */
final class View implements \Stringable
{
    /** @var array<class-string, callable(object): string> */
    private static array $stringable = [];

    private static bool $double_encode = true;

    private static ?Compiler $compiler = null;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $name,
        private array $data = [],
    ) {
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
        self::compiler()->share($key, $value);
    }

    public static function exists(string $view): bool
    {
        return self::compiler()->getTemplateFile($view) !== '';
    }

    public static function addLocation(string $path): void
    {
        self::compiler()->add_template_path($path);
    }

    public static function directive(string $name, callable $handler): void
    {
        self::compiler()->directive($name, $handler);
    }

    public static function if(string $name, callable $callback): void
    {
        $blade = self::compiler();
        $blade->if($name, $callback);
        $blade->directive('unless'.$name, static function (?string $expression) use ($name, $blade): string {
            $tmp = $blade->stripParentheses($expression);

            return ($expression !== null && $expression !== '')
                ? $blade->phpTag." if (! \$this->check('$name', $tmp)): ?>"
                : $blade->phpTag." if (! \$this->check('$name')): ?>";
        });
        $blade->directive('endunless'.$name, static function () use ($blade): string {
            return $blade->phpTag.' endif; ?>';
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

    public static function withoutDoubleEncoding(): void
    {
        self::$double_encode = false;
    }

    public static function compiler(): Compiler
    {
        if (self::$compiler instanceof Compiler) {
            return self::$compiler;
        }

        $views = webapp_path('resources/views');
        $compiled = webapp_path('storage/framework/views');
        if (! is_dir($compiled) && ! mkdir($compiled, 0775, true) && ! is_dir($compiled)) {
            throw new \RuntimeException('Unable to create '.$compiled);
        }

        $mode = Compiler::MODE_AUTO;
        $engine = new Compiler($views, $compiled, $mode);
        $engine->throwOnError = true;
        $engine->set_echo_format('\\'.self::class.'::echo(%s)');
        $engine->addAliasClasses('Js', Js::class);
        $engine->addAliasClasses('View', self::class);

        return self::$compiler = $engine;
    }

    /** Reset process singleton (tests). */
    public static function flush(): void
    {
        self::$compiler = null;
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

    public function render(): string
    {
        return self::compiler()->run($this->name, $this->data);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
