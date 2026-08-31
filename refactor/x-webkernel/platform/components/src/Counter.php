<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Component\ReactiveComponent;

/**
 * Counter component - reactive Liveview example.
 */
class Counter extends ReactiveComponent
{
    public int $count = 0;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::counter';
    }

    /**
     * Increment the counter.
     *
     * @return void
     */
    public function increment(): void
    {
        $this->count++;
    }

    /**
     * Decrement the counter.
     *
     * @return void
     */
    public function decrement(): void
    {
        $this->count--;
    }
}
