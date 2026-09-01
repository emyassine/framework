<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * `::make()` for a View class. Same `.view.php` as `<x-webkernel::{name}>`.
 *
 * @property string $name
 *
 * @method static static make(string $name = '')
 */
trait HasMethodMake
{
    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = ''): static
    {
        $self = new static();
        $self->name = $name;

        return $self;
    }
}
