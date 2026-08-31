<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Enums;

enum FontWeight: string
{
    case Thin = 'thin';

    case ExtraLight = 'extralight';

    case Light = 'light';

    case Normal = 'normal';

    case Medium = 'medium';

    case SemiBold = 'semibold';

    case Bold = 'bold';

    case ExtraBold = 'extrabold';

    case Black = 'black';
}
