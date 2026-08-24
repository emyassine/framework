<?php declare(strict_types=1);

namespace Webkernel\Http\Psr;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequest implements ServerRequestInterface
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, string> */
    private array $header_names = [];

    private StreamInterface $body;

    /** @var array<string, mixed> */
    private array $attributes = [];

    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookie
     * @param array<string, mixed> $query
     * @param array<string, mixed>|object|null $parsed_body
     * @param array<string, mixed> $files
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(
        private string $method,
        private UriInterface $uri,
        array $headers = [],
        string|StreamInterface $body = '',
        private string $version = '1.1',
        private array $server = [],
        private array $cookie = [],
        private array $query = [],
        private array|object|null $parsed_body = null,
        private array $files = [],
        private string $request_target = '',
    ) {
        $this->body = $body instanceof StreamInterface ? $body : new Stream($body);
        foreach ($headers as $name => $value) {
            $values = is_array($value) ? array_values($value) : [$value];
            $this->header_names[strtolower($name)] = $name;
            $this->headers[$name] = $values;
        }
    }

    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $cookie
     * @param array<string, mixed> $files
     */
    public static function from_globals(
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $files = [],
    ): self {
        $server = $server !== [] ? $server : $_SERVER;
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($server['REQUEST_URI'] ?? '/');
        $https = $server['HTTPS'] ?? '';
        $scheme = ($https !== '' && $https !== 'off') ? 'https' : 'http';
        $host = (string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost');
        $query = (string) ($server['QUERY_STRING'] ?? '');
        $headers = [];
        foreach ($server as $key => $value) {
            if (! is_string($key) || ! is_string($value)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }

        return new self(
            method: $method,
            uri: Uri::from_string($scheme.'://'.$host.$uri),
            headers: $headers,
            body: (string) file_get_contents('php://input'),
            server: $server,
            cookie: $cookie !== [] ? $cookie : $_COOKIE,
            query: $get !== [] ? $get : $_GET,
            parsed_body: $post !== [] ? $post : $_POST,
            files: self::normalize_files($files !== [] ? $files : $_FILES),
            request_target: $uri,
        );
    }

    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->version = $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->header_names[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $original = $this->header_names[strtolower($name)] ?? null;

        return $original === null ? [] : $this->headers[$original];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $values = is_array($value) ? array_values($value) : [(string) $value];
        $lower = strtolower($name);
        if (isset($clone->header_names[$lower])) {
            unset($clone->headers[$clone->header_names[$lower]]);
        }
        $clone->header_names[$lower] = $name;
        $clone->headers[$name] = $values;

        return $clone;
    }

    public function withAddedHeader(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $values = is_array($value) ? array_values($value) : [(string) $value];
        $lower = strtolower($name);
        if (isset($clone->header_names[$lower])) {
            $original = $clone->header_names[$lower];
            $clone->headers[$original] = array_merge($clone->headers[$original], $values);
        } else {
            $clone->header_names[$lower] = $name;
            $clone->headers[$name] = $values;
        }

        return $clone;
    }

    public function withoutHeader(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        $lower = strtolower($name);
        if (isset($clone->header_names[$lower])) {
            unset($clone->headers[$clone->header_names[$lower]], $clone->header_names[$lower]);
        }

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getRequestTarget(): string
    {
        if ($this->request_target !== '') {
            return $this->request_target;
        }
        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $target .= '?'.$this->uri->getQuery();
        }

        return $target;
    }

    public function withRequestTarget(string $requestTarget): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->request_target = $requestTarget;

        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);

        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;
        if (! $preserveHost && $uri->getHost() !== '') {
            return $clone->withHeader('Host', $uri->getHost());
        }

        return $clone;
    }

    public function getServerParams(): array
    {
        return $this->server;
    }

    public function getCookieParams(): array
    {
        return $this->cookie;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookie = $cookies;

        return $clone;
    }

    public function getQueryParams(): array
    {
        return $this->query;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->files;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->files = $uploadedFiles;

        return $clone;
    }

    public function getParsedBody()
    {
        return $this->parsed_body;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsed_body = $data;

        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * @param array<string, mixed> $files
     * @return array<string, mixed>
     */
    private static function normalize_files(array $files): array
    {
        $out = [];
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                $out[$key] = $file;
                continue;
            }
            if (! is_array($file) || ! isset($file['tmp_name'])) {
                continue;
            }
            if (is_array($file['tmp_name'])) {
                continue;
            }
            $out[$key] = new UploadedFile(
                (string) $file['tmp_name'],
                isset($file['size']) ? (int) $file['size'] : null,
                (int) ($file['error'] ?? UPLOAD_ERR_OK),
                isset($file['name']) ? (string) $file['name'] : null,
                isset($file['type']) ? (string) $file['type'] : null,
            );
        }

        return $out;
    }
}
