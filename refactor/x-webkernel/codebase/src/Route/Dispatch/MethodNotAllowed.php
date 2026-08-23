<?php declare(strict_types=1);

namespace Webkernel\Route\Dispatch;

final class MethodNotAllowed
{
    /**
     * @param non-empty-list<string> $allowed
     */
    public function __construct(
        public array $allowed,
    ) {
    }
}
