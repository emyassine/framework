<?php declare(strict_types=1);

namespace Webkernel\Contracts;

use Closure;
use Webkernel\Container\Manifest\ContainerSnapshot;
use Webkernel\Container\Manifest\ResolutionGraph;

interface InspectorBusInterface
{
    public function tap(Closure $listener): void;

    public function on(string $eventClass, Closure $listener): void;

    public function graph(string $abstract): ResolutionGraph;

    public function snapshot(): ContainerSnapshot;

    /**
     * @return list<ContainerEvent>
     */
    public function auditLog(): array;

    public function flush(): void;
}
