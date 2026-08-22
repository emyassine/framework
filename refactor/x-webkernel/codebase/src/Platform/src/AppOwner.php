<?php declare(strict_types=1);

namespace Webkernel\Platform;

final readonly class AppOwner
{
    public function __construct(
        public int|string $id,
        public string $name = '',
    ) {
    }
}
