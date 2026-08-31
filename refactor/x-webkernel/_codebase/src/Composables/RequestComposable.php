<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

/**
 * Current HTTP request. `webapp()->request()`.
 *
 * //> Reads $_SERVER / $_GET / $_POST / $_COOKIE / $_FILES / php://input.
 * //> Independent of PSR-7 message standards and Container bindings.
 * //> ip() trusts X-Forwarded-For when present — only safe behind a trusted proxy.
 */
final class RequestComposable implements ComposableContract
{
    /** @var array<string, mixed>|null */
    private static ?array $json = null;

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'request';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$json = null;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return \strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * @param $method string
     * @return bool
     */
    public function is_method(string $method): bool
    {
        return $this->method() === \strtoupper($method);
    }

    /**
     * Path of the current request, or of `$uri` when given.
     *
     * @param $uri string|null
     * @return string
     */
    public function path(?string $uri = null): string
    {
        $raw = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = \parse_url($raw, \PHP_URL_PATH);
        if (! \is_string($path) || $path === '') {
            return '/';
        }

        return \rawurldecode($path);
    }

    /**
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Body input: JSON object when Content-Type is JSON, otherwise `$_POST`.
     *
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->is_json() ? $this->json() : $_POST;
        if (! \is_array($data)) {
            $data = [];
        }
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Decoded JSON body (`php://input`). Empty array when invalid or absent.
     *
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        if (self::$json === null) {
            $content = \file_get_contents('php://input');
            $content = \is_string($content) ? $content : '';
            if ($content !== '' && \json_validate($content)) {
                $decoded = \json_decode($content, true);
                self::$json = \is_array($decoded) ? $decoded : [];
            } else {
                self::$json = [];
            }
        }
        if ($key === null) {
            return self::$json;
        }

        return self::$json[$key] ?? $default;
    }

    /**
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_COOKIE;
        }

        return $_COOKIE[$key] ?? $default;
    }

    /**
     * @param $key string|null
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function files(?string $key = null): mixed
    {
        if ($key === null) {
            return $_FILES;
        }

        return $_FILES[$key] ?? null;
    }

    /**
     * @param $name string
     * @param $default string
     * @return string
     */
    public function header(string $name, string $default = ''): string
    {
        $normalized = \strtoupper(\str_replace('-', '_', $name));
        if (\str_starts_with($normalized, 'HTTP_')) {
            $key = $normalized;
        } else {
            $key = match ($normalized) {
                'CONTENT_TYPE', 'CONTENT_LENGTH' => $normalized,
                default => 'HTTP_'.$normalized,
            };
        }
        if (isset($_SERVER[$key])) {
            return (string) $_SERVER[$key];
        }
        if ($normalized === 'AUTHORIZATION' || $normalized === 'HTTP_AUTHORIZATION') {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (\function_exists('apache_request_headers')) {
                $headers = \apache_request_headers();
                if (\is_array($headers)) {
                    foreach ($headers as $header_name => $value) {
                        if (\strtolower((string) $header_name) === 'authorization') {
                            return (string) $value;
                        }
                    }
                }
            }
        }

        return $default;
    }

    /**
     * @param $name string
     * @return bool
     */
    public function has_header(string $name): bool
    {
        return $this->header($name) !== '';
    }

    /**
     * @return bool
     */
    public function is_json(): bool
    {
        return \str_contains($this->header('Content-Type'), 'application/json');
    }

    /**
     * @return bool
     */
    public function is_secure(): bool
    {
        $https = $_SERVER['HTTPS'] ?? '';

        return $https === 'on' || $https === '1' || $this->header('X-Forwarded-Proto') === 'https';
    }

    /**
     * @return string
     */
    public function scheme(): string
    {
        return $this->is_secure() ? 'https' : 'http';
    }

    /**
     * @return string
     */
    public function host(): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
        if (\str_contains($host, ':')) {
            $host = \explode(':', $host, 2)[0];
        }

        return $host;
    }

    /**
     * @return int
     */
    public function port(): int
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if (\str_contains($host, ':')) {
            $port = \explode(':', $host, 2)[1];
            if (\ctype_digit($port)) {
                return (int) $port;
            }
        }
        $server_port = $_SERVER['SERVER_PORT'] ?? null;
        if (\is_numeric($server_port)) {
            return (int) $server_port;
        }

        return $this->is_secure() ? 443 : 80;
    }

    /**
     * @return string
     */
    public function ip(): string
    {
        $forwarded = $this->header('X-Forwarded-For');
        if ($forwarded !== '') {
            return \trim(\explode(',', $forwarded)[0]);
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    /**
     * @return string
     */
    public function user_agent(): string
    {
        return $this->header('User-Agent');
    }
}
