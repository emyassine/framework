<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Attribute;

/**
 * Marks a method as a CLI command. Name defaults to `{class}:{method}`
 * (`Make::user` → `make:user`; `__invoke` drops the method segment).
 *
 * @phpstan-type MiddlewareList list<class-string>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class ConsoleCommand
{
    /**
     * @param string|null    $name
     * @param string         $description
     * @param MiddlewareList $middleware
     */
    public function __construct(
        public ?string $name = null,
        public string $description = '',
        public array $middleware = [],
    ) {
    }
}
