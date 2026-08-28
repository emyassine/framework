<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Config\Config;
use Webkernel\Route\Route;

/**
 * HTTP door. Autoload is already done by fast-boot.php.
 */
final class Http
{
    public static function run(): void
    {
        Config::boot();
        self::register_panel_routes();
        $out = Route::dispatch();
        if ($out instanceof \Stringable || \is_scalar($out) || $out === null) {
            echo (string) $out;

            return;
        }

        echo '';
    }

    private static function register_panel_routes(): void
    {
        $file = \vendor_dir('composer/webkernel_panel_routes.php');
        if (! \is_file($file)) {
            return;
        }
        $routes = require $file;
        if (! \is_array($routes)) {
            return;
        }
        foreach ($routes as $row) {
            if (! \is_array($row) || ! isset($row[0], $row[1], $row[2])) {
                continue;
            }
            $methods = $row[0];
            $uri = $row[1];
            $action = $row[2];
            if (! \is_array($methods) || ! \is_string($uri) || ! \is_string($action) || $action === '') {
                continue;
            }
            /** @var list<string> $methods */
            Route::match(\array_values(\array_map(\strval(...), $methods)), $uri, $action);
        }
    }
}
