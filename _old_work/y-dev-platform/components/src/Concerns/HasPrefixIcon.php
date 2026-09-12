<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * Prefix icon prop for input wrappers and related atoms.
 *
 * @property array<string, mixed> $props
 */
trait HasPrefixIcon
{
    /**
     * @param $icon string
     *
     * @return static
     */
    public function prefix_icon(string $icon): static
    {
        $this->props['prefix_icon'] = $icon;

        return $this;
    }
}
