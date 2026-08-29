<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
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
        $this->assertStringContainsString('wds-fo-field', $view);
        $this->assertStringContainsString('wds-readonly', $view);
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
}
