<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview\Concerns;

/**
 * Trait for component lifecycle hooks.
 */
trait HasLifecycleHooks
{
    /**
     * Called before the component renders.
     *
     * @return void
     */
    protected function before_render(): void
    {
        // Override in child classes
    }

    /**
     * Called after the component renders.
     *
     * @param string $html
     * @return void
     */
    protected function after_render(string $html): void
    {
        // Override in child classes
    }

    /**
     * Called before an action is executed.
     *
     * @param string $action
     * @param array<string, mixed> $params
     * @return void
     */
    protected function before_action(string $action, array $params): void
    {
        // Override in child classes
    }

    /**
     * Called after an action is executed.
     *
     * @param string $action
     * @param array<string, mixed> $params
     * @return void
     */
    protected function after_action(string $action, array $params): void
    {
        // Override in child classes
    }

    /**
     * Called when the component is hydrated.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function on_hydrate(array $data): void
    {
        // Override in child classes
    }

    /**
     * Called when the component is dehydrated.
     *
     * @return void
     */
    protected function on_dehydrate(): void
    {
        // Override in child classes
    }
}
