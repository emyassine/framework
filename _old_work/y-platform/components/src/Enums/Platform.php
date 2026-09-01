<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Enums;

enum Platform
{
    case Windows;
    case Linux;
    case Mac;
    case Other;

    public static function detect(): Platform
    {
        $userAgent = webapp()->request()->user_agent();

        return match (true) {
            \str_contains($userAgent, 'Windows') => self::Windows,
            \str_contains($userAgent, 'Mac') => self::Mac,
            \str_contains($userAgent, 'Linux') => self::Linux,
            default => self::Other,
        };
    }
}
