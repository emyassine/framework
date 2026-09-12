<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Models\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Database\Blueprint;
use Webkernel\Database\Database;
use Webkernel\Database\Schema;
use Webkernel\Models\Model;

final class Widget extends Model
{
    protected string $table = 'widgets';
}

final class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        Database::flush();
        Database::connect(['driver' => 'sqlite', 'database' => ':memory:'], 'testing');
        Schema::create('widgets', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
    }

    /**
     * @return void
     */
    public function test_create_find_update_delete(): void
    {
        $created = Widget::create(['name' => 'bolt']);
        $found = Widget::find((int) $created->id);
        $this->assertNotNull($found);
        $this->assertSame('bolt', $found->name);

        $found->name = 'nut';
        $found->save();
        $this->assertSame('nut', Widget::find((int) $found->id)?->name);

        $found->delete();
        $this->assertNull(Widget::find((int) $found->id));
        $this->assertSame([], Widget::all());
    }
}
