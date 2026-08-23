<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;
use Webkernel\Router\Router as WebkernelRouter;

/**
 * Handles API requests (stateless, programmatic JSON endpoints).
 */
final class ApiHandler implements HandlerInterface
{
    private string $method;

    public function __construct(string $method = 'GET')
    {
        $this->method = strtoupper($method);
    }

    /**
     * Handle the API request.
     */
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Get router and try to match route
        $router = $container->get(WebkernelRouter::class);
        $route = $router->match($uri, $this->method);

        if ($route === null) {
            return Response::json([
                'error' => 'Not Found',
                'message' => 'The requested API endpoint does not exist.',
            ], 404);
        }

        // TODO: Apply API middleware (auth, CORS, rate limiting), resolve controller, execute
        // For now, return a simple JSON response
        return Response::json([
            'message' => 'API Handler',
            'path' => $uri,
            'method' => $this->method,
        ]);
    }
}
