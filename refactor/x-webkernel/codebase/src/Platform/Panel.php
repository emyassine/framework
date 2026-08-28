<?php declare(strict_types=1);

namespace Webkernel\Platform;

/**
 * Fluent panel declaration. Dump-autoload snapshots this; the request reads the dump.
 */
final class Panel
{
    private string $id = '';

    private string $path = '';

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

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param 'platform'|'module' $scope
     */
    public function scope(string $scope): self
    {
        if ($scope !== 'platform' && $scope !== 'module') {
            throw new \InvalidArgumentException('Panel scope must be platform|module.');
        }
        $this->scope = $scope;

        return $this;
    }

    public function default(bool $default = true): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param list<class-string> $pages
     */
    public function pages(array $pages): self
    {
        $this->pages = array_values($pages);

        return $this;
    }

    /**
     * @param list<class-string> $widgets
     */
    public function widgets(array $widgets): self
    {
        $this->widgets = array_values($widgets);

        return $this;
    }

    /**
     * @param list<class-string> $resources
     */
    public function resources(array $resources): self
    {
        $this->resources = array_values($resources);

        return $this;
    }

    /**
     * @param list<class-string> $middleware
     */
    public function middleware(array $middleware): self
    {
        $this->middleware = array_values($middleware);

        return $this;
    }

    /**
     * @param list<class-string> $middleware
     */
    public function auth_middleware(array $middleware): self
    {
        $this->auth_middleware = array_values($middleware);

        return $this;
    }

    public function favicon(mixed $favicon): self
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function brand_logo(mixed $logo): self
    {
        $this->brand_logo = $logo;

        return $this;
    }

    public function dark_mode_brand_logo(mixed $logo): self
    {
        $this->dark_mode_brand_logo = $logo;

        return $this;
    }

    public function brand_logo_height(mixed $height): self
    {
        $this->brand_logo_height = $height;

        return $this;
    }

    public function colors(mixed $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function dark_mode(mixed $dark_mode): self
    {
        $this->dark_mode = $dark_mode;

        return $this;
    }

    /**
     * @return array{
     *   id: string,
     *   path: string,
     *   scope: string,
     *   default: bool,
     *   pages: list<class-string>,
     *   widgets: list<class-string>,
     *   resources: list<class-string>,
     *   middleware: list<class-string>,
     *   auth_middleware: list<class-string>,
     *   branding: array<string, mixed>
     * }
     */
    public function to_array(): array
    {
        if ($this->id === '') {
            throw new \RuntimeException('Panel must declare id().');
        }
        if ($this->scope !== 'platform' && $this->scope !== 'module') {
            throw new \RuntimeException('Panel ['.$this->id.'] must declare scope platform|module.');
        }

        return [
            'id' => $this->id,
            'path' => $this->path !== '' ? $this->path : $this->id,
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
        ];
    }
}
