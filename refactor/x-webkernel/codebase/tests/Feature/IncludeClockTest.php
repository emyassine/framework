<?php declare(strict_types=1);

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Console\IncludeClock;

final class IncludeClockTest extends TestCase
{
    /**
     * @return void
     */
    public function test_run_file_records_real_execute_time(): void
    {
        $file = \tempnam(\sys_get_temp_dir(), 'includeclock');
        $this->assertNotFalse($file);
        \file_put_contents($file, "<?php declare(strict_types=1);\nusleep(2000);\nreturn 7;\n");
        IncludeClock::start();
        $value = IncludeClock::run_file($file);
        $stats = IncludeClock::stop();
        @\unlink($file);

        $this->assertSame(7, $value);
        $path = \str_replace('\\', '/', $file);
        $this->assertArrayHasKey($path, $stats);
        $this->assertGreaterThan(1.0, $stats[$path]['run_ms']);
        $this->assertSame(0.0, $stats[$path]['read_ms']);
    }

    /**
     * @return void
     */
    public function test_report_schema_is_stable(): void
    {
        IncludeClock::start();
        $stats = IncludeClock::stop();
        $report = IncludeClock::report($stats, ['Webkernel\\Http'], 123, 0.5);
        $this->assertSame(IncludeClock::SCHEMA, $report['schema']);
        $this->assertSame(['Webkernel\\Http'], $report['classes']);
        $this->assertSame(123, $report['mem']);
        $this->assertSame(0.5, $report['render_ms']);
        $this->assertIsArray($report['files']);
    }
}
