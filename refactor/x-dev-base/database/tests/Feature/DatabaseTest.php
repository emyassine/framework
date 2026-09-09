<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Database\Blueprint;
use Webkernel\Database\Database;
use Webkernel\Database\Driver;
use Webkernel\Database\Migrator;
use Webkernel\Database\Schema;

final class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        Database::flush();
        Database::connect(['driver' => 'sqlite', 'database' => ':memory:'], 'testing');
    }

    /**
     * @return void
     */
    public function test_sqlite_insert_select_and_schema(): void
    {
        Schema::create('widgets', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
        $id = Database::table('widgets')->insert(['name' => 'alpha']);
        $row = Database::table('widgets')->where('id', $id)->first();

        $this->assertTrue(Schema::has_table('widgets'));
        $this->assertGreaterThan(0, $id);
        $this->assertSame('alpha', $row['name'] ?? null);
        $this->assertSame(Driver::Sqlite, Database::driver());
    }

    /**
     * @return void
     */
    public function test_clickhouse_is_named_not_connected(): void
    {
        $this->expectException(\RuntimeException::class);
        Database::make(['driver' => 'clickhouse']);
    }

    /**
     * @return void
     */
    public function test_migrator_runs_a_file_once(): void
    {
        $dir = \sys_get_temp_dir().'/wk-migrate-'.\bin2hex(\random_bytes(4));
        \mkdir($dir);
        \file_put_contents($dir.'/0001_items.php', <<<'PHP'
<?php declare(strict_types=1);
use Webkernel\Database\Blueprint;
use Webkernel\Database\Migration;
use Webkernel\Database\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
    }
};
PHP);
        $migrator = new Migrator();
        $first = $migrator->run([$dir]);
        $second = $migrator->run([$dir]);
        \array_map(\unlink(...), \glob($dir.'/*.php') ?: []);
        \rmdir($dir);

        $this->assertSame(['0001_items'], $first);
        $this->assertSame([], $second);
        $this->assertTrue(Schema::has_table('items'));
    }
}
