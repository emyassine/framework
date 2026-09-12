<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Hook;

/**
 * Dispatches extra.webkernel.lifecycle.events.{event} callables
 * declared across installed packages.
 *
 * Callable formats:
 *   "Vendor\\ClassName"          → new ClassName(); __invoke($event)
 *   "Vendor\\ClassName::method"  → static or instance call
 *
 * Order: webkernel/codebase first, then alphabetical.
 */
final class LCHookDispatcher
{
    /**
     * @param list<array{name: string, extra: array<string, mixed>}> $packages
     * @throws \RuntimeException
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
            $target = $package['extra']['lifecycle']['events'][$key] ?? null;

            if (! is_string($target) || $target === '') {
                continue;
            }

            $jobs[] = ['name' => (string) $package['name'], 'target' => $target];
        }

        usort($jobs, static function (array $a, array $b): int {
            $p = static fn (string $n): int => $n === 'webkernel/codebase' ? 0 : 1;
            return [$p($a['name']), $a['name']] <=> [$p($b['name']), $b['name']];
        });

        foreach ($jobs as $job) {
            $this->invoke($job['target'], $event);
        }
    }

    /** @throws \RuntimeException */
    private function invoke(string $target, object $event): void
    {
        $class  = $target;
        $method = '__invoke';

        if (str_contains($target, '::')) {
            [$class, $method] = explode('::', $target, 2);
        }

        if (! class_exists($class)) {
            return;
        }

        if (! method_exists($class, $method)) {
            throw new \RuntimeException('[webkernel/lifecycle] Hook method not found: ' . $target);
        }

        $ref  = new \ReflectionMethod($class, $method);
        $args = $ref->getNumberOfParameters() > 0 ? [$event] : [];

        $ref->isStatic()
            ? $ref->invokeArgs(null, $args)
            : $ref->invokeArgs(new $class(), $args);
    }
}
