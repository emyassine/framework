<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * URI and path helpers.
 *
 * @mixin HasRequestState
 */
trait InteractsWithUri
{
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

        return $this->path_info;
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
        return \rtrim($this->resolved_scheme().'://'.$this->resolved_http_host().$this->path_info, '/');
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
        if ($this->query === []) {
            return '';
        }

        return \http_build_query($this->query, '', '&', \PHP_QUERY_RFC3986);
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return \rtrim($this->resolved_scheme().'://'.$this->resolved_http_host(), '/');
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
