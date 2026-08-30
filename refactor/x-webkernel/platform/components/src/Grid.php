<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Responsive CSS grid. View: `<x-webkernel::grid>`. Default 2 columns from `lg`.
 *
 * //> `Grid::make()->columns(2)` is 2 columns from `lg` up, 1 column below.
 */
final class Grid extends LayoutComponent
{
    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = ''): static
    {
        $self = parent::make($name);
        $self->columns(2);

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::grid';
    }
}
