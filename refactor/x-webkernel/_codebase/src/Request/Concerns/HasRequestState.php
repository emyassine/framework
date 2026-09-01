<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Shared request bags and protected resolvers for composed concerns.
 *
 * @property array<string, mixed> $query
 * @property array<string, mixed> $request
 * @property array<string, mixed> $cookies
 * @property array<string, mixed> $files
 * @property array<string, mixed> $server
 * @property array<string, string> $headers
 * @property array<string, mixed> $attributes
 * @property string $content
 * @property string $method
 * @property string $path_info
 * @property array<string, mixed>|null $json_cache
 */
trait HasRequestState
{
    /**
     * @param $name string
     * @param $default string
     * @return string
     */
    protected function normalized_header(string $name, string $default = ''): string
    {
        $key = \strtolower(\str_replace('_', '-', $name));
        if (\str_starts_with($key, 'http-')) {
            $key = \substr($key, 5);
        }

        return $this->headers[$key] ?? $default;
    }

    /**
     * @return bool
     */
    protected function resolved_is_secure(): bool
    {
        $https = (string) ($this->server['HTTPS'] ?? '');
        if ($https === 'on' || $https === '1') {
            return true;
        }

        return $this->normalized_header('X-Forwarded-Proto') === 'https';
    }

    /**
     * @return string
     */
    protected function resolved_scheme(): string
    {
        return $this->resolved_is_secure() ? 'https' : 'http';
    }

    /**
     * @return string
     */
    protected function resolved_host_without_port(): string
    {
        $host = (string) ($this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost');
        if (\str_contains($host, ':')) {
            $host = \explode(':', $host, 2)[0];
        }

        return $host;
    }

    /**
     * @return string
     */
    protected function resolved_http_host(): string
    {
        return (string) ($this->server['HTTP_HOST'] ?? $this->resolved_host_without_port());
    }

    /**
     * @return bool
     */
    protected function body_is_json(): bool
    {
        return \str_contains($this->normalized_header('Content-Type'), 'application/json');
    }
}
