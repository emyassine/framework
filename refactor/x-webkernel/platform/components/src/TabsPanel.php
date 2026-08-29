<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * One tab panel. Blade: `<x-webkernel::tabs.panel>`.
 */
final class TabsPanel extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs.panel';
    }

    /**
     * @param $tab string
     *
     * @return static
     */
    public function tab(string $tab): static
    {
        $this->props['tab'] = $tab;

        return $this;
    }

    /**
     * @param $active bool
     *
     * @return static
     */
    public function active(bool $active = true): static
    {
        $this->props['active'] = $active;

        return $this;
    }
}
