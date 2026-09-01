<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

use Psr\Http\Message\ServerRequestInterface;
use Webkernel\Request\Captured;
use Webkernel\Request\TrustedProxies;

/**
 * Immutable-per-request HTTP envelope. Superglobals are copied once at capture.
 *
 * //> Call Request::capture() at the HTTP door and Request::flush() in a finally block.
 * //> Forwarded client IP headers apply only after Request::trust_proxies().
 */
final class Request
{
    private static ?self $current = null;

    private static ?TrustedProxies $trusted_proxies = null;

    private Captured $captured;

    /** @var array<string, mixed> */
    private array $post;

    /** @var array<string, mixed> */
    private array $attributes;

    /** @var array<string, mixed>|null */
    private ?array $json_cache = null;

    private function __construct(Captured $captured)
    {
        $this->captured = $captured;
        $this->post = $captured->post;
        $this->attributes = $captured->attributes;
    }

    /**
     * @return self
     */
    public static function capture(): self
    {
        $content = \file_get_contents('php://input');

        return self::set_current(new self(Captured::from_bags(
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
            $_SERVER,
            \is_string($content) ? $content : '',
        )));
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
     * @return TrustedProxies
     */
    public static function trusted_proxies(): TrustedProxies
    {
        self::ensure_trusted_proxies();

        return self::$trusted_proxies;
    }

    /**
     * @param $psr ServerRequestInterface
     * @return self
     */
    public static function from_psr7(ServerRequestInterface $psr): self
    {
        $server = $psr->getServerParams();
        $server['REQUEST_METHOD'] ??= $psr->getMethod();
        $server['REQUEST_URI'] ??= (string) $psr->getUri();
        $server['HTTP_HOST'] ??= $psr->getUri()->getHost();
        if ($psr->getUri()->getPort() !== null) {
            $server['HTTP_HOST'] = $psr->getUri()->getHost().':'.$psr->getUri()->getPort();
        }
        foreach ($psr->getHeaders() as $name => $values) {
            $header = (string) $name;
            $normalized = \strtoupper(\str_replace('-', '_', $header));
            $server['HTTP_'.$normalized] = \implode(', ', $values);
        }
        $parsed = $psr->getParsedBody();
        $post = \is_array($parsed) ? $parsed : [];

        return new self(Captured::from_bags(
            $psr->getQueryParams(),
            $post,
            $psr->getCookieParams(),
            $psr->getUploadedFiles(),
            $server,
            (string) $psr->getBody(),
            $psr->getAttributes(),
        ));
    }

    /**
     * Synthetic request for tests and sub-requests.
     *
     * @param $method string
     * @param $uri string
     * @param $query array<string, mixed>
     * @param $post array<string, mixed>
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
        array $post = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
    ): self {
        $server['REQUEST_METHOD'] = $method;
        $server['REQUEST_URI'] = $uri;

        return new self(Captured::from_bags(
            $query,
            $post,
            $cookies,
            $files,
            $server,
            $content ?? '',
        ));
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->captured->method;
    }

    /**
     * @param $method string
     * @return bool
     */
    public function is_method(string $method): bool
    {
        return $this->captured->method === \strtoupper($method);
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
     * @param $name string
     * @param $default string
     * @return string
     */
    public function header(string $name, string $default = ''): string
    {
        $key = \strtolower(\str_replace('_', '-', $name));
        if (\str_starts_with($key, 'http-')) {
            $key = \substr($key, 5);
        }

        return $this->captured->headers[$key] ?? $default;
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
     * @return string|null
     */
    public function bearer_token(): ?string
    {
        $header = $this->header('Authorization');
        if ($header === '' || ! \str_starts_with($header, 'Bearer ')) {
            return null;
        }
        $token = \trim(\substr($header, 7));

        return $token === '' ? null : $token;
    }

    /**
     * @return string
     */
    public function user_agent(): string
    {
        return $this->header('User-Agent');
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->captured->headers;
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
    public function wants_json(): bool
    {
        $accept = $this->header('Accept');
        if ($accept === '') {
            return false;
        }

        return \str_contains($accept, 'application/json') || \str_contains($accept, '+json');
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return \strcasecmp($this->header('X-Requested-With'), 'XMLHttpRequest') === 0;
    }

    /**
     * @return bool
     */
    public function pjax(): bool
    {
        return $this->header('X-PJAX') !== '';
    }

    /**
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->captured->query;
        }

        return $this->captured->query[$key] ?? $default;
    }

    /**
     * Body input: JSON object when Content-Type is JSON, otherwise the POST bag.
     *
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->is_json() ? $this->json() : $this->post;
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Decoded JSON body. Empty array when invalid or absent.
     *
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->json_cache === null) {
            if ($this->captured->content === '' || ! \json_validate($this->captured->content)) {
                $this->json_cache = [];
            } else {
                $decoded = \json_decode($this->captured->content, true);
                $this->json_cache = \is_array($decoded) ? $decoded : [];
            }
        }
        if ($key === null) {
            return $this->json_cache;
        }

        return $this->json_cache[$key] ?? $default;
    }

    /**
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->captured->cookies;
        }

        return $this->captured->cookies[$key] ?? $default;
    }

    /**
     * @param $key string|null
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function files(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->captured->files;
        }

        return $this->captured->files[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return \array_merge($this->captured->query, $this->input());
    }

    /**
     * @param $key string
     * @return bool
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->all());
    }

    /**
     * @param $key string
     * @return bool
     */
    public function missing(string $key): bool
    {
        return ! $this->has($key);
    }

    /**
     * @param $key string
     * @return bool
     */
    public function filled(string $key): bool
    {
        if (! $this->has($key)) {
            return false;
        }
        $value = $this->input($key);

        return $value !== null && $value !== '';
    }

    /**
     * @param $input array<string, mixed>
     * @return $this
     */
    public function merge(array $input): self
    {
        if ($this->is_json()) {
            $this->json_cache = \array_merge($this->json(), $input);
        } else {
            $this->post = \array_merge($this->post, $input);
        }

        return $this;
    }

    /**
     * @param $input array<string, mixed>
     * @return $this
     */
    public function replace(array $input): self
    {
        if ($this->is_json()) {
            $this->json_cache = $input;
        } else {
            $this->post = $input;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function content(): string
    {
        return $this->captured->content;
    }

    /**
     * Path of the current request, or of `$uri` when given.
     *
     * @param $uri string|null
     * @return string
     */
    public function path(?string $uri = null): string
    {
        if ($uri !== null) {
            $parsed = \parse_url($uri, \PHP_URL_PATH);
            if (! \is_string($parsed) || $parsed === '') {
                return '/';
            }

            return \rawurldecode($parsed);
        }

        return $this->captured->path_info;
    }

    /**
     * @return string
     */
    public function decoded_path(): string
    {
        return $this->path();
    }

    /**
     * @return list<string>
     */
    public function segments(): array
    {
        $trimmed = \trim($this->path(), '/');
        if ($trimmed === '') {
            return [];
        }

        return \array_values(\array_filter(
            \explode('/', $trimmed),
            static fn (string $segment): bool => $segment !== '',
        ));
    }

    /**
     * @param $index int 1-based segment index.
     * @param $default string|null
     * @return string|null
     */
    public function segment(int $index, ?string $default = null): ?string
    {
        return $this->segments()[$index - 1] ?? $default;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return \rtrim($this->scheme().'://'.$this->http_host().$this->captured->path_info, '/');
    }

    /**
     * @return string
     */
    public function full_url(): string
    {
        $query = $this->query_string();
        if ($query === '') {
            return $this->url();
        }

        return $this->url().'?'.$query;
    }

    /**
     * @return string
     */
    public function query_string(): string
    {
        if ($this->captured->query === []) {
            return '';
        }

        return \http_build_query($this->captured->query, '', '&', \PHP_QUERY_RFC3986);
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return \rtrim($this->scheme().'://'.$this->http_host(), '/');
    }

    /**
     * @param $patterns string
     * @return bool
     */
    public function is(string ...$patterns): bool
    {
        $path = $this->path();
        foreach ($patterns as $pattern) {
            if ($this->path_matches_pattern($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function is_secure(): bool
    {
        $https = (string) ($this->captured->server['HTTPS'] ?? '');
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
        $host = (string) ($this->captured->server['HTTP_HOST'] ?? $this->captured->server['SERVER_NAME'] ?? 'localhost');
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
        return (string) ($this->captured->server['HTTP_HOST'] ?? $this->host());
    }

    /**
     * @return int
     */
    public function port(): int
    {
        $host = (string) ($this->captured->server['HTTP_HOST'] ?? '');
        if (\str_contains($host, ':')) {
            $port = \explode(':', $host, 2)[1];
            if (\ctype_digit($port)) {
                return (int) $port;
            }
        }
        $server_port = $this->captured->server['SERVER_PORT'] ?? null;
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
        $remote = (string) ($this->captured->server['REMOTE_ADDR'] ?? '127.0.0.1');
        if (! self::trusted_proxies()->is_trusted($remote)) {
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

    /**
     * @param $query array<string, mixed>|null
     * @param $post array<string, mixed>|null
     * @param $cookies array<string, mixed>|null
     * @param $files array<string, mixed>|null
     * @param $server array<string, mixed>|null
     * @param $content string|null
     * @return self
     */
    public function duplicate(
        ?array $query = null,
        ?array $post = null,
        ?array $cookies = null,
        ?array $files = null,
        ?array $server = null,
        ?string $content = null,
    ): self {
        $clone = new self(Captured::from_bags(
            $query ?? $this->captured->query,
            $post ?? $this->post,
            $cookies ?? $this->captured->cookies,
            $files ?? $this->captured->files,
            $server ?? $this->captured->server,
            $content ?? $this->captured->content,
            $this->attributes,
        ));
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
     * @return void
     */
    private static function ensure_trusted_proxies(): void
    {
        if (! isset(self::$trusted_proxies)) {
            self::$trusted_proxies = new TrustedProxies();
        }
    }

    /**
     * @param $path string
     * @param $pattern string
     * @return bool
     */
    private function path_matches_pattern(string $path, string $pattern): bool
    {
        $pattern = \trim($pattern, '/');
        $path = \trim($path, '/');
        if ($pattern === '*') {
            return true;
        }
        if ($pattern === $path) {
            return true;
        }
        if (\str_contains($pattern, '*')) {
            $regex = '/^'.\str_replace('\*', '.*', \preg_quote($pattern, '/')).'$/';

            return (bool) \preg_match($regex, $path);
        }

        return false;
    }
}
