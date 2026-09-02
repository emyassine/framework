<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Concerns;

use Composer\IO\IOInterface;
use Composer\Script\Event;

/**
 * Evaluates environment checks declared in extra.webkernel.lifecycle.checks[].
 *
 * Each check is a PHP expression (string). If it evaluates to truthy, the check
 * has failed. The on-fail.type determines the severity:
 *   - info    → blue notice, execution continues
 *   - warning → yellow warning, execution continues
 *   - danger  → red error, throws RuntimeException (blocks Composer)
 *
 * Example declaration in composer.json:
 *   "checks": [
 *     {
 *       "check": "PHP_VERSION_ID < 80500",
 *       "on-fail": { "type": "danger", "message": "PHP >= 8.5 required." },
 *       "fix": "sudo apt install php8.5"
 *     }
 *   ]
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

    /**
     * @throws \RuntimeException on danger-level failures
     */
    private function evaluate(mixed $spec, string $pkg_name, IOInterface $io): void
    {
        if (! \is_array($spec)) {
            return;
        }

        $expression = $spec['check']   ?? null;
        $on_fail    = $spec['on-fail'] ?? [];
        $fix        = $spec['fix']     ?? null;

        if (! \is_string($expression) || $expression === '') {
            return;
        }

        // Evaluate the PHP expression. Truthy = problem detected.
        $failed = false;
        try {
            // phpcs:ignore Squiz.PHP.Eval.Discouraged
            $result = eval("return (bool)({$expression});");
            $failed = (bool) $result;
        } catch (\ParseError $e) {
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

        $prefix = match ($type) {
            'danger'  => '<error>',
            'warning' => '<warning>',
            default   => '<info>',
        };
        $suffix = match ($type) {
            'danger'  => '</error>',
            'warning' => '</warning>',
            default   => '</info>',
        };

        $io->writeError(sprintf(
            "  {$prefix}[webkernel/%s] %s{$suffix}",
            $pkg_name,
            $message,
        ));

        if (\is_string($fix) && $fix !== '') {
            $io->writeError(sprintf('    Fix: <comment>%s</comment>', $fix));
        }

        if ($type === 'danger') {
            throw new \RuntimeException(sprintf(
                '[webkernel/lifecycle] Blocking environment check failed in "%s": %s',
                $pkg_name,
                $message,
            ));
        }
    }

    /**
     * @return list<array{name: string, checks: list<mixed>}>
     */
    private function collect_packages(Event $event): array
    {
        $composer  = $event->getComposer();
        $root      = $composer->getPackage();
        $installed = $composer->getRepositoryManager()->getLocalRepository()->getPackages();

        $out  = [];
        $seen = [];

        foreach (array_merge([$root], $installed) as $package) {
            $name   = $package->getName();
            $checks = $package->getExtra()['webkernel']['lifecycle']['checks'] ?? [];

            if (! \is_array($checks) || $checks === []) {
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
