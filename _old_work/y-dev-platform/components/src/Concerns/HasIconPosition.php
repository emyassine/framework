<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

use Webkernel\Platform\Components\Enums\IconPosition;

/**
 * Icon position prop shared by button, tabs, actions, and related atoms.
 *
 * @property array<string, mixed> $props
 */
trait HasIconPosition
{
    /**
     * @param $position IconPosition|string
     *
     * @return static
     */
    public function icon_position(IconPosition|string $position): static
    {
        $this->props['icon_position'] = $position instanceof IconPosition
            ? $position->value
            : $position;

        return $this;
    }
}
