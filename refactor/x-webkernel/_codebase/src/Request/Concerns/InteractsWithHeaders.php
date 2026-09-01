<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Header access backed by a normalized map built once at capture.
 *
 * @mixin HasRequestState
 */
trait InteractsWithHeaders
{
    /**
     * @param $name string
     * @param $default string
     * @return string
     */
    public function header(string $name, string $default = ''): string
    {
        return $this->normalized_header($name, $default);
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
        return $this->headers;
    }
}
