<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Commands\DumpAutoloadCommand;

/**
 * Dump file names and panel class FQCNs. Used by every dump-autoload trait.
 */
trait _DumpAutoloadCommand
{
    /**
     * Attribution block for generated dumps.
     *
     * @return string
     */
    public static function generated_header(): string
    {
        $end = ((int) \date('Y')) + 1;

        return \implode("\n", [
            '//> This file is part of Webkernel.',
            '//> (c) 2025 - '.$end.' Numerimondes, El Moumen Yassine',
            '//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>',
            '//> For the full copyright and license information, please view the LICENSE',
            '//> file that was distributed with this source code.',
        ]);
    }

    private const PANEL_CLASS = 'Webkernel\\Platform\\Panel';

    private const PANEL_PROVIDER_CLASS = 'Webkernel\\Platform\\PanelProvider';

    private const RESOURCE_CLASS = 'Webkernel\\Platform\\Resources\\Resource';

    public const BOOT_BASENAME = 'webkernel.php';
    public const CLASSMAP_BASENAME = 'webkernel_classmap.php';
    public const FILES_BASENAME = 'webkernel_files.php';
    public const VIEWS_BASENAME = 'webkernel_views.php';
    public const COMPONENTS_BASENAME = 'webkernel_components.php';
    public const ROUTES_BASENAME = 'webkernel_routes.php';
    public const COMPOSABLES_BASENAME = 'webkernel_composables.php';
    public const PROVIDERS_BASENAME = 'webkernel_providers.php';
    public const COMMANDS_BASENAME = 'webkernel_commands.php';
    public const PANELS_BASENAME = 'webkernel_panels.php';
    public const PANEL_ROUTES_BASENAME = 'webkernel_panel_routes.php';
    public const BRANDING_BASENAME = 'webkernel_branding.php';
    public const ICONS_BASENAME = 'webkernel_icons.php';
    public const LANG_BASENAME = 'webkernel_lang.php';
    public const COMPILED_VIEWS_BASENAME = 'webkernel_compiled_views.php';
}
