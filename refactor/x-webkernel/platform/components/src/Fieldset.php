<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Grouped fields. View: `<x-webkernel::fieldset>`. Default two columns from `lg`.
 */
final class Fieldset extends LayoutComponent
{
    /**
     * @param $label string
     *
     * @return static
     */
    public static function make(string $label = ''): static
    {
        $self = new static();
        $self->name = $label;
        $self->columns(2);
        if ($label !== '') {
            $self->props['label'] = $label;
        }

        return $self;
    }

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
