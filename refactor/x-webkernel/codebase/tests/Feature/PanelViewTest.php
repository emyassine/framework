<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use Acme\Billing\Presentation\Resources\Invoices\Pages\ListInvoices;
use PHPUnit\Framework\TestCase;
use Webkernel\Composables\PanelComposable;
use Webkernel\Config\Config;
use Webkernel\System\Pages\Dashboard;
use Webkernel\View\View;

final class PanelViewTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
        PanelComposable::flush();
    }

    /**
     * @return void
     */
    public function test_system_dashboard_uses_page_component_and_rail(): void
    {
        $html = (new Dashboard())->render();

        $this->assertStringContainsString('wds-rail', $html);
        $this->assertStringContainsString('wds-drawer', $html);
        $this->assertStringContainsString('wds-nav-search', $html);
        $this->assertStringContainsString('wds-header-heading', $html);
        $this->assertStringContainsString('/wds.css', $html);
        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
        $this->assertStringContainsString('wds-user-menu', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<title>Overview</title>', $html);
        $this->assertStringContainsString('name="description"', $html);
    }

    /**
     * @return void
     */
    public function test_page_component_renders_slot(): void
    {
        $html = View::make('webkernel::pages.home')->render();

        $this->assertStringContainsString('wds-page', $html);
        $this->assertStringContainsString('Platform is working', $html);
    }

    /**
     * @return void
     */
    public function test_page_csrf_meta_is_on_by_default_and_disableable(): void
    {
        $on = View::make('webkernel::page', ['title' => 'X', 'slot' => 'hi'])->render();
        $this->assertStringContainsString('csrf-token', $on);

        $off = View::make('webkernel::page', ['title' => 'X', 'csrf' => false, 'slot' => 'hi'])->render();
        $this->assertStringNotContainsString('csrf-token', $off);
    }

    /**
     * @return void
     */
    public function test_providers_declare_view_namespaces_on_views(): void
    {
        $this->assertArrayHasKey('webkernel', \Webkernel\CodebaseProvider::VIEWS);
        $this->assertArrayHasKey('webkernel', \Webkernel\Platform\PanelsProvider::VIEWS);
        $this->assertArrayHasKey('webkernel', \Webkernel\Platform\Components\ComponentsProvider::VIEWS);
        $this->assertArrayHasKey('billing', \Acme\Billing\BillingProvider::VIEWS);
        $this->assertArrayHasKey('billing', \Acme\Billing\BillingProvider::COMPONENTS);
        $this->assertNotEmpty(\Acme\Billing\BillingProvider::LANG_PATH);
    }

    /**
     * @return void
     */
    public function test_color_root_css_exposes_primary_stops(): void
    {
        $css = \Webkernel\Platform\Colors\Color::root_css();
        $this->assertStringContainsString('--primary-50:', $css);
        $this->assertStringContainsString('--primary-950:', $css);
        $this->assertStringContainsString('--color-red-500:', $css);
        $this->assertStringContainsString('--color-mauve-50:', $css);
        $this->assertStringContainsString('--danger-50:', $css);
        $this->assertStringContainsString('--warning-50:', $css);
        $this->assertStringContainsString('--info-50:', $css);
    }

    /**
     * @return void
     */
    public function test_compiled_view_map_enables_fast_include(): void
    {
        $map = require vendor_dir('composer/webkernel_compiled_views.php');
        $this->assertIsArray($map);
        $this->assertArrayHasKey('webkernel::page', $map);
        $this->assertFileExists($map['webkernel::page']);
    }

    /**
     * @return void
     */
    public function test_dumped_wds_css_is_linked_and_contains_chrome(): void
    {
        $path = \Webkernel\Platform\Wds::css_path();
        $this->assertFileExists($path);
        $css = (string) \file_get_contents($path);
        $this->assertStringContainsString('--primary-50:', $css);
        $this->assertStringContainsString('.wds-btn', $css);
        $this->assertStringContainsString('.wds-page', $css);
        $this->assertStringStartsWith('/wds.css?v=', \Webkernel\Platform\Wds::css_href());
    }

    /**
     * @return void
     */
    public function test_billing_invoices_uses_webkernel_table(): void
    {
        $html = (new ListInvoices())->render();
        $this->assertStringContainsString('Invoices', $html);
        $this->assertStringContainsString('wds-rail', $html);
        $this->assertStringContainsString('Create invoice', $html);
        $this->assertTrue(
            \str_contains($html, 'No invoices yet') || \str_contains($html, 'wds-ta-table'),
            'invoices page must render the empty state or the Webkernel table',
        );
    }

    /**
     * @return void
     */
    public function test_engine_does_not_accumulate_pushes_across_renders(): void
    {
        $first = (new Dashboard())->render();
        $second = (new Dashboard())->render();
        $this->assertSame(\strlen($first), \strlen($second));
    }
}
