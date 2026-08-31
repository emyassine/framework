<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

/**
 * Ring around an input. View: `<x-webkernel::input.wrapper>`.
 */
final class InputWrapper extends \Webkernel\Component\StaticComponent
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::input.wrapper';
    }

    /**
     * @param $prefix string
     *
     * @return static
     */
    public function prefix(string $prefix): static
    {
        $this->props['prefix'] = $prefix;

        return $this;
    }

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
     * @param $icon string
     *
     * @return static
     */
    public function prefix_icon(string $icon): static
    {
        $this->props['prefix_icon'] = $icon;

        return $this;
    }

    /**
     * @param $icon string
     *
     * @return static
     */
    public function suffix_icon(string $icon): static
    {
        $this->props['suffix_icon'] = $icon;

        return $this;
    }

}
