<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

use Webkernel\Platform\Panel\Concerns\HasPanelSidebar;
use Webkernel\Platform\Panel\Concerns\HasSidebar;
use Webkernel\Platform\Panel\Concerns\HasTopbar;

/**
 * Fluent panel declaration. Dump-autoload snapshots this; the request reads the dump.
 *
 * //> Scope is not declared here. Dump infers it from the Composer package.
 * //> Page layout lives in Concerns: sidebar() drawer, topbar() bar, panel_sidebar() icon rail.
 *
 * @mixin HasSidebar
 * @mixin HasTopbar
 * @mixin HasPanelSidebar
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
 * @method self topbar(bool $enabled = true)
 * @method bool has_topbar()
 * @method self panel_sidebar(bool $enabled = true)
 * @method bool has_panel_sidebar()
 * @method self panel_sidebar_width(string $width)
 * @method string get_panel_sidebar_width()
 */
final class Panel
{
    use HasSidebar;
    use HasTopbar;
    use HasPanelSidebar;

    private string $id = '';

    private string $path = '';

    private string $label = '';

    private string $icon = '';

    private string $scope = 'module';

    private bool $default = false;

    /** @var list<class-string> */
    private array $pages = [];

    /** @var list<class-string> */
    private array $widgets = [];

    /** @var list<class-string> */
    private array $resources = [];

    /** @var list<class-string> */
    private array $middleware = [];

    /** @var list<class-string> */
    private array $auth_middleware = [];

    private mixed $favicon = null;

    private mixed $brand_logo = null;

    private mixed $dark_mode_brand_logo = null;

    private mixed $brand_logo_height = null;

    private mixed $colors = null;

    private mixed $dark_mode = null;

    private string $home_url = '';

    /**
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * @param $id string
     * @return self
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param $path string
     * @return self
     */
    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param $label string
     * @return self
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Lucide icon basename for the app rail.
     *
     * @param $icon string
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Dump-autoload only. Providers do not call this.
     *
     * @param $scope 'platform'|'module'
     * @return self
     */
    public function scope(string $scope): self
    {
        if ($scope !== 'platform' && $scope !== 'module') {
            throw new \InvalidArgumentException('Panel scope must be platform|module.');
        }
        $this->scope = $scope;

        return $this;
    }

    /**
     * @param $default bool
     * @return self
     */
    public function default(bool $default = true): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param $pages list<class-string>
     * @return self
     */
    public function pages(array $pages): self
    {
        $this->pages = \array_values($pages);

        return $this;
    }

    /**
     * @param $widgets list<class-string>
     * @return self
     */
    public function widgets(array $widgets): self
    {
        $this->widgets = \array_values($widgets);

        return $this;
    }

    /**
     * @param $resources list<class-string>
     * @return self
     */
    public function resources(array $resources): self
    {
        $this->resources = \array_values($resources);

        return $this;
    }

    /**
     * @param $middleware list<class-string>
     * @return self
     */
    public function middleware(array $middleware): self
    {
        $this->middleware = \array_values($middleware);

        return $this;
    }

    /**
     * @param $middleware list<class-string>
     * @return self
     */
    public function auth_middleware(array $middleware): self
    {
        $this->auth_middleware = \array_values($middleware);

        return $this;
    }

    /**
     * @param $favicon mixed
     * @return self
     */
    public function favicon(mixed $favicon): self
    {
        $this->favicon = $favicon;

        return $this;
    }

    /**
     * @param $logo mixed
     * @return self
     */
    public function brand_logo(mixed $logo): self
    {
        $this->brand_logo = $logo;

        return $this;
    }

    /**
     * @param $logo mixed
     * @return self
     */
    public function dark_mode_brand_logo(mixed $logo): self
    {
        $this->dark_mode_brand_logo = $logo;

        return $this;
    }

    /**
     * @param $height mixed
     * @return self
     */
    public function brand_logo_height(mixed $height): self
    {
        $this->brand_logo_height = $height;

        return $this;
    }

    /**
     * @param $colors mixed
     * @return self
     */
    public function colors(mixed $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * @param $dark_mode mixed
     * @return self
     */
    public function dark_mode(mixed $dark_mode): self
    {
        $this->dark_mode = $dark_mode;

        return $this;
    }

    /**
     * Landing URL. Empty means dump-autoload uses the first page.
     *
     * @param $url string
     * @return self
     */
    public function home_url(string $url): self
    {
        $this->home_url = $url;

        return $this;
    }

    /**
     * @return array{
     *   id: string,
     *   path: string,
     *   href: string,
     *   home_url: string,
     *   label: string,
     *   icon: string,
     *   scope: string,
     *   default: bool,
     *   pages: list<class-string>,
     *   widgets: list<class-string>,
     *   resources: list<class-string>,
     *   middleware: list<class-string>,
     *   auth_middleware: list<class-string>,
     *   branding: array<string, mixed>,
     *   layout: array<string, mixed>
     * }
     */
    public function to_array(): array
    {
        if ($this->id === '') {
            throw new \RuntimeException('Panel must declare id().');
        }
        $path = $this->path !== '' ? $this->path : $this->id;
        $href = '/'.\trim($path, '/');

        return [
            'id' => $this->id,
            'path' => $path,
            'href' => $href === '/' ? '/'.$this->id : $href,
            'home_url' => $this->home_url,
            'label' => $this->label !== '' ? $this->label : \ucfirst($this->id),
            'icon' => $this->icon !== '' ? $this->icon : 'package',
            'scope' => $this->scope,
            'default' => $this->default,
            'pages' => $this->pages,
            'widgets' => $this->widgets,
            'resources' => $this->resources,
            'middleware' => $this->middleware,
            'auth_middleware' => $this->auth_middleware,
            'branding' => [
                'favicon' => $this->favicon,
                'logo_light' => $this->brand_logo,
                'logo_dark' => $this->dark_mode_brand_logo,
                'logo_height' => $this->brand_logo_height,
                'colors' => $this->colors,
                'dark_mode' => $this->dark_mode,
            ],
            'layout' => [
                ...$this->sidebar_layout(),
                ...$this->topbar_layout(),
                ...$this->panel_sidebar_layout(),
            ],
        ];
    }
}
