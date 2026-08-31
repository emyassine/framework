<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

use Webkernel\Composables\ComposableContract;
use Webkernel\Config\Config;

/**
 * Fluent host for dumped composables. Not a boot/HTTP/CLI god object.
 *
 * @method \Webkernel\Composables\ConfigComposable config()
 * @method \Webkernel\View\View view()
 * @method \Webkernel\Route\Route route()
 * @method \Webkernel\Composables\PanelComposable panel()
 * @method \Webkernel\Console\Terminal terminal()
 * @method \Webkernel\Console\Dispatcher console()
 * @method \Webkernel\Database\Database database()
 * @method \Webkernel\Auth\Auth auth()
 */
final class WebApp
{
    private static ?self $instance = null;

    /** @var array<string, class-string<ComposableContract>> */
    private array $composables = [];

    /** @var array<string, object> */
    private array $resolved = [];

    private bool $map_loaded = false;

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public static function flush(): void
    {
        self::$instance = null;
    }

    /**
     * @return ($key is null ? \Webkernel\Composables\ConfigComposable : mixed)
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $this->segment('config');
        if ($key === null) {
            return $config;
        }

        return Config::get($key, $default);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $segment = $this->segment($name);
        if ($arguments === []) {
            return $segment;
        }
        if (\is_callable($segment)) {
            return $segment(...$arguments);
        }

        throw new \BadMethodCallException('webapp()->'.$name.'() does not take arguments.');
    }

    private function segment(string $name): object
    {
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }
        $this->load_map();
        $class = $this->composables[$name] ?? null;
        if (! \is_string($class) || $class === '') {
            throw new \BadMethodCallException('Unknown composable ['.$name.'].');
        }

        return $this->resolved[$name] = new $class();
    }

    private function load_map(): void
    {
        if ($this->map_loaded) {
            return;
        }
        $this->map_loaded = true;
        $file = vendor_dir('composer/webkernel_composables.php');
        if (! \is_file($file)) {
            return;
        }
        $map = require $file;
        if (! \is_array($map)) {
            return;
        }
        foreach ($map as $name => $class) {
            if (\is_string($name) && \is_string($class) && $class !== '') {
                $this->composables[$name] = $class;
            }
        }
    }
}
