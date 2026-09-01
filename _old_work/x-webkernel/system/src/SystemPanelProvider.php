<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\System;

use Webkernel\Auth\Http\Middleware\Authenticate;
use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;
use Webkernel\System\Pages\Dashboard;

/**
 * System Admin Panel. Scope is inferred: this class lives in vendor webkernel.
 */
final class SystemPanelProvider extends PanelProvider
{
    /**
     * @param $panel Panel
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system')
            ->path('system')
            ->default()
            ->icon('settings')
            ->pages([
                Dashboard::class,
            ])
            ->auth_middleware([
                Authenticate::class,
            ]);
    }
}
