<?php declare(strict_types=1);

namespace Webkernel\Container\Manifest;

use Webkernel\Contracts\ContainerEvent;

final readonly class ContainerSnapshot
{
    /**
     * @param array<string, string> $bindings
     * @param list<string> $resolved
     * @param array<string, list<string>> $tags
     * @param list<ContainerEvent> $events
     */
    public function __construct(
        public array $bindings,
        public array $resolved,
        public array $tags,
        public array $events,
    ) {}
}
