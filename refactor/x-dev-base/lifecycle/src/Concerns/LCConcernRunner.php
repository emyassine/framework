<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Concerns;

use Composer\Script\Event;
use Webkernel\Lifecycle\Concerns\Contracts\LifecycleConcernContract;

/**
 * Resolves and runs every concern declared in extra.webkernel.lifecycle.concerns[]
 * across all installed packages, including the root package.
 *
 * Execution order:
 *   1. Root package concerns run first.
 *   2. webkernel/codebase concerns run second (if present).
 *   3. All other packages follow in alphabetical order.
 *
 * Each concern is instantiated fresh per call — no shared state.
 */
final class LCConcernRunner
{
    public function run(Event $event): void
    {
        $io = $event->getIO();
        $packages = $this->collect_packages($event);

        foreach ($packages as ['name' => $pkg_name, 'concerns' => $concerns]) {
            foreach ($concerns as $fqcn) {
                if (! \is_string($fqcn) || $fqcn === '') {
                    continue;
                }

                if (! \class_exists($fqcn)) {
                    $io->writeError(sprintf(
                        '  <warning>[webkernel/lifecycle] Concern not found: %s (package: %s)</warning>',
                        $fqcn,
                        $pkg_name,
                    ));
                    continue;
                }

                $concern = new $fqcn();

                if (! $concern instanceof LifecycleConcernContract) {
                    $io->writeError(sprintf(
                        '  <warning>[webkernel/lifecycle] %s does not implement LifecycleConcernContract — skipped.</warning>',
                        $fqcn,
                    ));
                    continue;
                }

                $io->write(sprintf('  <info>→ [%s] %s</info>', $pkg_name, $concern->name()));

                try {
                    $concern->handle($event);
                } catch (\Throwable $e) {
                    $io->writeError(sprintf(
                        '  <error>[webkernel/lifecycle] Concern "%s" failed: %s</error>',
                        $concern->name(),
                        $e->getMessage(),
                    ));
                }
            }
        }
    }

    /**
     * Collect all packages (root + installed) that declare lifecycle concerns.
     * Returns packages sorted: root first, then webkernel/codebase, then alphabetical.
     *
     * @return list<array{name: string, concerns: list<string>}>
     */
    private function collect_packages(Event $event): array
    {
        $composer   = $event->getComposer();
        $root       = $composer->getPackage();
        $installed  = $composer->getRepositoryManager()->getLocalRepository()->getPackages();

        $all = array_merge([$root], $installed);
        $out = [];
        $seen = [];

        foreach ($all as $package) {
            $name     = $package->getName();
            $concerns = $package->getExtra()['webkernel']['lifecycle']['concerns'] ?? [];

            if (! \is_array($concerns) || $concerns === []) {
                continue;
            }
            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $out[] = ['name' => $name, 'concerns' => array_values($concerns)];
        }

        \usort($out, static function (array $a, array $b): int {
            // Root package (empty name from RootPackage) always first.
            $a_root = $a['name'] === '' ? -1 : ($a['name'] === 'webkernel/codebase' ? 0 : 1);
            $b_root = $b['name'] === '' ? -1 : ($b['name'] === 'webkernel/codebase' ? 0 : 1);

            return [$a_root, $a['name']] <=> [$b_root, $b['name']];
        });

        return $out;
    }
}
