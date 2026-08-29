<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\View\View;

final class PanelViewTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    public function test_system_dashboard_uses_page_component_and_sidebar(): void
    {
        $html = View::make('webkernel::panels.system.dashboard')->render();

        $this->assertStringContainsString('webkernel-shell-sidebar', $html);
        $this->assertStringContainsString('webkernel-shell-page-title', $html);
        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
        $this->assertStringContainsString('webkernel-shell-user-menu', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<title>System</title>', $html);
    }

    public function test_page_component_renders_slot(): void
    {
        $html = View::make('webkernel::pages.home')->render();

        $this->assertStringContainsString('webkernel-shell-page', $html);
        $this->assertStringContainsString('Platform is working', $html);
    }
}
