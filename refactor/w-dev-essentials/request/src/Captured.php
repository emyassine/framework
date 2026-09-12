<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request;

/**
 * Immutable snapshot of request bags captured once per HTTP cycle.
 */
final readonly class Captured
{
    /**
     * @param $query array<string, mixed>
     * @param $post array<string, mixed>
     * @param $cookies array<string, mixed>
     * @param $files array<string, mixed>
     * @param $server array<string, mixed>
     * @param $headers array<string, string>
     * @param $attributes array<string, mixed>
     */
    public function __construct(
        public array $query,
        public array $post,
        public array $cookies,
        public array $files,
        public array $server,
        public array $headers,
        public string $content,
        public string $method,
        public string $path_info,
        public array $attributes = [],
    ) {
    }

    /**
     * @param $query array<string, mixed>
     * @param $post array<string, mixed>
     * @param $cookies array<string, mixed>
     * @param $files array<string, mixed>
     * @param $server array<string, mixed>
     * @param $content string
     * @param $attributes array<string, mixed>
     * @return self
     */
    public static function from_bags(
        array $query,
        array $post,
        array $cookies,
        array $files,
        array $server,
        string $content = '',
        array $attributes = [],
    ): self {
        return new self(
            $query,
            $post,
            $cookies,
            $files,
            $server,
            self::headers_from_server($server),
            $content,
            \strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')),
            self::path_from_uri((string) ($server['REQUEST_URI'] ?? '/')),
            $attributes,
        );
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
}
