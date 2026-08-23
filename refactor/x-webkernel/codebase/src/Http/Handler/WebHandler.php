<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;
use Webkernel\Router\Router as WebkernelRouter;

/**
 * Handles standard web requests (server-rendered HTML pages).
 */
final class WebHandler implements HandlerInterface
{
    private string $method;

    public function __construct(string $method = 'GET')
    {
        $this->method = strtoupper($method);
    }

    /**
     * Handle the web request.
     */
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Get router and try to match route
        $router = $container->get(WebkernelRouter::class);
        $route = $router->match($uri, $this->method);

        if ($route === null) {
            return Response::not_found('Not Found');
        }

        // TODO: Apply middleware, resolve controller, execute
        // For now, return a simple response
        return Response::html('<h1>Web Handler</h1><p>Path: ' . htmlspecialchars($uri) . '</p>');
    }
}
