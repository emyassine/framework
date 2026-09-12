<?php declare(strict_types=1);

namespace Webkernel\Container\Events;

use Webkernel\Contracts\ContainerEvent;

final readonly class ModuleBorderViolationAttempted implements ContainerEvent
{
    public function __construct(
        public string $callerModule,
        public string $targetModule,
        public string $requestedAbstract,
        public float $attemptedAtMs,
    ) {}
}
