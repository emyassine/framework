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

/**
 * @method void assertStringContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertStringNotContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertTrue(bool $condition, string $message = '')
 * @method void assertFalse(bool $condition, string $message = '')
 * @method void assertEquals(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNotEquals(mixed $expected, mixed $actual, string $message = '')
 * @method void assertSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNotSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNull(mixed $actual, string $message = '')
 * @method void assertNotNull(mixed $actual, string $message = '')
 * @method void assertEmpty(mixed $actual, string $message = '')
 * @method void assertNotEmpty(mixed $actual, string $message = '')
 * @method void assertCount(int $expectedCount, Countable|array $haystack, string $message = '')
 * @method void assertInstanceOf(string $expectedClass, mixed $actual, string $message = '')
 * @method void assertNotInstanceOf(string $expectedClass, mixed $actual, string $message = '')
 * @method void assertContains(mixed $needle, iterable $haystack, string $message = '')
 * @method void assertNotContains(mixed $needle, iterable $haystack, string $message = '')
 * @method void assertGreaterThan(mixed $expected, mixed $actual, string $message = '')
 * @method void assertGreaterThanOrEqual(mixed $expected, mixed $actual, string $message = '')
 * @method void assertLessThan(mixed $expected, mixed $actual, string $message = '')
 * @method void assertLessThanOrEqual(mixed $expected, mixed $actual, string $message = '')
 */
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

        $this->assertStringContainsString('wds-sidebar', $html);
        $this->assertStringContainsString('wds-header-heading', $html);
        $this->assertStringContainsString('csrf-token', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('/wds.css', $html);
        $this->assertStringNotContainsString('--color-mauve-50', $html);
        $this->assertStringContainsString('System Admin Panel', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
        $this->assertStringContainsString('wds-user-menu', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<title>System</title>', $html);
    }

    public function test_page_component_renders_slot(): void
    {
        $html = View::make('webkernel::pages.home')->render();

        $this->assertStringContainsString('wds-page', $html);
        $this->assertStringContainsString('Platform is working', $html);
    }

    /**
     * @return void
     */
    public function test_page_csrf_is_on_by_default_and_disableable(): void
    {
        $on = View::make('webkernel::page', ['title' => 'X', 'slot' => 'hi'])->render();
        $this->assertStringContainsString('name="_token"', $on);

        $off = View::make('webkernel::page', ['title' => 'X', 'csrf' => false, 'slot' => 'hi'])->render();
        $this->assertStringNotContainsString('name="_token"', $off);
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
    }

    /**
     * @return void
     */
    /**
     * @return void
     */
    public function test_compiled_view_map_enables_fast_include(): void
    {
        $map = require vendor_dir('composer/webkernel_compiled_views.php');
        $this->assertIsArray($map);
        $this->assertArrayHasKey('webkernel::panels.system.dashboard', $map);
        $this->assertFileExists($map['webkernel::panels.system.dashboard']);
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
        $this->assertStringContainsString('.wds-sidebar', $css);
        $this->assertStringContainsString('.wds-btn', $css);
        $this->assertStringContainsString('.wds-page', $css);
        $this->assertStringStartsWith('/wds.css?v=', \Webkernel\Platform\Wds::css_href());
    }

    /**
     * @return void
     */
    public function test_engine_does_not_accumulate_pushes_across_renders(): void
    {
        $first = View::make('webkernel::panels.system.dashboard')->render();
        $second = View::make('webkernel::panels.system.dashboard')->render();
        $this->assertSame(\strlen($first), \strlen($second));
    }
}
