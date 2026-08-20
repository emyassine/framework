<?php declare(strict_types=1);

namespace Webkernel\Lifecycle;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Webkernel\Lifecycle\Boot\BootGenerator;
use Webkernel\Lifecycle\Installer\LCBaseInstaller;

/**
 * Composer plugin. Registers the custom installer (see getcomposer.org
 * custom-installers) and writes the boot file on post-autoload-dump.
 */
final class LCInstaller implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'on_post_autoload_dump',
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $composer->getInstallationManager()->addInstaller(new LCBaseInstaller($io, $composer));
    }

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}

    public function on_post_autoload_dump(Event $event): void
    {
        BootGenerator::write($event->getComposer(), $event->getIO());
        if (function_exists('webkernel_boot_flush')) {
            webkernel_boot_flush();
        }
    }
}
