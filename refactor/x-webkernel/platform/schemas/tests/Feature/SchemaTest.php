<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Platform\Components\Action;
use Webkernel\Platform\Components\Grid;
use Webkernel\Platform\Components\Section;
use Webkernel\Platform\Components\TextInput;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Schemas\SchemaMode;
use Webkernel\View\View;

final class SchemaTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        View::flush();
    }

    /**
     * @return void
     */
    public function test_editable_and_readonly_are_the_same_tree(): void
    {
        $schema = (new Schema())->components([
            TextInput::make('email')->label('Email'),
        ]);

        $edit = $schema->render(['email' => 'a@b.c']);
        $this->assertStringContainsString('name="email"', $edit);
        $this->assertStringContainsString('value="a@b.c"', $edit);

        $view = (clone $schema)->mode(SchemaMode::Readonly)->render(['email' => 'a@b.c']);
        $this->assertStringContainsString('w-fo-field', $view);
        $this->assertStringContainsString('w-readonly', $view);
        $this->assertStringContainsString('a@b.c', $view);
        $this->assertStringNotContainsString('<input', $view);
    }

    /**
     * @return void
     */
    public function test_to_array_is_dumpable(): void
    {
        $dump = (new Schema())->components([
            TextInput::make('number')->label('Number'),
        ])->to_array();

        $this->assertSame('editable', $dump['mode']);
        $this->assertSame('webkernel::text-input', $dump['components'][0]['view']);
        $this->assertSame('number', $dump['components'][0]['props']['name']);
    }

    /**
     * @return void
     */
    public function test_form_wraps_csrf_actions_and_hidden_fields(): void
    {
        $html = Schema::make()
            ->components([
                TextInput::make('name')->label('Name'),
            ])
            ->form('/manage')
            ->hidden(['edit_locale' => 'en'])
            ->footer_actions([
                Action::make('save')->label('Save')->submit(),
            ])
            ->state(['name' => 'Acme'])
            ->render();

        $this->assertStringContainsString('id="w-schema"', $html);
        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="edit_locale"', $html);
        $this->assertStringContainsString('value="en"', $html);
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('Save', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('value="Acme"', $html);
        $this->assertStringContainsString('hx-post="/manage"', $html);
        $this->assertStringNotContainsString('hx-trigger="submit, change', $html);
    }

    /**
     * @return void
     */
    public function test_autosave_adds_change_trigger(): void
    {
        $html = Schema::make()
            ->components([TextInput::make('name')->label('Name')])
            ->form('/manage')
            ->autosave()
            ->render();

        $this->assertStringContainsString('change delay:400ms from:input', $html);
    }

    /**
     * @return void
     */
    public function test_nested_grid_and_section_render_fields(): void
    {
        $html = Schema::make()->components([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('name')->label('Name'),
                TextInput::make('title')->label('Title')->column_span_full(),
            ]),
            Grid::make()->columns(['md' => 2])->schema([
                TextInput::make('email')->label('Email'),
            ]),
        ])->state(['name' => 'A', 'title' => 'T', 'email' => 'a@b.c'])->render();

        $this->assertStringContainsString('w-section', $html);
        $this->assertStringContainsString('w-grid', $html);
        $this->assertStringContainsString('w-span-full', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('--cols-lg: 2', $html);
        $this->assertStringContainsString('--cols-md: 2', $html);
        $this->assertStringNotContainsString('<form', $html);
    }

    /**
     * @return void
     */
    public function test_get_fields_walks_nested_layouts(): void
    {
        $fields = Schema::make()->components([
            Grid::make()->columns(2)->schema([
                TextInput::make('email')->label('Email'),
            ]),
        ])->get_fields();

        $this->assertSame('email', $fields[0]['name']);
        $this->assertSame('Email', $fields[0]['label']);
    }
}
