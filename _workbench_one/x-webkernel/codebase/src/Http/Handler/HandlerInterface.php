<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

/**
 * Interface for all request handlers.
 * Each handler is responsible for processing a specific type of request.
 */
interface HandlerInterface
{
    /**
     * Handle the request and return a response.
     */
    public function handle(array $route_map, Container $container): ResponseInterface;
}
