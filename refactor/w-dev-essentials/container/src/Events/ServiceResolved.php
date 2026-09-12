<?php declare(strict_types=1);

namespace Webkernel\Container\Events;

use Webkernel\Contracts\ContainerEvent;

final readonly class ServiceResolved implements ContainerEvent
{
    public function __construct(
        public string $abstract,
        public string $concrete,
        public string $scope,
        public string $resolvedBy,
        public string $moduleId,
        public float $resolvedAtMs,
        public float $durationMs,
    ) {}
}
