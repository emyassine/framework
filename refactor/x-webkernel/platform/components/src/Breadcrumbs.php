<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Trail of page links. Blade: `<x-webkernel::breadcrumbs>`.
 */
final class Breadcrumbs extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::breadcrumbs';
    }

    /**
     * @param $breadcrumbs list<array{label: string, href?: string}>|array<string, string>
     *
     * @return static
     */
    public function items(array $breadcrumbs): static
    {
        $this->props['breadcrumbs'] = $breadcrumbs;

        return $this;
    }

}
