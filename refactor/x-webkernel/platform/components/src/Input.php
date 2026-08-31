<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

/**
 * Bare text control. View: `<x-webkernel::input>`.
 */
final class Input extends \Webkernel\Component\StaticComponent
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::input';
    }

    /**
     * @param $type string
     *
     * @return static
     */
    public function type(string $type): static
    {
        $this->props['type'] = $type;

        return $this;
    }

    /**
     * @param $value string
     *
     * @return static
     */
    public function value(string $value): static
    {
        $this->props['value'] = $value;

        return $this;
    }

    /**
     * @param $placeholder string
     *
     * @return static
     */
    public function placeholder(string $placeholder): static
    {
        $this->props['placeholder'] = $placeholder;

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
