<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

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
