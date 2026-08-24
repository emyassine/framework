<?php declare(strict_types=1);

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
     * @param MiddlewareList $middleware
     */
    public function __construct(
        public ?string $name = null,
        public string $description = '',
        public array $middleware = [],
    ) {
    }
}
