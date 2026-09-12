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
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Webkernel\Lifecycle\Hook\LCHook;
use Webkernel\Lifecycle\Hook\LCHookDispatcher;
use Webkernel\Lifecycle\Installer\LCBaseInstaller;

/**
 * Webkernel Composer plugin entry-point.
 *
 * 1. Registers the custom installer for webkernel-* package types.
 * 2. Subscribes to all Composer script events.
 * 3. Dispatches extra.webkernel.lifecycle.events.{event} callables across packages.
 */
final class LCInstaller implements PluginInterface, EventSubscriberInterface
{
    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        $events = [];
        foreach (LCHook::cases() as $hook) {
            $events[$hook->value] = 'on_script_event';
        }
        return $events;
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $composer->getInstallationManager()->addInstaller(new LCBaseInstaller($io, $composer));
    }

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}

    public function on_script_event(Event $event): void
    {
        $vendor   = rtrim((string) $event->getComposer()->getConfig()->get('vendor-dir'), '/');
        $autoload = $vendor . '/autoload.php';

        if (is_file($autoload)) {
            require_once $autoload;
        }

        (new LCHookDispatcher())->dispatch(
            $event->getName(),
            $event,
            $this->hook_packages($event),
        );
    }

    /**
     * @return list<array{name: string, extra: array<string, mixed>}>
     */
    private function hook_packages(Event $event): array
    {
        $out  = [];
        $seen = [];

        $packages = array_merge(
            [$event->getComposer()->getPackage()],
            $event->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages(),
        );

        foreach ($packages as $package) {
            if (! $package instanceof PackageInterface) {
                continue;
            }

            $extra = $package->getExtra()['webkernel'] ?? null;

            if (! is_array($extra)) {
                continue;
            }

            $name = $package->getName();

            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $out[] = ['name' => $name, 'extra' => $extra];
        }

        return $out;
    }
}
