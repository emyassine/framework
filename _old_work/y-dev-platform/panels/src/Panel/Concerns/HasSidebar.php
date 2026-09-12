<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Panel\Concerns;

/**
 * Collapsible submenu drawer of the active panel.
 *
 * @method self sidebar(bool $enabled = true)
 * @method bool has_sidebar()
 * @method self sidebar_width(string $width)
 * @method string get_sidebar_width()
 * @method self collapsed_sidebar_width(string $width)
 * @method string get_collapsed_sidebar_width()
 * @method self sidebar_collapsible_on_desktop(bool $condition = true)
 * @method bool is_sidebar_collapsible_on_desktop()
 * @method self collapsible_navigation_groups(bool $condition = true)
 * @method bool has_collapsible_navigation_groups()
 */
trait HasSidebar
{
    private bool $has_sidebar = true;

    private string $sidebar_width = '20rem';

    private string $collapsed_sidebar_width = '0px';

    private bool $sidebar_collapsible_on_desktop = true;

    private bool $collapsible_navigation_groups = true;

    /**
     * Show or hide the submenu drawer.
     *
     * @param $enabled bool
     * @return self
     */
    public function sidebar(bool $enabled = true): self
    {
        $this->has_sidebar = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function has_sidebar(): bool
    {
        return $this->has_sidebar;
    }

    /**
     * @param $width string
     * @return self
     */
    public function sidebar_width(string $width): self
    {
        $this->sidebar_width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function get_sidebar_width(): string
    {
        return $this->sidebar_width;
    }

    /**
     * @param $width string
     * @return self
     */
    public function collapsed_sidebar_width(string $width): self
    {
        $this->collapsed_sidebar_width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function get_collapsed_sidebar_width(): string
    {
        return $this->collapsed_sidebar_width;
    }

    /**
     * @param $condition bool
     * @return self
     */
    public function sidebar_collapsible_on_desktop(bool $condition = true): self
    {
        $this->sidebar_collapsible_on_desktop = $condition;

        return $this;
    }

    /**
     * @return bool
     */
    public function is_sidebar_collapsible_on_desktop(): bool
    {
        return $this->sidebar_collapsible_on_desktop;
    }

    /**
     * @param $condition bool
     * @return self
     */
    public function collapsible_navigation_groups(bool $condition = true): self
    {
        $this->collapsible_navigation_groups = $condition;

        return $this;
    }

    /**
     * @return bool
     */
    public function has_collapsible_navigation_groups(): bool
    {
        return $this->collapsible_navigation_groups;
    }

    /**
     * @return array{
     *   sidebar: bool,
     *   sidebar_width: string,
     *   collapsed_sidebar_width: string,
     *   sidebar_collapsible_on_desktop: bool,
     *   collapsible_navigation_groups: bool
     * }
     */
    private function sidebar_layout(): array
    {
        return [
            'sidebar' => $this->has_sidebar,
            'sidebar_width' => $this->sidebar_width,
            'collapsed_sidebar_width' => $this->collapsed_sidebar_width,
            'sidebar_collapsible_on_desktop' => $this->sidebar_collapsible_on_desktop,
            'collapsible_navigation_groups' => $this->collapsible_navigation_groups,
        ];
    }
}
