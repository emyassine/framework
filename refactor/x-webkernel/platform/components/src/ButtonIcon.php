<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

/**
 * Icon-only control. View: `<x-webkernel::button-icon>`.
 */
final class ButtonIcon extends \Webkernel\Component\StaticComponent
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::button-icon';
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
     * @param $placement string
     *
     * @return static
     */
    public function tooltip(string $tooltip, string $placement = 'top'): static
    {
        $this->props['tooltip'] = $tooltip;
        $this->props['tooltip_placement'] = $placement;

        return $this;
    }
}
