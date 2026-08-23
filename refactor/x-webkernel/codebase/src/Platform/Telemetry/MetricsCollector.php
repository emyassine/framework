<?php declare(strict_types=1);

namespace Webkernel\Platform\Telemetry;

final class MetricsCollector
{
    /** @var array<string, int|float> */
    private array $counters = [];

    public function increment(string $name, int|float $by = 1): void
    {
        $this->counters[$name] = ($this->counters[$name] ?? 0) + $by;
        if (function_exists('apcu_inc')) {
            @apcu_inc('wk.metric.'.$name, (int) $by);
        }
    }

    /**
     * @return array<string, int|float>
     */
    public function counters(): array
    {
        return $this->counters;
    }
}
