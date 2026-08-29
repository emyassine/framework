<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

use Webkernel\Config\Config;
use Webkernel\Platform\Colors\Color;

/**
 * One admin UI. Dump-autoload calls register(); panel() only declares the UI.
 *
 * //> Branding is applied here. Providers do not call apply_platform_config().
 * //> Scope is inferred from the Composer package. Providers do not call scope().
 */
abstract class PanelProvider
{
    /**
     * @param $panel Panel
     * @return Panel
     */
    abstract public function panel(Panel $panel): Panel;

    /**
     * Dump-autoload entry. Same shape as Filament's PanelProvider::register().
     *
     * @return Panel
     */
    final public function register(): Panel
    {
        return $this->apply_platform_config($this->panel(Panel::make()));
    }

    /**
     * Per-panel gate. Roles and permissions fill this in later.
     *
     * @return bool
     */
    public function can_access(): bool
    {
        return true;
    }

    /**
     * A platform panel ships from a webkernel vendor package. Everything else is a module panel.
     *
     * @param $package string Composer name (`webkernel/panels`)
     * @param $type string Composer type
     * @return 'platform'|'module'
     */
    final public static function scope_for_package(string $package, string $type = ''): string
    {
        if (\str_starts_with($package, 'webkernel/')) {
            return 'platform';
        }
        if ($type === 'webkernel-platform-module' || $type === 'webkernel-platform-module-feature') {
            return 'platform';
        }

        return 'module';
    }

    /**
     * @param $panel Panel
     * @return Panel
     */
    private function apply_platform_config(Panel $panel): Panel
    {
        return $panel
            ->favicon(Config::get('branding.favicon'))
            ->brand_logo(Config::get('branding.logo_light'))
            ->dark_mode_brand_logo(Config::get('branding.logo_dark'))
            ->brand_logo_height(Config::get('branding.logo_height', '2rem'))
            ->colors(Config::get('branding.colors', ['primary' => Color::Blue]))
            ->dark_mode(Config::get('ui.dark_mode', false));
    }
}
