<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Lifecycle;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Webkernel\Console\Commands\DumpAutoloadCommand;
use Webkernel\Lifecycle\Installer\LCBaseInstaller;

/**
 * Composer plugin. Registers the custom installer (see getcomposer.org
 * custom-installers) and runs dump-autoload on post-autoload-dump.
 */
final class LCInstaller implements PluginInterface, EventSubscriberInterface
{
    /** @return array<string,string> */
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
        (new DumpAutoloadCommand())->__invoke();
        if (function_exists('webkernel_boot_flush')) {
            webkernel_boot_flush();
        }
    }
}
