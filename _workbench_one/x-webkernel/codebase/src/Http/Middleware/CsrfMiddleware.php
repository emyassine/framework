<?php declare(strict_types=1);

namespace Webkernel\Http\Middleware;

use Webkernel\Http\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Validates CSRF tokens for stateful web requests.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Handle the request and validate CSRF token.
     */
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        // Only check for mutating methods
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->getHeaderLine('X-CSRF-TOKEN');

            if (!$this->validate_token($token)) {
                return new \Webkernel\Http\Handler\Response(403, [], 'Forbidden - Invalid CSRF token');
            }
        }

        return $next($request);
    }

    /**
     * Validate the CSRF token.
     */
    private function validate_token(string $token): bool
    {
        // Get session token (from session or cookie)
        $session_token = $_SESSION['csrf_token'] ?? '';

        return hash_equals($session_token, $token);
    }
}
