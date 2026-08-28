<?php declare(strict_types=1);

namespace Tests\Feature;

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

        $this->assertStringContainsString('webkernel-shell-sidebar', $html);
        $this->assertStringContainsString('webkernel-shell-page', $html);
    }
}
