<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * Text suffix prop for input wrappers and related atoms.
 *
 * @property array<string, mixed> $props
 */
trait HasSuffix
{
    /**
     * @param $suffix string
     *
     * @return static
     */
    public function suffix(string $suffix): static
    {
        $this->props['suffix'] = $suffix;

        return $this;
    }
}
