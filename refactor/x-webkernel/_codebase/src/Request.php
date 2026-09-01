<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

use Webkernel\Request\Concerns\HasRequestState;
use Webkernel\Request\Concerns\InteractsWithClient;
use Webkernel\Request\Concerns\InteractsWithContentTypes;
use Webkernel\Request\Concerns\InteractsWithHeaders;
use Webkernel\Request\Concerns\InteractsWithInput;
use Webkernel\Request\Concerns\InteractsWithUri;
use Webkernel\Request\TrustedProxies;

/**
 * Immutable-per-request HTTP envelope. Superglobals are copied once at capture.
 *
 * //> Call Request::capture() at the HTTP door and Request::flush() in a finally block.
 * //> Forwarded client IP headers apply only after Request::trust_proxies().
 */
final class Request
{
    use HasRequestState;
    use InteractsWithClient;
    use InteractsWithContentTypes;
    use InteractsWithHeaders;
    use InteractsWithInput;
    use InteractsWithUri;

    private static ?self $current = null;

    private static ?TrustedProxies $trusted_proxies = null;

    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $request;

    /** @var array<string, mixed> */
    private array $cookies;

    /** @var array<string, mixed> */
    private array $files;

    /** @var array<string, mixed> */
    private array $server;

    /** @var array<string, string> */
    private array $headers;

    /** @var array<string, mixed> */
    private array $attributes;

    private string $content;

    private string $method;

    private string $path_info;

    /** @var array<string, mixed>|null */
    private ?array $json_cache = null;

    /**
     * @param $query array<string, mixed>
     * @param $request array<string, mixed>
     * @param $cookies array<string, mixed>
     * @param $files array<string, mixed>
     * @param $server array<string, mixed>
     * @param $content string
     * @param $attributes array<string, mixed>
     */
    public function __construct(
        array $query,
        array $request,
        array $cookies,
        array $files,
        array $server,
        string $content = '',
        array $attributes = [],
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->content = $content;
        $this->attributes = $attributes;
        $this->headers = self::headers_from_server($server);
        $this->method = \strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $this->path_info = self::path_from_uri((string) ($server['REQUEST_URI'] ?? '/'));
    }

    /**
     * @return self
     */
    public static function capture(): self
    {
        $content = \file_get_contents('php://input');

        return self::set_current(new self(
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
            $_SERVER,
            \is_string($content) ? $content : '',
        ));
    }

    /**
     * @return self
     */
    public static function current(): self
    {
        return self::$current ?? self::capture();
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$current = null;
    }

    /**
     * @param $proxies list<string>
     * @return void
     */
    public static function trust_proxies(array $proxies): void
    {
        self::ensure_trusted_proxies();
        self::$trusted_proxies = new TrustedProxies($proxies);
    }

    /**
     * @param $psr \Psr\Http\Message\ServerRequestInterface
     * @return self
     */
    public static function from_psr7(\Psr\Http\Message\ServerRequestInterface $psr): self
    {
        $server = $psr->getServerParams();
        $server['REQUEST_METHOD'] ??= $psr->getMethod();
        $server['REQUEST_URI'] ??= (string) $psr->getUri();
        $server['HTTP_HOST'] ??= $psr->getUri()->getHost();
        if ($psr->getUri()->getPort() !== null) {
            $server['HTTP_HOST'] = $psr->getUri()->getHost().':'.$psr->getUri()->getPort();
        }
        foreach ($psr->getHeaders() as $name => $values) {
            $normalized = \strtoupper(\str_replace('-', '_', $name));
            $server['HTTP_'.$normalized] = \implode(', ', $values);
        }
        $parsed = $psr->getParsedBody();
        $request = \is_array($parsed) ? $parsed : [];
        $content = (string) $psr->getBody();

        return new self(
            $psr->getQueryParams(),
            $request,
            $psr->getCookieParams(),
            $psr->getUploadedFiles(),
            $server,
            $content,
            $psr->getAttributes(),
        );
    }

    /**
     * Synthetic request for tests and sub-requests.
     *
     * @param $method string
     * @param $uri string
     * @param $query array<string, mixed>
     * @param $request array<string, mixed>
     * @param $cookies array<string, mixed>
     * @param $files array<string, mixed>
     * @param $server array<string, mixed>
     * @param $content string|null
     * @return self
     */
    public static function create(
        string $method = 'GET',
        string $uri = '/',
        array $query = [],
        array $request = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
    ): self {
        $server['REQUEST_METHOD'] = $method;
        $server['REQUEST_URI'] = $uri;

        return new self(
            $query,
            $request,
            $cookies,
            $files,
            $server,
            $content ?? '',
        );
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @param $method string
     * @return bool
     */
    public function is_method(string $method): bool
    {
        return $this->method === \strtoupper($method);
    }

    /**
     * @param $key string
     * @param $value mixed
     * @return $this
     */
    public function set_attribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param $key string
     * @param $default mixed
     * @return mixed
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param $query array<string, mixed>|null
     * @param $request array<string, mixed>|null
     * @param $cookies array<string, mixed>|null
     * @param $files array<string, mixed>|null
     * @param $server array<string, mixed>|null
     * @param $content string|null
     * @return self
     */
    public function duplicate(
        ?array $query = null,
        ?array $request = null,
        ?array $cookies = null,
        ?array $files = null,
        ?array $server = null,
        ?string $content = null,
    ): self {
        $clone = new self(
            $query ?? $this->query,
            $request ?? $this->request,
            $cookies ?? $this->cookies,
            $files ?? $this->files,
            $server ?? $this->server,
            $content ?? $this->content,
            $this->attributes,
        );
        if ($this->json_cache !== null) {
            $clone->json_cache = $this->json_cache;
        }

        return $clone;
    }

    /**
     * @param $request self
     * @return self
     */
    private static function set_current(self $request): self
    {
        return self::$current = $request;
    }

    /**
     * @param $server array<string, mixed>
     * @return array<string, string>
     */
    private static function headers_from_server(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (! \is_string($key) || ! \is_scalar($value)) {
                continue;
            }
            $value = (string) $value;
            if (\str_starts_with($key, 'HTTP_')) {
                $name = \strtolower(\str_replace('_', '-', \substr($key, 5)));
                $headers[$name] = $value;
                continue;
            }
            if ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
                $headers[\strtolower(\str_replace('_', '-', $key))] = $value;
            }
        }
        if (! isset($headers['authorization'])) {
            if (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = (string) $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (\function_exists('apache_request_headers')) {
                $apache = \apache_request_headers();
                if (\is_array($apache)) {
                    foreach ($apache as $name => $header_value) {
                        if (\strtolower((string) $name) === 'authorization') {
                            $headers['authorization'] = (string) $header_value;
                            break;
                        }
                    }
                }
            }
        }

        return $headers;
    }

    /**
     * @param $uri string
     * @return string
     */
    private static function path_from_uri(string $uri): string
    {
        $path = \parse_url($uri, \PHP_URL_PATH);
        if (! \is_string($path) || $path === '') {
            return '/';
        }

        return \rawurldecode($path);
    }

    /**
     * @return TrustedProxies
     */
    public static function trusted_proxies(): TrustedProxies
    {
        self::ensure_trusted_proxies();

        return self::$trusted_proxies;
    }

    /**
     * @return void
     */
    private static function ensure_trusted_proxies(): void
    {
        if (! isset(self::$trusted_proxies)) {
            self::$trusted_proxies = new TrustedProxies();
        }
    }
}
