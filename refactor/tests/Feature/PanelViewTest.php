<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\View\View;

final class PanelViewTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
        View::share([
            'title' => 'Webkernel',
            'brand' => 'Webkernel',
            'theme' => 'dark',
        ]);
    }

    public function test_system_dashboard_uses_page_component_and_sidebar(): void
    {
        $html = View::make('webkernel::panels.system.dashboard')->render();

        $this->assertStringContainsString('wks-sidebar', $html);
        $this->assertStringContainsString('wks-page-title', $html);
        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
    }

    public function test_page_component_renders_slot(): void
    {
        $html = View::make('webkernel::pages.home')->render();

        $this->assertStringContainsString('wks-page', $html);
        $this->assertStringContainsString('Platform is working', $html);
    }
}
