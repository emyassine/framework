<?php declare(strict_types=1);

namespace Webkernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Webkernel\Composables\ComposableContract;
use Webkernel\Http\Psr\ServerRequest;

/**
 * Current HTTP request. Path helpers plus a PSR-7 adapter.
 */
final class Request implements ComposableContract
{
    public function __construct(
        private readonly string $path,
        private readonly string $method = 'GET',
        private readonly string $host = '',
    ) {
    }

    public static function api_name(): string
    {
        return 'request';
    }

    public static function container_lifetime(): string
    {
        return 'scoped';
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

    public function header(string $name): ?string
    {
        $key = 'HTTP_'.strtoupper(str_replace('-', '_', $name));
        $value = $_SERVER[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $bag = $_POST + $_GET;
        if ($key === null) {
            return $bag;
        }

        return $bag[$key] ?? $default;
    }

    public function file(string $key): ?UploadedFileInterface
    {
        $files = $this->psr()->getUploadedFiles();
        $file = $files[$key] ?? null;

        return $file instanceof UploadedFileInterface ? $file : null;
    }

    public function psr(): ServerRequestInterface
    {
        return ServerRequest::from_globals();
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
