<?php declare(strict_types=1);

namespace Webkernel\Container;

use Psr\Container\ContainerInterface;

/**
 * PSR-11 container. Lifetimes: singleton, bind, scoped.
 * Explicit bind, or a constructor we own. No reflection auto-wiring.
 */
final class Container implements ContainerInterface
{
    /**
     * @var array<string, array{lifetime: 'singleton'|'bind'|'scoped', factory: callable|null}>
     */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, object> */
    private array $scoped_instances = [];

    public function singleton(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'singleton', 'factory' => $factory];
    }

    public function bind(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'bind', 'factory' => $factory];
        unset($this->instances[$abstract], $this->scoped_instances[$abstract]);
    }

    public function scoped(string $abstract, ?callable $factory = null): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'scoped', 'factory' => $factory];
    }

    public function instance(string $abstract, object $object): void
    {
        $this->bindings[$abstract] = ['lifetime' => 'singleton', 'factory' => static fn (): object => $object];
        $this->instances[$abstract] = $object;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || isset($this->scoped_instances[$id]);
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw NotFound::of($id);
        }

        return $this->make($id);
    }

    public function make(string $abstract): mixed
    {
        if (! $this->has($abstract)) {
            throw NotFound::of($abstract);
        }

        $binding = $this->bindings[$abstract] ?? ['lifetime' => 'singleton', 'factory' => null];
        $lifetime = $binding['lifetime'];

        if ($lifetime === 'singleton' && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if ($lifetime === 'scoped' && isset($this->scoped_instances[$abstract])) {
            return $this->scoped_instances[$abstract];
        }

        try {
            $object = $binding['factory'] !== null
                ? ($binding['factory'])()
                : new $abstract();
        } catch (NotFound $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ContainerException('Unable to resolve ['.$abstract.']: '.$e->getMessage(), 0, $e);
        }

        if (! is_object($object)) {
            throw new ContainerException('Factory for ['.$abstract.'] did not return an object.');
        }

        if ($lifetime === 'singleton') {
            $this->instances[$abstract] = $object;
        } elseif ($lifetime === 'scoped') {
            $this->scoped_instances[$abstract] = $object;
        }

        return $object;
    }

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
