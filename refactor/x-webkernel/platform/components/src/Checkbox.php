<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Boolean input. Blade: `<x-webkernel::checkbox>`.
 */
final class Checkbox extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::checkbox';
    }

    /**
     * @param $checked bool
     *
     * @return static
     */
    public function checked(bool $checked = true): static
    {
        $this->props['checked'] = $checked;

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
