<?php declare(strict_types=1);

namespace Webkernel\Container;

use Closure;
use Webkernel\Container\Manifest\ContainerSnapshot;
use Webkernel\Container\Manifest\ResolutionGraph;
use Webkernel\Contracts\ContainerEvent;
use Webkernel\Contracts\InspectorBusInterface;

final class InspectorBus implements InspectorBusInterface
{
    /** @var list<Closure(ContainerEvent): void> */
    private array $taps = [];

    /** @var array<class-string, list<Closure(ContainerEvent): void>> */
    private array $listeners = [];

    /** @var list<ContainerEvent> */
    private array $events = [];

    private ?Closure $snapshotResolver = null;

    /**
     * @param Closure(): ContainerSnapshot $resolver
     */
    public function setSnapshotResolver(Closure $resolver): void
    {
        $this->snapshotResolver = $resolver;
    }

    public function tap(Closure $listener): void
    {
        $this->taps[] = $listener;
    }

    public function on(string $eventClass, Closure $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(ContainerEvent $event): void
    {
        $this->events[] = $event;

        foreach ($this->taps as $tap) {
            $tap($event);
        }

        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            $listener($event);
        }
    }

    public function graph(string $abstract): ResolutionGraph
    {
        return new ResolutionGraph(
            abstract: $abstract,
            events: \array_values(\array_filter(
                $this->events,
                static fn(ContainerEvent $event): bool => \property_exists($event, 'abstract')
                    && $event->abstract === $abstract,
            )),
        );
    }

    public function snapshot(): ContainerSnapshot
    {
        if ($this->snapshotResolver === null) {
            return new ContainerSnapshot([], [], [], $this->events);
        }

        return ($this->snapshotResolver)();
    }

    public function auditLog(): array
    {
        return $this->events;
    }

    public function flush(): void
    {
        $this->events = [];
    }
}
