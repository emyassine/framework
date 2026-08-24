<?php declare(strict_types=1);

namespace Webkernel\Route\Dispatch;

use Webkernel\Route\Compile\Generator;

/**
 * @phpstan-import-type Extra from Generator
 */
final class Matched
{
    /**
     * @param array<string, string> $variables
     * @param Extra                 $extra
     */
    public function __construct(
        public mixed $handler,
        public array $variables = [],
        public array $extra = [],
    ) {
    }
}
