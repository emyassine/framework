<?php declare(strict_types=1);

namespace Webkernel\Container\Events;

use Webkernel\Contracts\ContainerEvent;

final readonly class ServiceReplaced implements ContainerEvent
{
    public function __construct(
        public string $abstract,
        public string $moduleId,
        public float $replacedAtMs,
    ) {}
}
