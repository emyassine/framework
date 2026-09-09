<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Badge;
use Webkernel\Platform\Components\Breadcrumbs;
use Webkernel\Platform\Components\Callout;
use Webkernel\Platform\Components\EmptyState;
use Webkernel\Platform\Components\Input;
use Webkernel\Platform\Components\Textarea;
use Webkernel\View\View;

final class AtomsTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    /**
     * @return void
     */
    public function test_breadcrumbs_render_links_and_current(): void
    {
        $html = Breadcrumbs::make()
            ->items([
                ['label' => 'System', 'href' => '/system'],
                ['label' => 'Overview', 'href' => ''],
            ])
            ->render();

        $this->assertStringContainsString('w-breadcrumbs', $html);
        $this->assertStringContainsString('href="/system"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertSame('webkernel::breadcrumbs', Breadcrumbs::make()->view());
    }

    /**
     * @return void
     */
    public function test_badge_callout_empty_state_and_input_views(): void
    {
        $badge = Badge::make()->color('success')->slot('Paid')->render();
        $callout = Callout::make()->heading('Note')->color('warning')->slot('Body')->render();
        $empty = EmptyState::make()->heading('None')->icon('receipt')->render();
        $input = Input::make('email')->type('email')->render();

        $this->assertStringContainsString('w-badge', $badge);
        $this->assertStringContainsString('Paid', $badge);
        $this->assertStringContainsString('w-callout', $callout);
        $this->assertStringContainsString('w-empty-state', $empty);
        $this->assertStringContainsString('w-input', $input);
        $this->assertStringContainsString('type="email"', $input);
    }

    /**
     * @return void
     */
    public function test_textarea_matches_filament_structure(): void
    {
        $html = Textarea::make('body')->label('Body')->rows(4)->value('Hello')->render();

        $this->assertStringContainsString('w-fo-textarea', $html);
        $this->assertStringContainsString('w-fo-textarea-wrp', $html);
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="body"', $html);
        $this->assertStringContainsString('Hello', $html);
        $this->assertStringContainsString('rows="4"', $html);
        $this->assertSame('webkernel::textarea', Textarea::make()->view());
    }
}
