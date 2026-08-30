<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Placeholder when a list is empty. Blade: `<x-webkernel::empty-state>`.
 */
final class EmptyState extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::empty-state';
    }

    /**
     * @param $heading string
     *
     * @return static
     */
    public function heading(string $heading): static
    {
        $this->props['heading'] = $heading;

        return $this;
    }

    /**
     * @param $description string
     *
     * @return static
     */
    public function description(string $description): static
    {
        $this->props['description'] = $description;

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
     * @param $compact bool
     *
     * @return static
     */
    public function compact(bool $compact = true): static
    {
        $this->props['compact'] = $compact;

        return $this;
    }

    /**
     * @param $contained bool
     *
     * @return static
     */
    public function contained(bool $contained = true): static
    {
        $this->props['contained'] = $contained;

        return $this;
    }

}
