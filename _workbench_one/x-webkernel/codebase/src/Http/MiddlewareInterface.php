<?php declare(strict_types=1);

namespace Webkernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for all middleware.
 * Middleware must implement the handle method which receives the request
 * and the next middleware/handler in the chain.
 */
interface MiddlewareInterface
{
    /**
     * Handle the request and call the next middleware/handler.
     *
     * @param ServerRequestInterface $request The PSR-7 request
     * @param callable $next The next middleware/handler in the chain
     * @return ResponseInterface The PSR-7 response
     */
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface;
}
