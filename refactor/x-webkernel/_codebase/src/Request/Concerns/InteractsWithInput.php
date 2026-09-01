<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Query string, body, cookie, and file input.
 *
 * @mixin HasRequestState
 */
trait InteractsWithInput
{
    /**
     * @param $key string|null
     * @param $default mixed
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
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
        $data = $this->body_is_json() ? $this->json() : $this->request;
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
            if ($this->content === '' || ! \json_validate($this->content)) {
                $this->json_cache = [];
            } else {
                $decoded = \json_decode($this->content, true);
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
            return $this->cookies;
        }

        return $this->cookies[$key] ?? $default;
    }

    /**
     * @param $key string|null
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function files(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }

        return $this->files[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return \array_merge($this->query, $this->input());
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
        if ($this->body_is_json()) {
            $this->json_cache = \array_merge($this->json(), $input);
        } else {
            $this->request = \array_merge($this->request, $input);
        }

        return $this;
    }

    /**
     * @param $input array<string, mixed>
     * @return $this
     */
    public function replace(array $input): self
    {
        if ($this->body_is_json()) {
            $this->json_cache = $input;
        } else {
            $this->request = $input;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function content(): string
    {
        return $this->content;
    }
}
