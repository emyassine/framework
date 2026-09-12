<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Actions;

use Composer\IO\IOInterface;
use Composer\Script\Event;

/**
 * Evaluates environment checks declared in extra.webkernel.lifecycle.checks[].
 *
 * Each check is a PHP boolean expression (string). Truthy = failed.
 *
 * Severity:
 *   info    → blue notice, continues.
 *   warning → yellow warning, continues.
 *   danger  → red error, throws RuntimeException, blocks Composer.
 */
final class LCEnvChecker
{
    public function run(Event $event): void
    {
        $io       = $event->getIO();
        $packages = $this->collect_packages($event);

        foreach ($packages as ['name' => $pkg_name, 'checks' => $checks]) {
            foreach ($checks as $spec) {
                $this->evaluate($spec, $pkg_name, $io);
            }
        }
    }

    /** @throws \RuntimeException on danger-level failure */
    private function evaluate(mixed $spec, string $pkg_name, IOInterface $io): void
    {
        if (! is_array($spec)) {
            return;
        }

        $expression = $spec['check']   ?? null;
        $on_fail    = $spec['on-fail'] ?? [];
        $fix        = $spec['fix']     ?? null;

        if (! is_string($expression) || $expression === '') {
            return;
        }

        try {
            // phpcs:ignore Squiz.PHP.Eval.Discouraged
            $failed = (bool) eval("return ({$expression});");
        } catch (\ParseError) {
            $io->writeError(sprintf(
                '  <warning>[webkernel/lifecycle] Invalid check expression in "%s": %s</warning>',
                $pkg_name,
                $expression,
            ));
            return;
        }

        if (! $failed) {
            return;
        }

        $type    = $on_fail['type']    ?? 'warning';
        $message = $on_fail['message'] ?? 'An environment check failed.';

        [$open, $close] = match ($type) {
            'danger'  => ['<error>',   '</error>'],
            'warning' => ['<warning>', '</warning>'],
            default   => ['<info>',    '</info>'],
        };

        $io->writeError("  {$open}[webkernel/{$pkg_name}] {$message}{$close}");

        if (is_string($fix) && $fix !== '') {
            $io->writeError("    Fix: <comment>{$fix}</comment>");
        }

        if ($type === 'danger') {
            throw new \RuntimeException(
                "[webkernel/lifecycle] Blocking check failed in \"{$pkg_name}\": {$message}",
            );
        }
    }

    /**
     * @return list<array{name: string, checks: list<mixed>}>
     */
    private function collect_packages(Event $event): array
    {
        $composer  = $event->getComposer();
        $installed = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $out       = [];
        $seen      = [];

        foreach (array_merge([$composer->getPackage()], $installed) as $package) {
            $name   = $package->getName();
            $checks = $package->getExtra()['webkernel']['lifecycle']['checks'] ?? [];

            if (! is_array($checks) || $checks === []) {
                continue;
            }
            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $out[] = ['name' => $name, 'checks' => array_values($checks)];
        }

        return $out;
    }
}
