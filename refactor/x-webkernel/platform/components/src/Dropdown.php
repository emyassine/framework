<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Trigger plus floating panel. View: `<x-webkernel::dropdown>`.
 */
final class Dropdown extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::dropdown';
    }

    /**
     * @param $placement string
     *
     * @return static
     */
    public function placement(string $placement): static
    {
        $this->props['placement'] = $placement;

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
     * @param $html string
     *
     * @return static
     */
    public function trigger(string $html): static
    {
        $this->props['trigger'] = $html;

        return $this;
    }

}
