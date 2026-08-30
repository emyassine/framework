<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Icon-only control. View: `<x-webkernel::icon-button>`.
 */
final class IconButton extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::icon-button';
    }

    /**
     * @param $icon string
     *
     * @return static
     */
    public function icon(string $icon): static
    {
        $this->props['icon'] = $icon;

        return $this;
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
     * @param $size Size|string
     *
     * @return static
     */
    public function size(Size|string $size): static
    {
        $this->props['size'] = $size instanceof Size ? $size->value : $size;

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

    /**
     * @param $tooltip string
     *
     * @return static
     */
    public function tooltip(string $tooltip): static
    {
        $this->props['tooltip'] = $tooltip;

        return $this;
    }

}
