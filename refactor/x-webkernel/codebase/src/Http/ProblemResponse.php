<?php declare(strict_types=1);

namespace Webkernel\Http;

use Webkernel\Http\Handler\ResponseInterface;
use Webkernel\Http\Handler\Response;

/**
 * RFC 7807 Problem Details response factory.
 * Creates standardized error responses following the Problem Details specification.
 */
final class ProblemResponse
{
    /**
     * Create a 404 Not Found problem response.
     */
    public static function not_found(string $path): ResponseInterface
    {
        return new Response(404, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/route-not-found',
            'title'    => 'Not Found',
            'status'   => 404,
            'detail'   => "The requested endpoint '{$path}' does not exist.",
            'instance' => $path,
        ]));
    }

    /**
     * Create a 422 Validation Failed problem response.
     */
    public static function validation_error(array $errors): ResponseInterface
    {
        return new Response(422, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/validation-failed',
            'title'    => 'Validation Failed',
            'status'   => 422,
            'detail'   => 'The request data failed validation.',
            'errors'   => $errors,
        ]));
    }

    /**
     * Create a 429 Too Many Requests problem response.
     */
    public static function rate_limited(int $retry_after): ResponseInterface
    {
        return new Response(429, [
            'Content-Type' => 'application/problem+json',
            'Retry-After' => (string) $retry_after,
        ], json_encode([
            'type'       => 'https://webkernelphp.com/errors/rate-limited',
            'title'      => 'Too Many Requests',
            'status'     => 429,
            'detail'     => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retry_after,
        ]));
    }

    /**
     * Create a 401 Unauthorized problem response.
     */
    public static function unauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return new Response(401, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/unauthorized',
            'title'    => 'Unauthorized',
            'status'   => 401,
            'detail'   => $message,
        ]));
    }

    /**
     * Create a 403 Forbidden problem response.
     */
    public static function forbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return new Response(403, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/forbidden',
            'title'    => 'Forbidden',
            'status'   => 403,
            'detail'   => $message,
        ]));
    }

    /**
     * Create a 500 Internal Server Error problem response.
     */
    public static function server_error(string $message = 'Internal Server Error'): ResponseInterface
    {
        return new Response(500, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/server-error',
            'title'    => 'Internal Server Error',
            'status'   => 500,
            'detail'   => $message,
        ]));
    }
}
