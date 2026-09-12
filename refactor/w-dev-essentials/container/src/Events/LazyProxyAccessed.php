<?php declare(strict_types=1);

namespace Webkernel\Container\Events;

use Webkernel\Contracts\ContainerEvent;

final readonly class LazyProxyAccessed implements ContainerEvent
{
    public function __construct(
        public string $abstract,
        public string $sourceModule,
        public float $accessedAtMs,
    ) {}
}
