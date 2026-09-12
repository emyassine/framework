<?php declare(strict_types=1);

namespace Webkernel\Contracts;

use Closure;
use Webkernel\Container\InspectorBus;
use Webkernel\Container\Manifest\ContainerSnapshot;

interface ContainerInterface
{
    /**
     * @param array<string, object> $parameters
     */
    public function make(string $abstract, array $parameters = []): object;

    public function makeScoped(string $abstract): object;

    public function bind(string $abstract, string|Closure $concrete): void;

    public function singleton(string $abstract, string|Closure|null $concrete = null): void;

    public function scoped(string $abstract, string|Closure|null $concrete = null): void;

    public function transient(string $abstract, string|Closure|null $concrete = null): void;

    public function bindLazy(string $abstract, string $sourceModule): void;

    public function instance(string $abstract, object $instance): void;

    /**
     * @param list<string> $abstracts
     */
    public function tag(string $tag, array $abstracts): void;

    /**
     * @return iterable<object>
     */
    public function tagged(string $tag): iterable;

    public function bound(string $abstract): bool;

    public function resolved(string $abstract): bool;

    public function inspector(): InspectorBusInterface;

    public function snapshot(): ContainerSnapshot;

    public function forModule(string $moduleId): ModuleScopeInterface;
}
