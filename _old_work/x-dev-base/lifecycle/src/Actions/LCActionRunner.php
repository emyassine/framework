<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Actions;

use Composer\Script\Event;
use Webkernel\Lifecycle\Actions\Contracts\LifecycleActionContract;

/**
 * Resolves and runs every action declared in extra.webkernel.lifecycle.actions[]
 * across all installed packages (root + installed).
 *
 * Execution order:
 *   1. Root package actions first.
 *   2. webkernel/codebase actions second (platform core).
 *   3. All other packages in alphabetical order.
 */
final class LCActionRunner
{
    public function run(Event $event): void
    {
        $io       = $event->getIO();
        $packages = $this->collect_packages($event);

        foreach ($packages as ['name' => $pkg_name, 'actions' => $action_fqcns]) {
            foreach ($action_fqcns as $fqcn) {
                if (! is_string($fqcn) || $fqcn === '') {
                    continue;
                }

                if (! class_exists($fqcn)) {
                    $io->writeError(sprintf(
                        '  <warning>[webkernel/lifecycle] Action not found: %s (package: %s)</warning>',
                        $fqcn,
                        $pkg_name,
                    ));
                    continue;
                }

                $action = new $fqcn();

                if (! $action instanceof LifecycleActionContract) {
                    $io->writeError(sprintf(
                        '  <warning>[webkernel/lifecycle] %s does not implement LifecycleActionContract — skipped.</warning>',
                        $fqcn,
                    ));
                    continue;
                }

                $io->write(sprintf('  <info>→ [%s] %s</info>', $pkg_name, $action->name()));

                try {
                    $action->handle($event);
                } catch (\Throwable $e) {
                    $io->writeError(sprintf(
                        '  <error>[webkernel/lifecycle] Action "%s" failed: %s</error>',
                        $action->name(),
                        $e->getMessage(),
                    ));
                }
            }
        }
    }

    /**
     * @return list<array{name: string, actions: list<string>}>
     */
    private function collect_packages(Event $event): array
    {
        $composer  = $event->getComposer();
        $installed = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $out       = [];
        $seen      = [];

        foreach (array_merge([$composer->getPackage()], $installed) as $package) {
            $name    = $package->getName();
            $actions = $package->getExtra()['webkernel']['lifecycle']['actions'] ?? [];

            if (! is_array($actions) || $actions === []) {
                continue;
            }
            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $out[] = ['name' => $name, 'actions' => array_values($actions)];
        }

        usort($out, static function (array $a, array $b): int {
            $priority = static fn (string $n): int => match ($n) {
                ''                   => -1,
                'webkernel/codebase' => 0,
                default              => 1,
            };
            return [$priority($a['name']), $a['name']] <=> [$priority($b['name']), $b['name']];
        });

        return $out;
    }
}
