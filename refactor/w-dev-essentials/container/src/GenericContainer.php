<?php declare(strict_types=1);

namespace Webkernel\Container;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use Webkernel\Container\Attributes\Scoped;
use Webkernel\Container\Attributes\Singleton;
use Webkernel\Container\Attributes\Tag;
use Webkernel\Container\Events\LazyProxyAccessed;
use Webkernel\Container\Events\LazyProxyCreated;
use Webkernel\Container\Events\ServiceBound;
use Webkernel\Container\Events\ServiceReplaced;
use Webkernel\Container\Events\ServiceResolved;
use Webkernel\Container\Exceptions\BindingNotFoundException;
use Webkernel\Container\Exceptions\ContainerException;
use Webkernel\Container\Manifest\ContainerManifest;
use Webkernel\Container\Manifest\ContainerSnapshot;
use Webkernel\Contracts\ContainerInterface;
use Webkernel\Contracts\InspectorBusInterface;
use Webkernel\Contracts\ModuleScopeInterface;

final class GenericContainer implements ContainerInterface
{
    /** @var array<string, Binding> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $singletons = [];

    /** @var array<string, object> */
    private array $scoped = [];

    /** @var array<string, list<string>> */
    private array $tags = [];

    /** @var array<string, ModuleScope> */
    private array $modules = [];

    /** @var array<string, string> */
    private array $owners = [];

    /** @var array<string, string> */
    private array $lazyBindings = [];

    private InspectorBus $inspector;

    public function __construct(?InspectorBus $inspector = null)
    {
        $this->inspector = $inspector ?? new InspectorBus();
        $this->inspector->setSnapshotResolver(fn(): ContainerSnapshot => $this->snapshot());
    }

    /** @param $manifest */
    public static function hydrate(ContainerManifest|array $manifest, ?InspectorBus $inspector = null): self
    {
        $container = new self($inspector);
        $compiled = \is_array($manifest) ? ContainerManifest::fromArray($manifest) : $manifest;

        foreach ($compiled->modules as $moduleId => $module) {
            $exports = \array_values(\array_filter($module['exports'] ?? [], \is_string(...)));
            $container->mountModule($moduleId, $exports);
        }

        foreach ($compiled->bindings as $abstract => $concrete) {
            $container->bind($abstract, $concrete);
        }

        foreach ($compiled->lazyProxies as $abstract => $sourceModule) {
            $container->bindLazy($abstract, $sourceModule);
        }

        return $container;
    }

    /**
     * @param array<string, object> $parameters
     */
    public function make(string $abstract, array $parameters = []): object
    {
        return $this->resolve($abstract, $parameters, null);
    }

    public function makeScoped(string $abstract): object
    {
        $this->scoped($abstract);

        return $this->make($abstract);
    }

    public function bind(string $abstract, string|Closure $concrete): void
    {
        $this->setBinding(new Binding($abstract, $concrete, Binding::SCOPE_TRANSIENT));
    }

    public function singleton(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->setBinding(new Binding($abstract, $concrete ?? $abstract, Binding::SCOPE_SINGLETON));
    }

    public function scoped(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->setBinding(new Binding($abstract, $concrete ?? $abstract, Binding::SCOPE_SCOPED));
    }

    public function transient(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->setBinding(new Binding($abstract, $concrete ?? $abstract, Binding::SCOPE_TRANSIENT));
    }

    public function bindLazy(string $abstract, string $sourceModule): void
    {
        $this->lazyBindings[$abstract] = $sourceModule;
        $this->setBinding(new Binding(
            abstract: $abstract,
            concrete: function () use ($abstract, $sourceModule): object {
                $this->inspector->dispatch(new LazyProxyAccessed(
                    abstract: $abstract,
                    sourceModule: $sourceModule,
                    accessedAtMs: self::nowMs(),
                ));

                return $this->forModule($sourceModule)->resolve($abstract);
            },
            scope: Binding::SCOPE_TRANSIENT,
            moduleId: $sourceModule,
        ));

        $this->inspector->dispatch(new LazyProxyCreated(
            abstract: $abstract,
            sourceModule: $sourceModule,
            createdAtMs: self::nowMs(),
        ));
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->singletons[$abstract] = $instance;
        unset($this->scoped[$abstract]);

        $this->inspector->dispatch(new ServiceReplaced(
            abstract: $abstract,
            moduleId: $this->owners[$abstract] ?? 'kernel',
            replacedAtMs: self::nowMs(),
        ));
    }

    public function tag(string $tag, array $abstracts): void
    {
        $this->tags[$tag] = \array_values(\array_unique([
            ...($this->tags[$tag] ?? []),
            ...$abstracts,
        ]));
    }

    public function tagged(string $tag): iterable
    {
        foreach ($this->tags[$tag] ?? [] as $abstract) {
            yield $this->make($abstract);
        }
    }

    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->singletons[$abstract]) || \class_exists($abstract);
    }

    public function resolved(string $abstract): bool
    {
        return isset($this->singletons[$abstract], $this->scoped[$abstract]);
    }

    public function inspector(): InspectorBusInterface
    {
        return $this->inspector;
    }

    public function snapshot(): ContainerSnapshot
    {
        return new ContainerSnapshot(
            bindings: \array_map(static fn(Binding $binding): string => $binding->concreteName(), $this->bindings),
            resolved: \array_keys([...$this->singletons, ...$this->scoped]),
            tags: $this->tags,
            events: $this->inspector->auditLog(),
        );
    }

    public function forModule(string $moduleId): ModuleScopeInterface
    {
        if (! isset($this->modules[$moduleId])) {
            $this->mountModule($moduleId, []);
        }

        return $this->modules[$moduleId];
    }

    /**
     * @param list<class-string> $exports
     */
    public function mountModule(string $moduleId, array $exports): ModuleScope
    {
        $scope = new ModuleScope($moduleId, $exports, $this);
        $this->modules[$moduleId] = $scope;

        foreach ($exports as $abstract) {
            $this->owners[$abstract] = $moduleId;
        }

        return $scope;
    }

    public function restricted(string $moduleId): RestrictedContainer
    {
        return new RestrictedContainer($this, $moduleId);
    }

    /**
     * @param array<string, object> $parameters
     */
    public function makeForModule(string $moduleId, string $abstract, array $parameters = []): object
    {
        $owner = $this->owners[$abstract] ?? $moduleId;

        if ($owner !== $moduleId) {
            return $this->forModule($owner)->resolve($abstract);
        }

        return $this->resolve($abstract, $parameters, $moduleId);
    }

    public function bindForModule(string $moduleId, string $abstract, string|Closure $concrete): void
    {
        $this->setBinding(new Binding($abstract, $concrete, Binding::SCOPE_TRANSIENT, $moduleId));
        $this->owners[$abstract] = $moduleId;
    }

    public function singletonForModule(string $moduleId, string $abstract, string|Closure|null $concrete = null): void
    {
        $this->setBinding(new Binding($abstract, $concrete ?? $abstract, Binding::SCOPE_SINGLETON, $moduleId));
        $this->owners[$abstract] = $moduleId;
    }

    public function flushScoped(): void
    {
        $this->scoped = [];
        $this->inspector->flush();
    }

    public static function nowMs(): float
    {
        return \microtime(true) * 1000;
    }

    /**
     * @param array<string, object> $parameters
     */
    private function resolve(string $abstract, array $parameters, ?string $moduleId): object
    {
        $startedAt = self::nowMs();
        $binding = $this->bindingFor($abstract);

        if ($binding->scope === Binding::SCOPE_SINGLETON && isset($this->singletons[$abstract])) {
            return $this->recordResolution($abstract, $this->singletons[$abstract], $binding, $startedAt);
        }

        if ($binding->scope === Binding::SCOPE_SCOPED && isset($this->scoped[$abstract])) {
            return $this->recordResolution($abstract, $this->scoped[$abstract], $binding, $startedAt);
        }

        $object = $this->build($binding, $parameters, $moduleId);

        if ($binding->scope === Binding::SCOPE_SINGLETON) {
            $this->singletons[$abstract] = $object;
        }

        if ($binding->scope === Binding::SCOPE_SCOPED) {
            $this->scoped[$abstract] = $object;
        }

        return $this->recordResolution($abstract, $object, $binding, $startedAt);
    }

    private function bindingFor(string $abstract): Binding
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }

        if (! \class_exists($abstract)) {
            throw BindingNotFoundException::for($abstract);
        }

        $scope = Binding::SCOPE_TRANSIENT;
        $reflection = new ReflectionClass($abstract);

        if ($reflection->getAttributes(Singleton::class) !== []) {
            $scope = Binding::SCOPE_SINGLETON;
        } elseif ($reflection->getAttributes(Scoped::class) !== []) {
            $scope = Binding::SCOPE_SCOPED;
        }

        $binding = new Binding($abstract, $abstract, $scope, $this->owners[$abstract] ?? 'kernel');
        $this->bindings[$abstract] = $binding;

        foreach ($reflection->getAttributes(Tag::class) as $attribute) {
            $tag = $attribute->newInstance();
            $this->tag($tag->name, [$abstract]);
        }

        return $binding;
    }

    /**
     * @param array<string, object> $parameters
     */
    private function build(Binding $binding, array $parameters, ?string $moduleId): object
    {
        if ($binding->concrete instanceof Closure) {
            $object = ($binding->concrete)($this);

            if (! \is_object($object)) {
                throw new ContainerException(\sprintf('Factory for "%s" must return an object.', $binding->abstract));
            }

            return $object;
        }

        $class = $binding->concrete;
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new ContainerException(\sprintf('Class "%s" is not instantiable.', $class));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (isset($parameters[$name])) {
                $arguments[] = $parameters[$name];
                continue;
            }

            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new ContainerException(\sprintf(
                    'Cannot autowire parameter "$%s" on "%s".',
                    $name,
                    $class,
                ));
            }

            $arguments[] = $this->resolve($type->getName(), [], $moduleId ?? $binding->moduleId);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private function setBinding(Binding $binding): void
    {
        $this->bindings[$binding->abstract] = $binding;

        $this->inspector->dispatch(new ServiceBound(
            abstract: $binding->abstract,
            concrete: $binding->concreteName(),
            scope: $binding->scope,
            moduleId: $binding->moduleId,
            boundAtMs: self::nowMs(),
        ));
    }

    private function recordResolution(string $abstract, object $object, Binding $binding, float $startedAt): object
    {
        $this->inspector->dispatch(new ServiceResolved(
            abstract: $abstract,
            concrete: $object::class,
            scope: $binding->scope,
            resolvedBy: \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'] ?? 'unknown',
            moduleId: $binding->moduleId,
            resolvedAtMs: self::nowMs(),
            durationMs: self::nowMs() - $startedAt,
        ));

        return $object;
    }
}
