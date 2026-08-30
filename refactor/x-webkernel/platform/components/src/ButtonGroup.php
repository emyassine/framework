<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Horizontal group of buttons. View: `<x-webkernel::button.group>`.
 */
final class ButtonGroup extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::button.group';
    }
}
