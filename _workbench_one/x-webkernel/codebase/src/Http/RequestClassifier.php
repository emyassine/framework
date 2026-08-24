<?php declare(strict_types=1);

namespace Webkernel\Http;

use Webkernel\Http\Handler\HandlerInterface;

/**
 * Classifies incoming requests based on URI and HTTP method.
 * Returns appropriate handler for each request type.
 */
final class RequestClassifier
{
    /**
     * Classify a request and return the appropriate handler.
     */
    public function classify(string $uri, string $method = 'GET'): HandlerInterface
    {
        // Machine & AI endpoints
        if (str_ends_with($uri, '.md') || $uri === '/llm.txt') {
            return new Handler\MachineHandler();
        }

        // API endpoints
        if (str_starts_with($uri, '/api/')) {
            return new Handler\ApiHandler($method);
        }

        // Syndication endpoints
        if (str_starts_with($uri, '/rss') || str_starts_with($uri, '/atom')) {
            return new Handler\SyndicationHandler();
        }

        // Real-time protocols
        if (str_starts_with($uri, '/ws') || str_starts_with($uri, '/sse')) {
            return new Handler\RealTimeHandler();
        }

        // Default: Web handler
        return new Handler\WebHandler($method);
    }
}
