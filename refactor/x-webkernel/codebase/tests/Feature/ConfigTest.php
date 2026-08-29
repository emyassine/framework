<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;

final class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        Config::flush();
        Config::boot();
    }

    public function test_boot_reads_app_name(): void
    {
        $this->assertSame('Webkernel', Config::get('app.name'));
    }

    public function test_set_writes_runtime_and_reads_back(): void
    {
        $this->assertSame(1, Config::set('tests.probe', 1)->get('tests.probe'));
        $this->assertFileExists(\webapp_path('platform/platform-runtime.php'));
    }
}
