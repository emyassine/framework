<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Enums;

enum Width: string
{
    case ThreeExtraSmall = '3xs';

    case TwoExtraSmall = '2xs';

    case ExtraSmall = 'xs';

    case Small = 'sm';

    case Medium = 'md';

    case Large = 'lg';

    case ExtraLarge = 'xl';

    case TwoExtraLarge = '2xl';

    case ThreeExtraLarge = '3xl';

    case FourExtraLarge = '4xl';

    case FiveExtraLarge = '5xl';

    case SixExtraLarge = '6xl';

    case SevenExtraLarge = '7xl';

    case None = 'none';

    case Full = 'full';

    case MinContent = 'min';

    case MaxContent = 'max';

    case FitContent = 'fit';

    case Prose = 'prose';

    case Container = 'container';

    case ScreenSmall = 'screen-sm';

    case ScreenMedium = 'screen-md';

    case ScreenLarge = 'screen-lg';

    case ScreenExtraLarge = 'screen-xl';

    case ScreenTwoExtraLarge = 'screen-2xl';

    case Screen = 'screen';
}
