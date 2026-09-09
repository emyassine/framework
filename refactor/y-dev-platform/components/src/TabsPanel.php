<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasMethodMake;
use Webkernel\Platform\Components\Concerns\HasLayout;

/**
 * One tab panel. View: `<x-webkernel::tabs.panel>`.
 */
final class TabsPanel extends \Webkernel\Component\StaticComponent
{
    use HasMethodMake;
    use HasLayout;

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

    /**
     * @param $columns int|array<string, int>|null
     *
     * @return static
     */
    public function columns(int|array|null $columns): static
    {
        if ($columns !== null) {
            $this->props['columns'] = $columns;
        }

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $extra['grid_class'] = $this->grid_class();
        $extra['grid_style'] = $this->grid_style();

        return parent::render($extra);
    }
}
