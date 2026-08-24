<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Closure;
use Webkernel\Platform\Telemetry\AccessLogWriter;
use Webkernel\Platform\Telemetry\MetricsCollector;
use Webkernel\Platform\Telemetry\ProfileResult;

final class TelemetryComposable
{
    private ?AccessLogWriter $access_log = null;

    private ?MetricsCollector $metrics = null;

    public function access_log(): AccessLogWriter
    {
        return $this->access_log ??= new AccessLogWriter();
    }

    public function metrics(): MetricsCollector
    {
        return $this->metrics ??= new MetricsCollector();
    }

    public function profile(Closure $task): ProfileResult
    {
        $start = hrtime(true);
        $memory = memory_get_usage(true);
        $value = $task();

        return new ProfileResult(
            $value,
            hrtime(true) - $start,
            memory_get_usage(true) - $memory,
        );
    }
}
