<?php declare(strict_types=1);

namespace Webkernel\WebAppApi;

/**
 * Three lifetimes. No PSR-11. No reflection auto-wiring.
 * Explicit bind, or `new $abstract()`.
 */
final class Container
{
    /**
     * @var array<class-string, array{lifetime: 'singleton'|'bind'|'scoped', factory: callable|null}>
     */
    private array $bindings = [];

    /** @var array<class-string, object> */
    private array $instances = [];

    /** @var array<class-string, object> */
    private array $scoped_instances = [];

    /**
     * @param class-string $abstract
     */
    public function singleton(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'singleton', 'factory' => $factory];
    }

    /**
     * @param class-string $abstract
     */
    public function bind(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'bind', 'factory' => $factory];
        unset($this->instances[$abstract], $this->scoped_instances[$abstract]);
    }

    /**
     * @param class-string $abstract
     */
    public function scoped(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'scoped', 'factory' => $factory];
    }

    /**
     * @param class-string $abstract
     */
    public function make(string $abstract): mixed
    {
        $binding = $this->bindings[$abstract] ?? ['lifetime' => 'bind', 'factory' => null];
        $lifetime = $binding['lifetime'];

        if ($lifetime === 'singleton' && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if ($lifetime === 'scoped' && isset($this->scoped_instances[$abstract])) {
            return $this->scoped_instances[$abstract];
        }

        $object = $binding['factory'] !== null
            ? ($binding['factory'])()
            : new $abstract();

        if ($lifetime === 'singleton') {
            $this->instances[$abstract] = $object;
        } elseif ($lifetime === 'scoped') {
            $this->scoped_instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * @param class-string $abstract
     */
    public function forget(string $abstract): void
    {
        unset($this->instances[$abstract], $this->scoped_instances[$abstract]);
    }

    /** Drop scoped instances (end of request / webapp()->boot() reset). */
    public function flush(): void
    {
        $this->scoped_instances = [];
    }
}
