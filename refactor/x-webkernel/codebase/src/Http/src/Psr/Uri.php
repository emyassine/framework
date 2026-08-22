<?php declare(strict_types=1);

namespace Webkernel\Http\Psr;

use Psr\Http\Message\UriInterface;

final class Uri implements UriInterface
{
    public function __construct(
        private string $scheme = '',
        private string $user_info = '',
        private string $host = '',
        private ?int $port = null,
        private string $path = '',
        private string $query = '',
        private string $fragment = '',
    ) {
    }

    public static function from_string(string $uri): self
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            return new self(path: $uri);
        }
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? null;
        $user_info = is_string($user) ? $user : '';
        if (is_string($pass) && $pass !== '') {
            $user_info .= ':'.$pass;
        }

        return new self(
            scheme: strtolower((string) ($parts['scheme'] ?? '')),
            user_info: $user_info,
            host: strtolower((string) ($parts['host'] ?? '')),
            port: isset($parts['port']) ? (int) $parts['port'] : null,
            path: (string) ($parts['path'] ?? ''),
            query: (string) ($parts['query'] ?? ''),
            fragment: (string) ($parts['fragment'] ?? ''),
        );
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }
        $authority = $this->host;
        if ($this->user_info !== '') {
            $authority = $this->user_info.'@'.$authority;
        }
        if ($this->port !== null) {
            $authority .= ':'.$this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->user_info;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $clone = clone $this;
        $clone->scheme = strtolower($scheme);

        return $clone;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone = clone $this;
        $clone->user_info = $password === null || $password === '' ? $user : $user.':'.$password;

        return $clone;
    }

    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;
        $clone->host = strtolower($host);

        return $clone;
    }

    public function withPort(?int $port): UriInterface
    {
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    public function withPath(string $path): UriInterface
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function withQuery(string $query): UriInterface
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    public function __toString(): string
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme.':';
        }
        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//'.$authority;
        }
        $uri .= $this->path;
        if ($this->query !== '') {
            $uri .= '?'.$this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#'.$this->fragment;
        }

        return $uri;
    }
}
