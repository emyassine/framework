<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

final class RealTimeHandler implements HandlerInterface
{
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        return RouteResponse::from_map($route_map, $_SERVER['REQUEST_METHOD'] ?? 'GET', 'text/plain');
    }
}
