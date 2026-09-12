<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Enums;

enum IconSize: string
{
    case ExtraSmall = 'xs';

    case Small = 'sm';

    case Medium = 'md';

    case Large = 'lg';

    case ExtraLarge = 'xl';

    case TwoExtraLarge = '2xl';

    /**
     * @deprecated Use `TwoExtraLarge` instead.
     */
    public const ExtraExtraLarge = self::TwoExtraLarge;
}
