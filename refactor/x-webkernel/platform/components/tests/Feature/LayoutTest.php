<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Action;
use Webkernel\Platform\Components\Actions;
use Webkernel\Platform\Components\Alignment;
use Webkernel\Platform\Components\Fieldset;
use Webkernel\Platform\Components\Flex;
use Webkernel\Platform\Components\Grid;
use Webkernel\Platform\Components\Section;
use Webkernel\Platform\Components\TextInput;
use Webkernel\Platform\Components\Wizard;
use Webkernel\Platform\Components\WizardStep;
use Webkernel\View\View;

final class LayoutTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    /**
     * @return void
     */
    public function test_grid_columns_and_column_span(): void
    {
        $html = Grid::make()
            ->columns(['md' => 2, 'xl' => 4])
            ->schema([
                TextInput::make('name')->label('Name')->column_span(['md' => 2]),
                TextInput::make('bio')->label('Bio')->column_span_full(),
            ])
            ->render();

        $this->assertStringContainsString('w-grid', $html);
        $this->assertStringContainsString('--cols-md: 2', $html);
        $this->assertStringContainsString('--cols-xl: 4', $html);
        $this->assertStringContainsString('--col-span-md: 2', $html);
        $this->assertStringContainsString('w-span-full', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertSame('webkernel::grid', Grid::make()->view());
        $this->assertTrue(Grid::make()->has_columns());
    }

    /**
     * @return void
     */
    public function test_section_fieldset_and_flex(): void
    {
        $section = Section::make('Identity')->columns(2)->schema([
            TextInput::make('name')->label('Name'),
        ])->render();
        $fieldset = Fieldset::make('Address')->schema([
            TextInput::make('city')->label('City'),
        ])->render();
        $flex = Flex::make()
            ->from('md')
            ->schema([
                Section::make('Main')->schema([TextInput::make('title')->label('Title')]),
                Section::make('Side')->grow(false)->schema([TextInput::make('ok')->label('Ok')]),
            ])
            ->render();

        $this->assertStringContainsString('w-section', $section);
        $this->assertStringContainsString('Identity', $section);
        $this->assertStringContainsString('--cols-lg: 2', $section);
        $this->assertStringContainsString('w-fieldset', $fieldset);
        $this->assertStringContainsString('Address', $fieldset);
        $this->assertStringContainsString('w-flex', $flex);
        $this->assertStringContainsString('data-from="md"', $flex);
        $this->assertStringContainsString('w-flex-nogrow', $flex);
    }

    /**
     * @return void
     */
    public function test_wizard_and_actions_row(): void
    {
        $wizard = Wizard::make('Setup')
            ->schema([
                WizardStep::make('Details')->schema([
                    TextInput::make('name')->label('Name'),
                ]),
                WizardStep::make('Review')->description('Check')->schema([
                    TextInput::make('ok')->label('Ok'),
                ]),
            ])
            ->render();
        $actions = Actions::make()
            ->actions([
                Action::make('star')->icon('star')->label('Star'),
                Action::make('reset')->color('danger')->label('Reset'),
            ])
            ->alignment(Alignment::End)
            ->render();

        $this->assertStringContainsString('data-w-wizard', $wizard);
        $this->assertStringContainsString('Details', $wizard);
        $this->assertStringContainsString('Review', $wizard);
        $this->assertStringContainsString('name="name"', $wizard);
        $this->assertStringContainsString('w-actions', $actions);
        $this->assertStringContainsString('w-align-end', $actions);
        $this->assertStringContainsString('Star', $actions);
        $this->assertStringContainsString('Reset', $actions);
    }

    /**
     * @return void
     */
    public function test_action_submit_and_url(): void
    {
        $save = Action::make('save')->label('Save')->color('primary')->submit()->render();
        $edit = Action::make('edit')->url('/posts/1')->icon('pencil')->render();
        $hidden = Action::make('secret')->hidden()->render();

        $this->assertStringContainsString('type="submit"', $save);
        $this->assertStringContainsString('Save', $save);
        $this->assertStringContainsString('href="/posts/1"', $edit);
        $this->assertSame('', $hidden);
    }
}
