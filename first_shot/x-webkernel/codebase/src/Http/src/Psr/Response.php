<?php declare(strict_types=1);

namespace Webkernel\Http\Psr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, string> */
    private array $header_names = [];

    private StreamInterface $body;

    /**
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(
        private int $status = 200,
        array $headers = [],
        string|StreamInterface $body = '',
        private string $version = '1.1',
        private string $reason = '',
    ) {
        $this->body = $body instanceof StreamInterface ? $body : new Stream($body);
        foreach ($headers as $name => $value) {
            $values = is_array($value) ? array_values($value) : [$value];
            $this->header_names[strtolower($name)] = $name;
            $this->headers[$name] = $values;
        }
        if ($this->reason === '') {
            $this->reason = self::REASON[$this->status] ?? '';
        }
    }

    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    public function withProtocolVersion(string $version): ResponseInterface
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

    public function withHeader(string $name, $value): ResponseInterface
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

    public function withAddedHeader(string $name, $value): ResponseInterface
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

    public function withoutHeader(string $name): ResponseInterface
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

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->status = $code;
        $clone->reason = $reasonPhrase !== '' ? $reasonPhrase : (self::REASON[$code] ?? '');

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reason;
    }

    private const REASON = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];
}
