<?php declare(strict_types=1);

namespace Webkernel\Platform\System;

use Webkernel\Platform\Http\Middleware\Authenticate;
use Webkernel\Platform\Pages\Dashboard;
use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;

/**
 * System Admin Panel.
 * Scope: platform — manages modules and platform-wide configuration.
 * Branding is not in this method. See NOTES.md §5.
 */
final class SystemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->apply_platform_config($panel)
            ->id('system')
            ->path('system')
            ->scope('platform')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([])
            ->resources([])
            ->middleware([])
            ->auth_middleware([
                Authenticate::class,
            ]);
    }
}
