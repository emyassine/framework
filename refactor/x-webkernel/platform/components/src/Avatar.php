<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

/**
 * Image or initial. ViewView: `<x-webkernel::avatar>`.
 */
final class Avatar extends \Webkernel\Component\StaticComponent
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::avatar';
    }

    /**
     * @param $src string
     *
     * @return static
     */
    public function src(string $src): static
    {
        $this->props['src'] = $src;

        return $this;
    }

    /**
     * @param $alt string
     *
     * @return static
     */
    public function alt(string $alt): static
    {
        $this->props['alt'] = $alt;

        return $this;
    }

    /**
     * @param $circular bool
     *
     * @return static
     */
    public function circular(bool $circular = true): static
    {
        $this->props['circular'] = $circular;

        return $this;
    }

    /**
     * @param $size Size|string
     *
     * @return static
     */
    public function size(Size|string $size): static
    {
        $this->props['size'] = $size instanceof Size ? $size->value : $size;

        return $this;
    }

}
