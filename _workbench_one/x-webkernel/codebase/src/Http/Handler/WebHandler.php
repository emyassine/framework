<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

final class WebHandler implements HandlerInterface
{
    public function __construct(
        private readonly string $method = 'GET',
    ) {
    }

    public function handle(array $route_map, Container $container): ResponseInterface
    {
        return RouteResponse::from_map($route_map, strtoupper($this->method));
    }
}
