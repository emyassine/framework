<?php declare(strict_types=1);

namespace Webkernel\Http\Middleware;

use Webkernel\Http\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Injects a unique X-Request-ID header for end-to-end tracing.
 */
final class RequestIdMiddleware implements MiddlewareInterface
{
    /**
     * Handle the request and add request ID.
     */
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        // Generate a unique request ID
        $request_id = $this->generate_id();

        // Add request ID to request attributes
        $request = $request->withAttribute('request_id', $request_id);

        // Execute the next middleware/handler
        $response = $next($request);

        // Add request ID to response headers
        return $response->withHeader('X-Request-ID', $request_id);
    }

    /**
     * Generate a unique request ID.
     */
    private function generate_id(): string
    {
        return bin2hex(random_bytes(16));
    }
}
