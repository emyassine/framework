<?php declare(strict_types=1);

namespace Webkernel\Container\Manifest;

use Webkernel\Contracts\ContainerEvent;

final readonly class ResolutionGraph
{
    /**
     * @param list<ContainerEvent> $events
     */
    public function __construct(
        public string $abstract,
        public array $events,
    ) {}
}
