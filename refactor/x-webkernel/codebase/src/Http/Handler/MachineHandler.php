<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

final class MachineHandler implements HandlerInterface
{
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        return RouteResponse::dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', 'text/plain');
    }
}
