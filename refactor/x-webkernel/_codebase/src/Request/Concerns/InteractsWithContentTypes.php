<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Content negotiation helpers.
 *
 * @mixin HasRequestState
 */
trait InteractsWithContentTypes
{
    /**
     * @return bool
     */
    public function is_json(): bool
    {
        return $this->body_is_json();
    }

    /**
     * @return bool
     */
    public function wants_json(): bool
    {
        $accept = $this->normalized_header('Accept');
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
        return \strcasecmp($this->normalized_header('X-Requested-With'), 'XMLHttpRequest') === 0;
    }

    /**
     * @return bool
     */
    public function pjax(): bool
    {
        return $this->normalized_header('X-PJAX') !== '';
    }
}
