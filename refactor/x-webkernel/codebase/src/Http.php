<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Config\Config;
use Webkernel\Route\Compile\Cache;
use Webkernel\Route\Route;

/**
 * HTTP door. Autoload is already done by fast-boot.php.
 */
final class Http
{
    public static function run(): void
    {
        Config::boot();
        self::maybe_compress();
        if (! Cache::is_fresh()) {
            Route::register_dumped_panel_routes();
        }
        $out = Route::dispatch();
        if ($out instanceof \Stringable || \is_scalar($out) || $out === null) {
            echo (string) $out;

            return;
        }

        echo '';
    }

    /**
     * gzip/deflate is what browsers and phones already decode. Blaze-style
     * folding happens at compile time (routes + static icons); this is the
     * wire format for the HTML that comes out.
     */
    private static function maybe_compress(): void
    {
        if (\headers_sent() || \ini_get('zlib.output_compression')) {
            return;
        }
        $accept = (string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '');
        if ($accept === '' || ! \str_contains($accept, 'gzip') || ! \function_exists('ob_gzhandler')) {
            return;
        }
        \ob_start('ob_gzhandler');
    }
}
