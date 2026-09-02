<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Hook;

/**
 * Dispatches extra.webkernel.lifecycle.events.{event} callables
 * declared on installed packages.
 *
 * Execution order:
 *   1. webkernel/codebase (if present) — core platform runs first.
 *   2. All other packages in alphabetical order.
 *
 * Callable formats supported:
 *   - "Vendor\\Class"          → new Vendor\Class() then __invoke($event)
 *   - "Vendor\\Class::method"  → static or instance call
 */
final class LCHookDispatcher
{
    /**
     * @param string                                                    $event_name  Composer event name
     * @param object                                                    $event       Composer Event object
     * @param list<array{name: string, extra: array<string, mixed>}>   $packages    Packages to scan
     *
     * @throws \RuntimeException if a declared callable method does not exist
     */
    public function dispatch(string $event_name, object $event, array $packages): void
    {
        $hook = LCHook::tryFrom($event_name);
        if ($hook === null) {
            return;
        }

        $key  = $hook->extra_key();
        $jobs = [];

        foreach ($packages as $package) {
            // Read from the new structure: extra.webkernel.lifecycle.events.{key}
            $target = $package['extra']['lifecycle']['events'][$key] ?? null;

            // Backwards-compat: also read flat extra.webkernel.{key} (legacy)
            if (! \is_string($target) || $target === '') {
                $target = $package['extra'][$key] ?? null;
            }

            if (! \is_string($target) || $target === '') {
                continue;
            }

            $jobs[] = [
                'name'   => (string) $package['name'],
                'target' => $target,
            ];
        }

        // webkernel/codebase always runs first among peers.
        \usort($jobs, static function (array $a, array $b): int {
            $priority = static fn (string $n): int => match ($n) {
                'webkernel/codebase' => 0,
                default              => 1,
            };
            return [$priority($a['name']), $a['name']] <=> [$priority($b['name']), $b['name']];
        });

        foreach ($jobs as $job) {
            $this->invoke($job['target'], $event);
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function invoke(string $target, object $event): void
    {
        $class  = $target;
        $method = '__invoke';

        if (\str_contains($target, '::')) {
            [$class, $method] = \explode('::', $target, 2);
        }

        if (! \class_exists($class)) {
            return;
        }

        if (! \method_exists($class, $method)) {
            throw new \RuntimeException(
                '[webkernel/lifecycle] Hook method not found: ' . $target,
            );
        }

        $ref  = new \ReflectionMethod($class, $method);
        $args = $ref->getNumberOfParameters() > 0 ? [$event] : [];

        if ($ref->isStatic()) {
            $ref->invokeArgs(null, $args);
            return;
        }

        $ref->invokeArgs(new $class(), $args);
    }
}
