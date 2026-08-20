<?php declare(strict_types=1);

namespace Webkernel\Lifecycle;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Webkernel\Lifecycle\Installer\LCBaseInstaller;

/**
 * Composer plugin: custom package install paths for Webkernel types.
 *
 * Vite assets: post-autoload-dump → ComposerScripts calls ViteWebapp only.
 */
final class LCInstaller implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        $composer->getInstallationManager()->addInstaller(new LCBaseInstaller($io, $composer));
    }

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}
}
