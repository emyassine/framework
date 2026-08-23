<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Webkernel\Route\Route;

/**
 * Dispatch through the in-tree router and wrap the body as a handler response.
 */
final class RouteResponse
{
    public static function dispatch(string $method, string $content_type = 'text/html; charset=UTF-8'): Response
    {
        $out = Route::dispatch($method);
        $status = http_response_code();
        if (! is_int($status) || $status < 100) {
            $status = 200;
        }
        if ($out instanceof PsrResponse) {
            $headers = [];
            foreach ($out->getHeaders() as $name => $values) {
                $headers[$name] = implode(', ', $values);
            }

            return new Response($out->getStatusCode(), $headers, (string) $out->getBody());
        }

        return new Response($status, ['Content-Type' => $content_type], (string) $out);
    }
}
