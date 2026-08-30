<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * One tab trigger. View: `<x-webkernel::tabs.item>`.
 */
final class TabsItem extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs.item';
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

    /**
     * @param $icon string
     *
     * @return static
     */
    public function icon(string $icon): static
    {
        $this->props['icon'] = $icon;

        return $this;
    }

    /**
     * @param $position IconPosition|string
     *
     * @return static
     */
    public function icon_position(IconPosition|string $position): static
    {
        $this->props['icon_position'] = $position instanceof IconPosition
            ? $position->value
            : $position;

        return $this;
    }

    /**
     * @param $badge string
     *
     * @return static
     */
    public function badge(string $badge): static
    {
        $this->props['badge'] = $badge;

        return $this;
    }

    /**
     * @param $color string
     *
     * @return static
     */
    public function badge_color(string $color): static
    {
        $this->props['badge_color'] = $color;

        return $this;
    }

    /**
     * @param $deferred bool
     *
     * @return static
     */
    public function defer_badge(bool $deferred = true): static
    {
        $this->props['defer_badge'] = $deferred;

        return $this;
    }
}
