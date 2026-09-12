<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * Label prop shared by fields and related atoms.
 *
 * @property array<string, mixed> $props
 */
trait HasLabel
{
    /**
     * @param $label string
     *
     * @return static
     */
    public function label(string $label): static
    {
        $this->props['label'] = $label;

        return $this;
    }
}
