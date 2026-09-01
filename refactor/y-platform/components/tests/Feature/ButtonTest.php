<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Button;
use Webkernel\Platform\Components\Enums\Size;
use Webkernel\View\View;

final class ButtonTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    /**
     * @return void
     */
    public function test_view_button_supports_color_size_icon_and_attributes(): void
    {
        $button = Button::make()
            ->color('danger')
            ->size('lg')
            ->outlined()
            ->icon('plus')
            ->slot('New')
            ->render(['class' => 'extra']);

        $this->assertStringContainsString('w-btn', $button);
        $this->assertStringContainsString('w-color-danger', $button);
        $this->assertStringContainsString('w-size-lg', $button);
        $this->assertStringContainsString('w-outlined', $button);
        $this->assertStringNotContainsString('title=', $button);
        $this->assertStringContainsString('New', $button);
        $this->assertStringContainsString('extra', $button);
        $this->assertStringContainsString('<button', $button);
    }

    /**
     * @return void
     */
    public function test_href_renders_an_anchor(): void
    {
        $html = Button::make()
            ->href('/billing/invoices')
            ->color('gray')
            ->slot('Cancel')
            ->render();

        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('href="/billing/invoices"', $html);
        $this->assertStringContainsString('Cancel', $html);
    }

    /**
     * @return void
     */
    public function test_size_enum_dumps_to_props(): void
    {
        $props = Button::make()->size(Size::Small)->to_props();
        $this->assertSame('sm', $props['size']);
    }

    /**
     * @return void
     */
    public function test_tooltip_is_an_html_attribute_not_title(): void
    {
        $html = Button::make()->tooltip('Save', 'bottom')->slot('Save')->render();
        $this->assertStringContainsString('x-tooltip="Save"', $html);
        $this->assertStringContainsString('x-tooltip-placement="bottom"', $html);
        $this->assertStringNotContainsString(' title=', $html);
    }

    /**
     * @return void
     */
    public function test_ghost_wires_the_ghost_class(): void
    {
        $html = Button::make()->ghost()->slot('Quiet')->render();
        $this->assertStringContainsString('w-ghost', $html);
    }
}
