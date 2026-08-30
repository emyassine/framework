<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Grouped fields. View: `<x-webkernel::fieldset>`.
 */
final class Fieldset extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::fieldset';
    }

    /**
     * @param $contained bool
     *
     * @return static
     */
    public function contained(bool $contained = true): static
    {
        $this->props['contained'] = $contained;

        return $this;
    }

    /**
     * @param $required bool
     *
     * @return static
     */
    public function required(bool $required = true): static
    {
        $this->props['required'] = $required;

        return $this;
    }

}
