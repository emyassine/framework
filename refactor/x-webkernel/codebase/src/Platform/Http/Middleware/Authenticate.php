<?php declare(strict_types=1);

namespace Webkernel\Platform\Http\Middleware;

/**
 * First-cut auth. Always continues. Real session check is spec H.
 */
final class Authenticate
{
    public function handle(callable $next): mixed
    {
        return $next();
    }
}
