<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Actions;

/**
 * Prefabricated restore action.
 */
final class RestoreAction extends Action
{
    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = 'restore'): static
    {
        return parent::make($name)
            ->label('Restore')
            ->icon('rotate-ccw')
            ->color('success');
    }
}
