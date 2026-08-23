<?php declare(strict_types=1);

namespace Webkernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware pipeline executor.
 * Executes middleware in the order they are provided (first to last).
 */
final class Pipeline
{
    /**
     * Run the middleware pipeline.
     *
     * @param ServerRequestInterface $request The PSR-7 request
     * @param array<MiddlewareInterface> $middleware Array of middleware to execute
     * @param callable $controller The final controller/handler to execute
     * @return ResponseInterface The PSR-7 response
     */
    public function run(
        ServerRequestInterface $request,
        array $middleware,
        callable $controller
    ): ResponseInterface {
        $next = $controller;

        // Build the middleware stack in reverse order
        // so they execute in the order they were added
        foreach (array_reverse($middleware) as $mw) {
            $next = fn($req) => $mw->handle($req, $next);
        }

        return $next($request);
    }
}
