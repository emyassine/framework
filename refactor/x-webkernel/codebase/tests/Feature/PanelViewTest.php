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

        $this->assertStringContainsString('w-rail', $html);
        $this->assertStringContainsString('w-rail-brand', $html);
        $this->assertStringContainsString('w-rail-logo-mark', $html);
        $this->assertStringContainsString('w-drawer', $html);
        $this->assertStringContainsString('w-nav-search', $html);
        $this->assertStringContainsString('w-header-heading', $html);
        $this->assertStringContainsString('/webapp.css', $html);
        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
        $this->assertStringContainsString('w-user-menu', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<title>Overview</title>', $html);
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('w-breadcrumbs', $html);
        $this->assertStringContainsString('w-topbar', $html);
    }

    /**
     * @return void
     */
    public function test_page_component_renders_slot_and_breadcrumbs(): void
    {
        $html = View::make('webkernel::page', [
            'title' => 'X',
            'header' => 'X',
            'breadcrumbs' => [
                ['label' => 'System', 'href' => '/system'],
                ['label' => 'Overview', 'href' => ''],
            ],
            'slot' => 'hi',
        ])->render();

        $this->assertStringContainsString('w-page', $html);
        $this->assertStringContainsString('hi', $html);
        $this->assertStringContainsString('w-breadcrumbs', $html);
        $this->assertStringContainsString('href="/system"', $html);
        $this->assertStringContainsString('Overview', $html);
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
        $this->assertStringContainsString('--gray-50: var(--color-zinc-50)', $css);
        $this->assertStringContainsString('--color-red-500:', $css);
        $this->assertStringContainsString('--color-mauve-50:', $css);
        $this->assertStringContainsString('--danger-50:', $css);
        $this->assertStringContainsString('--warning-50:', $css);
        $this->assertStringContainsString('--info-50:', $css);
        $dark = \Webkernel\Platform\Colors\Color::dark_root_css();
        $this->assertStringContainsString('--gray-50:', $dark);
        $this->assertStringNotContainsString('--primary-50:', $dark);
        $this->assertStringNotContainsString('--danger-50:', $dark);
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
    public function test_dumped_webapp_css_is_linked_and_contains_component_rules(): void
    {
        $path = \Webkernel\Platform\Assets::css_path();
        $this->assertFileExists($path);
        $css = (string) \file_get_contents($path);
        $this->assertStringContainsString('--primary-50:', $css);
        $this->assertStringContainsString('.w-btn', $css);
        $this->assertStringContainsString('.w-page', $css);
        $this->assertStringContainsString('.w-sc-tabs', $css);
        $this->assertStringContainsString('.w-tabs-item', $css);
        $this->assertSame(\substr_count($css, '('), \substr_count($css, ')'), 'dumped CSS var() parentheses must be balanced');
        $this->assertStringStartsWith('/webapp.css?v=', \Webkernel\Platform\Assets::css_href());
    }

    /**
     * @return void
     */
    public function test_css_minify_keeps_calc_and_color_mix(): void
    {
        $min = \Webkernel\Platform\Css::minify(
            '.x { top: calc(100% + 0.5rem); box-shadow: var(--w-shadow-sm), 0 0 0 1px color-mix(in srgb, var(--color-zinc-950) 5%, transparent); }',
        );
        $this->assertStringContainsString('calc(100% + 0.5rem)', $min);
        $this->assertStringContainsString('color-mix(in srgb,var(--color-zinc-950) 5%,transparent)', $min);
        $this->assertStringNotContainsString('calc(100%+0.5rem)', $min);
    }

    /**
     * @return void
     */
    public function test_billing_invoices_uses_webkernel_table(): void
    {
        $html = (new ListInvoices())->render();
        $this->assertStringContainsString('Invoices', $html);
        $this->assertStringContainsString('w-rail', $html);
        $this->assertStringContainsString('Create invoice', $html);
        $this->assertTrue(
            \str_contains($html, 'No invoices yet') || \str_contains($html, 'w-ta-table'),
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
