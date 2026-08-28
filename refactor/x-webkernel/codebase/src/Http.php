<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Config\Config;
use Webkernel\Platform\Resources\Resource;
use Webkernel\Route\Route;

/**
 * HTTP door. Autoload is already done by fast-boot.php.
 */
final class Http
{
    public static function run(): void
    {
        Config::boot();
        self::register_panels();
        $out = Route::dispatch();
        if ($out instanceof \Stringable || \is_scalar($out) || $out === null) {
            echo (string) $out;

            return;
        }

        echo '';
    }

    private static function register_panels(): void
    {
        $file = vendor_dir('composer/webkernel_panels.php');
        if (! \is_file($file)) {
            return;
        }
        $panels = require $file;
        if (! \is_array($panels)) {
            return;
        }

        foreach ($panels as $panel) {
            if (! \is_array($panel)) {
                continue;
            }
            $base = \trim((string) ($panel['path'] ?? ''), '/');
            $prefix = $base === '' ? '' : '/'.$base;

            foreach ($panel['pages'] ?? [] as $page) {
                if (! \is_string($page) || $page === '') {
                    continue;
                }
                Route::get($prefix === '' ? '/' : $prefix, $page);
            }

            foreach ($panel['resources'] ?? [] as $resource) {
                if (! \is_string($resource) || $resource === '' || ! \class_exists($resource)) {
                    continue;
                }
                if (! \is_a($resource, Resource::class, true)) {
                    continue;
                }
                $slug = $resource::$slug ?? self::resource_slug($resource);
                foreach ($resource::pages() as $def) {
                    $path = '/';
                    $class = $def;
                    $methods = ['GET'];
                    if (\is_array($def)) {
                        $path = (string) ($def['path'] ?? '/');
                        $class = (string) ($def['class'] ?? '');
                        $methods = \is_array($def['methods'] ?? null) ? $def['methods'] : ['GET'];
                    }
                    if ($class === '') {
                        continue;
                    }
                    $uri = $prefix.'/'.$slug.$path;
                    $uri = '/'.\trim(\str_replace('//', '/', $uri), '/');
                    if ($uri === '') {
                        $uri = '/';
                    }
                    /** @var list<string> $methods */
                    Route::match(\array_map(\strval(...), $methods), $uri, $class);
                }
            }
        }
    }

    /**
     * @param class-string $resource
     */
    private static function resource_slug(string $resource): string
    {
        $short = (new \ReflectionClass($resource))->getShortName();
        if (\str_ends_with($short, 'Resource')) {
            $short = \substr($short, 0, -8);
        }
        $kebab = \preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $short);

        return \strtolower(\is_string($kebab) ? $kebab : $short);
    }
}
