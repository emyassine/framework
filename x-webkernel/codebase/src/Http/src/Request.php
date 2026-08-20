<?php declare(strict_types=1);

namespace Webkernel\Http;

/**
 * Current HTTP request. Path helpers only — no PSR-7.
 */
final class Request
{
    public function __construct(
        private readonly string $path,
        private readonly string $method = 'GET',
        private readonly string $host = '',
    ) {
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (false !== $q = strpos($uri, '?')) {
            $uri = substr($uri, 0, $q);
        }
        $path = rawurldecode($uri);
        $path = trim($path, '/');

        return new self(
            $path === '' ? '/' : $path,
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            (string) ($_SERVER['HTTP_HOST'] ?? ''),
        );
    }

    public function path(): string
    {
        return $this->path;
    }

    /** Path with leading slash, for the router. */
    public function uri(): string
    {
        return $this->path === '/' ? '/' : '/'.$this->path;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function is(string ...$patterns): bool
    {
        $path = $this->path === '/' ? '/' : $this->path;
        foreach ($patterns as $pattern) {
            if (self::matches($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function matches(string $path, string $pattern): bool
    {
        $pattern = trim($pattern, '/');
        $path = $path === '/' ? '' : $path;
        if ($pattern === '' || $pattern === '*') {
            return true;
        }
        $regex = '#^'.str_replace('\*', '.*', preg_quote($pattern, '#')).'$#';

        return preg_match($regex, $path) === 1;
    }
}
