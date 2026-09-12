<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Notifications\Providers;

use Webkernel\PlatformProvider;

/**
 * UI Views. CSS and JS live next to the View. PHP classes use HasMethodMake.
 *
 * //> Three roots so tags stay `x-webkernel::page`, not `x-webkernel::layout.page`.
 */
final class NotificationsProvider extends PlatformProvider
{
    public const VIEWS = [
        'webkernel' => [
            __DIR__.'/../../resources/views',
        ],
    ];

    public const COMPONENTS = [
        'webkernel' => [
            __DIR__.'/../../resources/views',
        ],
    ];
}
