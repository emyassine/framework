<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Http;
use Webkernel\Route\Compile\Cache;
use Webkernel\Route\Route;
use Webkernel\View\View;

final class RouteCacheTest extends TestCase
{
    protected function setUp(): void
    {
        Config::flush();
        View::flush();
        Route::flush();
        Config::boot();
    }

    public function test_compiled_routes_exist_after_dump(): void
    {
        $this->assertFileExists(Cache::path());
        $this->assertTrue(Cache::is_fresh());
        $this->assertIsArray(Cache::fresh_data());
    }

    public function test_system_dispatch_from_fresh_cache(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/system';
        $_SERVER['HTTP_HOST'] = 'localhost';

        ob_start();
        Http::run();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertTrue(Cache::is_fresh());
    }

    public function test_warm_process_does_not_load_generator(): void
    {
        $root = \webapp_path();
        $script = $root.'/platform/temporary/route-cache-probe.php';
        if (! \is_dir(\dirname($script)) && ! \mkdir(\dirname($script), 0775, true) && ! \is_dir(\dirname($script))) {
            $this->fail('unable to create temporary dir');
        }
        \file_put_contents($script, <<<'PHP'
<?php declare(strict_types=1);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/system';
$_SERVER['HTTP_HOST'] = 'localhost';
require dirname(__DIR__).'/fast-boot.php';
ob_start();
Webkernel\Http::run();
$html = (string) ob_get_clean();
$loaded = class_exists(Webkernel\Route\Compile\Generator::class, false)
    || class_exists(Webkernel\Route\Compile\Pattern::class, false)
    || class_exists(Webkernel\Route\Compile\Compiled::class, false);
echo ($loaded ? 'GENERATOR' : 'NO_GENERATOR')."\n";
echo (str_contains($html, 'System Admin Panel') ? 'HIT' : 'MISS')."\n";
PHP);
        $cmd = \escapeshellarg(\PHP_BINARY).' '.\escapeshellarg($script);
        $out = [];
        $code = 0;
        \exec($cmd, $out, $code);
        @\unlink($script);
        $this->assertSame(0, $code, \implode("\n", $out));
        $this->assertSame(['NO_GENERATOR', 'HIT'], $out);
    }

    public function test_route_file_change_invalidates_cache(): void
    {
        $this->assertTrue(Cache::is_fresh());
        $file = Cache::source_files()[0] ?? '';
        $this->assertNotSame('', $file);
        $mtime = \filemtime($file);
        $this->assertNotFalse($mtime);
        \touch($file, $mtime + 2);
        \clearstatcache(true, $file);
        Cache::reset();
        $this->assertFalse(Cache::is_fresh());
        \touch($file, $mtime);
        \clearstatcache(true, $file);
        Cache::reset();
        $this->assertTrue(Cache::is_fresh());
    }
}
