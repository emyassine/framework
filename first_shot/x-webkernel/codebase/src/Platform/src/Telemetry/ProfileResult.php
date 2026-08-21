<?php declare(strict_types=1);

namespace Webkernel\Platform\Telemetry;

final readonly class ProfileResult
{
    public function __construct(
        public mixed $value,
        public int $duration_ns,
        public int $memory_bytes,
    ) {
    }
}
