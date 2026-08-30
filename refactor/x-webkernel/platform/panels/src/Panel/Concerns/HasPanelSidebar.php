<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Panel\Concerns;

/**
 * Icon rail that switches between registered panels.
 *
 * @method self panel_sidebar(bool $enabled = true)
 * @method bool has_panel_sidebar()
 * @method self panel_sidebar_width(string $width)
 * @method string get_panel_sidebar_width()
 */
trait HasPanelSidebar
{
    private bool $has_panel_sidebar = true;

    private string $panel_sidebar_width = '4rem';

    /**
     * Show or hide the panel icon rail.
     *
     * @param $enabled bool
     * @return self
     */
    public function panel_sidebar(bool $enabled = true): self
    {
        $this->has_panel_sidebar = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function has_panel_sidebar(): bool
    {
        return $this->has_panel_sidebar;
    }

    /**
     * @param $width string
     * @return self
     */
    public function panel_sidebar_width(string $width): self
    {
        $this->panel_sidebar_width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function get_panel_sidebar_width(): string
    {
        return $this->panel_sidebar_width;
    }

    /**
     * @return array{panel_sidebar: bool, panel_sidebar_width: string}
     */
    private function panel_sidebar_layout(): array
    {
        return [
            'panel_sidebar' => $this->has_panel_sidebar,
            'panel_sidebar_width' => $this->panel_sidebar_width,
        ];
    }
}
