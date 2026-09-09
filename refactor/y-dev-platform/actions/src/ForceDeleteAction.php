<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Actions;

/**
 * Prefabricated force delete action.
 */
final class ForceDeleteAction extends Action
{
    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = 'force_delete'): static
    {
        return parent::make($name)
            ->label('Force delete')
            ->icon('trash-2')
            ->color('danger')
            ->requires_confirmation();
    }
}
