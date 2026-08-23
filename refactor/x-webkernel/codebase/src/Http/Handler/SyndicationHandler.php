<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Webkernel\Container\Container;

/**
 * Handles syndication endpoints (RSS, Atom feeds).
 */
final class SyndicationHandler implements HandlerInterface
{
    /**
     * Handle the syndication request.
     */
    public function handle(array $route_map, Container $container): ResponseInterface
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // TODO: Implement actual syndication feed generation
        // For now, return XML based on the request

        if (str_starts_with($uri, '/rss')) {
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Webkernel RSS Feed</title>
        <link>https://webkernelphp.com</link>
        <description>Webkernel RSS Feed</description>
    </channel>
</rss>
XML;
            return Response::text($xml, 200, ['Content-Type' => 'application/rss+xml']);
        }

        if (str_starts_with($uri, '/atom')) {
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Webkernel Atom Feed</title>
    <link href="https://webkernelphp.com"/>
    <updated>2024-01-01T00:00:00Z</updated>
</feed>
XML;
            return Response::text($xml, 200, ['Content-Type' => 'application/atom+xml']);
        }

        return Response::not_found('Syndication endpoint not found');
    }
}
