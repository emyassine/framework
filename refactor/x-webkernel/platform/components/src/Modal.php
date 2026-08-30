<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Dialog. View: `<x-webkernel::modal>`.
 */
final class Modal extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::modal';
    }

    /**
     * @param $heading string
     *
     * @return static
     */
    public function heading(string $heading): static
    {
        $this->props['heading'] = $heading;

        return $this;
    }

    /**
     * @param $description string
     *
     * @return static
     */
    public function description(string $description): static
    {
        $this->props['description'] = $description;

        return $this;
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
     * @param $width string
     *
     * @return static
     */
    public function width(string $width): static
    {
        $this->props['width'] = $width;

        return $this;
    }

    /**
     * @param $open bool
     *
     * @return static
     */
    public function open(bool $open = true): static
    {
        $this->props['open'] = $open;

        return $this;
    }

    /**
     * @param $html string
     *
     * @return static
     */
    public function footer(string $html): static
    {
        $this->props['footer'] = $html;

        return $this;
    }

    /**
     * @param $id string
     *
     * @return static
     */
    public function id(string $id): static
    {
        $this->props['id'] = $id;

        return $this;
    }

    /**
     * @param $slide_over bool
     *
     * @return static
     */
    public function slide_over(bool $slide_over = true): static
    {
        $this->props['slide_over'] = $slide_over;

        return $this;
    }

}
