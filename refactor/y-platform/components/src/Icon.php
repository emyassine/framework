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
 * SVG mark. View: `<x-webkernel::icon>`.
 */
final class Icon extends \Webkernel\Component\StaticComponent
{
    use HasMethodMake;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::icon';
    }

    /**
     * @param $set string
     *
     * @return static
     */
    public function set(string $set): static
    {
        $this->props['set'] = $set;

        return $this;
    }
}
