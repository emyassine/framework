<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * Custom compile handlers and runtime conditions (`View::if()`).
 */
final class Directives
{
    /** @var array<string, callable(string): string> */
    private array $handlers = [];

    /** @var array<string, callable(mixed...): bool> */
    private array $conditions = [];

    /**
     * @param $name string
     * @param $handler callable(string): string
     *
     * @return void
     */
    public function handler(string $name, callable $handler): void
    {
        $this->handlers[$name] = $handler;
    }

    /**
     * @param $name string
     *
     * @return callable(string): string|null
     */
    public function get(string $name): ?callable
    {
        return $this->handlers[$name] ?? null;
    }

    /**
     * @param $name string
     * @param $callback callable(mixed...): bool
     *
     * @return void
     */
    public function condition(string $name, callable $callback): void
    {
        $this->conditions[$name] = $callback;
        $this->handler($name, static function (string $expression) use ($name): string {
            $tmp = Php::strip_parentheses($expression);

            return $tmp !== ''
                ? Php::OPEN."if (\$this->check('$name', $tmp)): ?>"
                : Php::OPEN."if (\$this->check('$name')): ?>";
        });
        $this->handler('else'.$name, static function (string $expression) use ($name): string {
            $tmp = Php::strip_parentheses($expression);

            return $tmp !== ''
                ? Php::OPEN."elseif (\$this->check('$name', $tmp)): ?>"
                : Php::OPEN."elseif (\$this->check('$name')): ?>";
        });
        $this->handler('end'.$name, static fn (string $_): string => Php::OPEN.'endif; ?>');
        $this->handler('unless'.$name, static function (string $expression) use ($name): string {
            $tmp = Php::strip_parentheses($expression);

            return $tmp !== ''
                ? Php::OPEN."if (! \$this->check('$name', $tmp)): ?>"
                : Php::OPEN."if (! \$this->check('$name')): ?>";
        });
        $this->handler('endunless'.$name, static fn (string $_): string => Php::OPEN.'endif; ?>');
    }

    /**
     * @param $name string
     * @param $args mixed ...
     *
     * @return bool
     */
    public function check(string $name, mixed ...$args): bool
    {
        $callback = $this->conditions[$name] ?? null;
        if ($callback === null) {
            throw new \RuntimeException('Unknown view condition ['.$name.'].');
        }

        return (bool) $callback(...$args);
    }
}
