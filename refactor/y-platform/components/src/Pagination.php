<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasMethodMake;

/**
 * Page controls. View: `<x-webkernel::pagination>`.
 */
final class Pagination extends \Webkernel\Component\StaticComponent
{
    use HasMethodMake;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::pagination';
    }

    /**
     * @param $simple bool
     *
     * @return static
     */
    public function simple(bool $simple = true): static
    {
        $this->props['simple'] = $simple;

        return $this;
    }

    /**
     * @param $current int
     *
     * @return static
     */
    public function current(int $current): static
    {
        $this->props['current'] = $current;

        return $this;
    }

    /**
     * @param $last int
     *
     * @return static
     */
    public function last(int $last): static
    {
        $this->props['last'] = $last;

        return $this;
    }

    /**
     * @param $total int
     *
     * @return static
     */
    public function total(int $total): static
    {
        $this->props['total'] = $total;

        return $this;
    }

}
