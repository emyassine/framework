<?php declare(strict_types=1);

namespace Webkernel\Platform;

use Webkernel\Config\Config;
use Webkernel\Platform\Colors\Color;

abstract class PanelProvider
{
    abstract public function panel(Panel $panel): Panel;

    final protected function apply_platform_config(Panel $panel): Panel
    {
        return $panel
            ->favicon(Config::get('branding.favicon'))
            ->brand_logo(Config::get('branding.logo_light'))
            ->dark_mode_brand_logo(Config::get('branding.logo_dark'))
            ->brand_logo_height(Config::get('branding.logo_height', '2rem'))
            ->colors(Config::get('branding.colors', ['primary' => Color::Blue]))
            ->dark_mode(Config::get('ui.dark_mode', true));
    }
}
