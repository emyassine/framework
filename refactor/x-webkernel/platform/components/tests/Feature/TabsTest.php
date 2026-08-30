<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Tabs;
use Webkernel\Platform\Components\TabsItem;
use Webkernel\Platform\Components\TabsPanel;
use Webkernel\View\View;

final class TabsTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    /**
     * @return void
     */
    public function test_tabs_view_and_class_render_the_same_markup(): void
    {
        $item = TabsItem::make()->tab('branding')->active()->slot('Branding')->render();
        $panel = TabsPanel::make()->tab('branding')->active()->slot('Body')->render();
        $html = Tabs::make()->label('Settings')->list($item)->slot($panel)->render();

        $this->assertStringContainsString('data-w-tabs', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('data-tab="branding"', $html);
        $this->assertStringContainsString('Branding', $html);
        $this->assertStringContainsString('Body', $html);
        $this->assertSame('webkernel::tabs', Tabs::make()->view());
        $this->assertSame('webkernel::tabs.item', TabsItem::make()->view());
        $this->assertSame('webkernel::tabs.panel', TabsPanel::make()->view());
    }
}
