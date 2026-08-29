<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\Commands\DumpAutoloadCommand\{
	CanCompileRuntime, CanDumpAssets, CanDumpPanels,
	CanDumpTypography, CanStampPlatform, CanWritePhp,
	HasPackages, HasPaths, HasProviders, _DumpAutoloadCommand
};
use Webkernel\Console\ExitCode;
use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;

/**
 * Writes `{vendor}/composer/webkernel_*.php`. Composer `post-autoload-dump`
 * invokes this command; so does `php webkernel dump-autoload`.
 */
final readonly class DumpAutoloadCommand
{
    use _DumpAutoloadCommand;
    use CanCompileRuntime;
    use CanDumpAssets;
    use CanDumpPanels;
    use CanDumpTypography;
    use CanStampPlatform;
    use CanWritePhp;
    use HasPackages;
    use HasPaths;
    use HasProviders;

    /**
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: 'dump-autoload',
        description: 'Write Webkernel dump files (classmap, providers, views, routes)',
    )]
    public function __invoke(): ExitCode
    {
        $this->ensure_path_helpers();
        $root = $this->project_root();
        $vendor_dir = $this->vendor_dir($root);
        $composer_dir = $vendor_dir.DIRECTORY_SEPARATOR.'composer';

        if (! is_dir($composer_dir) && ! mkdir($composer_dir, 0775, true) && ! is_dir($composer_dir)) {
            $this->terminal()->warning('cannot create '.$composer_dir);

            return ExitCode::ERROR;
        }

        $instance_id = InstanceId::record($root);
        $vendor_rel = $this->relative($root, $vendor_dir) ?? basename($vendor_dir);
        $this->stamp_platform_config($root, str_replace('\\', '/', $vendor_rel), $instance_id);
        $packages = $this->packages($vendor_dir);

        $boot = [
            'instance_id' => $instance_id,
            'webapp_root' => $root,
            'vendor_dir' => $vendor_dir,
            'vendor_rel' => str_replace('\\', '/', $vendor_rel),
            'generated_at' => gmdate('c'),
        ];

        $classmap = $this->classmap($packages);
        $providers = $this->providers_meta($packages, $classmap);
        $composables = $this->composables_list($classmap);

        $this->write_php($composer_dir.DIRECTORY_SEPARATOR.self::BOOT_BASENAME, $boot);
        $this->write_classmap(
            $composer_dir.DIRECTORY_SEPARATOR.self::CLASSMAP_BASENAME,
            $classmap,
            $vendor_dir,
            $root,
        );
        $this->write_files(
            $composer_dir.DIRECTORY_SEPARATOR.self::FILES_BASENAME,
            $this->files_list($packages),
            $vendor_dir,
            $root,
        );
        $this->write_namespaced_paths(
            $composer_dir.DIRECTORY_SEPARATOR.self::VIEWS_BASENAME,
            $this->collect_provider_paths($providers, 'VIEWS'),
            $vendor_dir,
            $root,
        );
        $this->write_namespaced_paths(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPONENTS_BASENAME,
            $this->collect_provider_paths($providers, 'COMPONENTS'),
            $vendor_dir,
            $root,
        );
        $this->write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::ROUTES_BASENAME,
            $this->collect_provider_files($providers, 'ROUTES'),
            $vendor_dir,
            $root,
        );
        $this->write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::LANG_BASENAME,
            $this->collect_lang_paths($providers),
            $vendor_dir,
            $root,
        );
        $this->write_composables(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPOSABLES_BASENAME,
            $composables,
        );
        $this->write_webapp_ide($composables);
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::PROVIDERS_BASENAME,
            array_column($providers, 'class'),
        );
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMMANDS_BASENAME,
            $this->collect_provider_classes($providers, 'COMMANDS'),
        );
        $panels = $this->panels_dump($providers, $classmap, $root);
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::PANELS_BASENAME,
            $panels,
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::PANEL_ROUTES_BASENAME,
            $this->panel_routes_dump($panels, $classmap),
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::BRANDING_BASENAME,
            $this->branding_dump($packages, $root),
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::ICONS_BASENAME,
            $this->icons_dump($providers),
        );
        $this->dump_typography();
        $this->dump_wds_css($providers);
        $this->strip_dev_autoload_files($composer_dir);
        $this->ensure_path_helpers();
        $this->rebuild_compiled_routes($composer_dir);
        $this->compile_views($providers, $root, $vendor_dir);

        $io = $this->terminal();
        $io->success('wrote composer/'.self::BOOT_BASENAME.' (instance '.$instance_id.')');

        try {
            $ide = IdeHelper::generate($vendor_dir);
            $io->info(\sprintf(
                'ide helper %s (%d classes, %d bytes%s)',
                $ide['path'],
                $ide['classes'],
                $ide['bytes'],
                $ide['skipped'] ? ', unchanged' : '',
            ));
        } catch (\Throwable $e) {
            $io->warning('ide helper: '.$e->getMessage());
        }

        return ExitCode::SUCCESS;
    }
}
