<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasIcon;
use Webkernel\Platform\Components\Concerns\HasSize;
use Webkernel\Platform\Components\Concerns\HasMethodMake;

/**
 * Status chip. View: `<x-webkernel::badge>`.
 */
final class Badge extends StaticComponent
{
    use HasMethodMake;

    use HasIcon;
    use HasSize;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::badge';
    }

    /**
     * @param $color string
     *
     * @return static
     */
    public function color(string $color): static
    {
        $this->props['color'] = $color;

        return $this;
    }

    /**
     * @param $href string
     *
     * @return static
     */
    public function href(string $href): static
    {
        $this->props['href'] = $href;

        return $this;
    }

    /**
     * @param $disabled bool
     *
     * @return static
     */
    public function disabled(bool $disabled = true): static
    {
        $this->props['disabled'] = $disabled;

        return $this;
    }
}
