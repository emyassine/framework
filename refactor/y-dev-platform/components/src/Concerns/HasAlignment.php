<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

use Webkernel\Platform\Components\Enums\Alignment;

/**
 * Horizontal alignment prop.
 *
 * @property array<string, mixed> $props
 */
trait HasAlignment
{
    /**
     * @param $alignment Alignment|string
     *
     * @return static
     */
    public function alignment(Alignment|string $alignment): static
    {
        $this->props['alignment'] = $alignment instanceof Alignment
            ? $alignment->value
            : $alignment;

        return $this;
    }
}
