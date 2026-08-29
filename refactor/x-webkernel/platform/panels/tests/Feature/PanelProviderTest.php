<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Composables\PanelComposable;
use Webkernel\Config\Config;
use Webkernel\Platform\PanelProvider;
use Webkernel\System\SystemPanelProvider;
use Webkernel\View\View;

final class PanelProviderTest extends TestCase
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
    public function test_scope_comes_from_webkernel_vendor_package(): void
    {
        $this->assertSame('platform', PanelProvider::scope_for_package('webkernel/panels'));
        $this->assertSame('platform', PanelProvider::scope_for_package('webkernel/foo', 'webkernel-stdlib'));
        $this->assertSame('module', PanelProvider::scope_for_package('acme/billing', 'webkernel-business-module'));
        $this->assertSame('platform', PanelProvider::scope_for_package('acme/core', 'webkernel-platform-module'));
    }

    /**
     * @return void
     */
    public function test_register_applies_branding_without_provider_calling_it(): void
    {
        $src = (string) \file_get_contents(
            \dirname(__DIR__, 4).'/system/src/SystemPanelProvider.php',
        );
        $this->assertStringNotContainsString('apply_platform_config', $src);
        $this->assertStringNotContainsString("scope('platform')", $src);

        $data = (new SystemPanelProvider())->register()->scope('platform')->to_array();
        $this->assertSame('system', $data['id']);
        $this->assertSame('settings', $data['icon']);
        $this->assertSame('System', $data['label']);
        $this->assertTrue($data['default']);
        $this->assertSame('/favicon.ico', $data['branding']['favicon']);
        $this->assertSame('platform', $data['scope']);
    }

    /**
     * @return void
     */
    public function test_dumped_panels_carry_navigation_and_inferred_scope(): void
    {
        $panels = (new PanelComposable())->all();
        $by_id = [];
        foreach ($panels as $panel) {
            $by_id[(string) $panel['id']] = $panel;
        }
        $this->assertArrayHasKey('system', $by_id);
        $this->assertArrayHasKey('billing', $by_id);
        $this->assertSame('platform', $by_id['system']['scope']);
        $this->assertSame('module', $by_id['billing']['scope']);
        $this->assertSame('settings', $by_id['system']['icon']);
        $this->assertSame('receipt', $by_id['billing']['icon']);
        $this->assertSame('/system', $by_id['system']['home_url']);
        $this->assertSame('/billing/invoices', $by_id['billing']['home_url']);
        $this->assertSame('/system', $by_id['system']['href']);
        $this->assertNotEmpty($by_id['system']['navigation']);
        $this->assertSame('Overview', $by_id['system']['navigation'][0]['items'][0]['label']);
        $this->assertSame('/system', $by_id['system']['navigation'][0]['items'][0]['href']);
        $this->assertSame('/billing/invoices', $by_id['billing']['navigation'][0]['items'][0]['href']);
    }
}
