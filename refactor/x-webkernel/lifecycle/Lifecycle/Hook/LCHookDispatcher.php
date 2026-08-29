<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Lifecycle\Hook;

use Webkernel\Console\DumpHook;

/**
 * Runs extra.webkernel.{event} callables declared on installed packages.
 *
 * //> webkernel/codebase runs first so dump-autoload exists for later hooks.
 * //> DumpHook classes are injected by DumpAutoloadCommand, not here.
 */
final class LCHookDispatcher
{
    /**
     * @param $event_name string
     * @param $event object
     * @param $packages list<array{name: string, extra: array<string, mixed>}>
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function dispatch(string $event_name, object $event, array $packages): void
    {
        $hook = LCHook::tryFrom($event_name);
        if ($hook === null) {
            return;
        }
        $key = $hook->extra_key();
        $jobs = [];
        foreach ($packages as $package) {
            $target = $package['extra'][$key] ?? null;
            if (! \is_string($target) || $target === '') {
                continue;
            }
            $jobs[] = [
                'name' => (string) $package['name'],
                'target' => $target,
            ];
        }
        \usort($jobs, static function (array $a, array $b): int {
            $a_core = $a['name'] === 'webkernel/codebase' ? 0 : 1;
            $b_core = $b['name'] === 'webkernel/codebase' ? 0 : 1;

            return [$a_core, $a['name']] <=> [$b_core, $b['name']];
        });
        foreach ($jobs as $job) {
            $this->invoke($job['target'], $event);
        }
    }

    /**
     * @param $target string
     * @param $event object
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    private function invoke(string $target, object $event): void
    {
        $class = $target;
        $method = '__invoke';
        if (\str_contains($target, '::')) {
            [$class, $method] = \explode('::', $target, 2);
        }
        if (! \class_exists($class)) {
            return;
        }
        if (\is_a($class, DumpHook::class, true)) {
            return;
        }
        if (! \method_exists($class, $method)) {
            throw new \RuntimeException('Webkernel lifecycle hook method not found: '.$target);
        }
        $ref = new \ReflectionMethod($class, $method);
        $args = $ref->getNumberOfParameters() > 0 ? [$event] : [];
        if ($ref->isStatic()) {
            $ref->invokeArgs(null, $args);

            return;
        }
        $ref->invokeArgs(new $class(), $args);
    }
}
