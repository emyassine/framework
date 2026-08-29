<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Route\Route;
use Webkernel\View\View;

final class HttpTest extends TestCase
{
    protected function setUp(): void
    {
        Config::flush();
        View::flush();
        Route::flush();
        Config::boot();
    }

    public function test_home_route_renders_panel_layout(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $html = (string) Route::dispatch();

        $this->assertStringContainsString('wds-sidebar', $html);
        $this->assertStringContainsString('wds-page', $html);
        $this->assertStringContainsString('<title>Home</title>', $html);
    }
}
