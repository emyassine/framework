<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Tab list plus panels. Same view for the tag and `Tabs::make()`.
 */
final class Tabs extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs';
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

    /**
     * @param $vertical bool
     *
     * @return static
     */
    public function vertical(bool $vertical = true): static
    {
        $this->props['vertical'] = $vertical;

        return $this;
    }

    /**
     * @param $html string
     *
     * @return static
     */
    public function list(string $html): static
    {
        $this->props['list'] = $html;

        return $this;
    }
}
