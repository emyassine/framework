<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Enums\IconPosition;
use Webkernel\Platform\Components\Tab;
use Webkernel\Platform\Components\Tabs;
use Webkernel\Platform\Components\TabsItem;
use Webkernel\Platform\Components\TabsPanel;
use Webkernel\Platform\Components\TextInput;
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

    /**
     * @return void
     */
    public function test_schema_tabs_match_filament_shape(): void
    {
        $html = Tabs::make('Tabs')
            ->tabs([
                Tab::make('Tab 1')->icon('bell')->schema([
                    TextInput::make('name')->label('Name'),
                ]),
                Tab::make('Notifications')
                    ->icon('bell')
                    ->icon_position(IconPosition::After)
                    ->badge(5)
                    ->badge_color('info')
                    ->schema([
                        TextInput::make('title')->label('Title'),
                    ]),
            ])
            ->active_tab(2)
            ->render();

        $this->assertStringContainsString('w-sc-tabs', $html);
        $this->assertStringContainsString('w-contained', $html);
        $this->assertStringContainsString('w-tabs-item-label', $html);
        $this->assertStringContainsString('Notifications', $html);
        $this->assertStringContainsString('w-badge', $html);
        $this->assertStringContainsString('>5</', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('w-active', $html);
        $this->assertStringContainsString('data-tab="notifications"', $html);
    }

    /**
     * @return void
     */
    public function test_contained_false_and_vertical(): void
    {
        $html = Tabs::make('Tabs')
            ->contained(false)
            ->vertical()
            ->scrollable(false)
            ->tabs([
                Tab::make('One')->schema([TextInput::make('a')->label('A')]),
                Tab::make('Two')->schema([TextInput::make('b')->label('B')]),
            ])
            ->render();

        $this->assertStringContainsString('w-vertical', $html);
        $this->assertStringContainsString('data-scrollable="false"', $html);
        $this->assertStringContainsString('data-w-tabs-overflow', $html);
        $this->assertStringNotContainsString('w-sc-tabs w-contained', $html);
    }
}
