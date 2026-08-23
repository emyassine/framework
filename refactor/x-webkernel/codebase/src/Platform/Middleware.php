<?php declare(strict_types=1);

namespace Webkernel\Platform;

use Webkernel\Composables\ComposableContract;

/**
 * HTTP middleware stack. Recorded at boot, executed as a flat pipeline.
 */
final class Middleware implements ComposableContract
{
    /** @var list<callable|string> */
    private array $stack = [];

    public static function api_name(): string
    {
        return 'middleware';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    /**
     * @param list<callable|string> $middlewares
     */
    public function with_middleware(array $middlewares): void
    {
        $this->stack = array_values(array_merge($this->stack, $middlewares));
    }

    /**
     * @return list<callable|string>
     */
    public function stack(): array
    {
        return $this->stack;
    }
}
