<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

/**
 * Handles machine & AI endpoints (.md, /llm.txt).
 * Stateless, returns markdown or text content.
 */
final class MachineHandler implements HandlerInterface
{
    /**
     * Handle the machine/AI request.
     */
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // TODO: Implement actual machine/AI endpoint handling
        // For now, return markdown or text based on the request

        if (str_ends_with($uri, '.md')) {
            $content = "# Machine Endpoint\n\nThis is a markdown response for: {$uri}";
            return Response::text($content, 200, ['Content-Type' => 'text/markdown']);
        }

        if ($uri === '/llm.txt') {
            return Response::text("Plain text response for LLM crawlers", 200, [
                'Content-Type' => 'text/plain',
            ]);
        }

        return Response::not_found('Machine endpoint not found');
    }
}
