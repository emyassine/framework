<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Client connection metadata (host, scheme, IP).
 *
 * @mixin HasRequestState
 *
 * @method string header(string $name, string $default = '')
 */
trait InteractsWithClient
{
    /**
     * @return bool
     */
    public function is_secure(): bool
    {
        $https = (string) ($this->server['HTTPS'] ?? '');
        if ($https === 'on' || $https === '1') {
            return true;
        }

        return $this->header('X-Forwarded-Proto') === 'https';
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
        $host = (string) ($this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost');
        if (\str_contains($host, ':')) {
            $host = \explode(':', $host, 2)[0];
        }

        return $host;
    }

    /**
     * @return string
     */
    public function http_host(): string
    {
        return (string) ($this->server['HTTP_HOST'] ?? $this->host());
    }

    /**
     * @return int
     */
    public function port(): int
    {
        $host = (string) ($this->server['HTTP_HOST'] ?? '');
        if (\str_contains($host, ':')) {
            $port = \explode(':', $host, 2)[1];
            if (\ctype_digit($port)) {
                return (int) $port;
            }
        }
        $server_port = $this->server['SERVER_PORT'] ?? null;
        if (\is_numeric($server_port)) {
            return (int) $server_port;
        }

        return $this->is_secure() ? 443 : 80;
    }

    /**
     * Client IP. Forwarded headers apply only when the direct peer is a trusted proxy.
     *
     * @return string
     */
    public function ip(): string
    {
        return $this->ips()[0] ?? '127.0.0.1';
    }

    /**
     * @return list<string>
     */
    public function ips(): array
    {
        $remote = (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
        if (! \Webkernel\Request::trusted_proxies()->is_trusted($remote)) {
            return [$remote];
        }
        $forwarded = $this->header('X-Forwarded-For');
        if ($forwarded === '') {
            $real = $this->header('X-Real-IP');
            if ($real !== '') {
                return [\trim($real)];
            }

            return [$remote];
        }
        $ips = [];
        foreach (\explode(',', $forwarded) as $part) {
            $ip = \trim($part);
            if ($ip !== '') {
                $ips[] = $ip;
            }
        }

        return $ips !== [] ? $ips : [$remote];
    }
}
