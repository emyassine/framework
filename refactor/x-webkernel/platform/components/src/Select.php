<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Native select. View: `<x-webkernel::select>`.
 */
final class Select extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::select';
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
     * @param $options array<string, string>
     *
     * @return static
     */
    public function options(array $options): static
    {
        $this->props['options'] = $options;

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
}
