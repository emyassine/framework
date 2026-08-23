<?php declare(strict_types=1);

namespace Webkernel\Platform\Telemetry;

/**
 * Appends JSON access lines under platform/telemetry/logs/access/.
 */
final class AccessLogWriter
{
    public function write(string $line): void
    {
        $rel = webapp()->config('telemetry.logs_path', 'platform/telemetry/logs');
        $dir = webapp_path(is_string($rel) ? $rel : 'platform/telemetry/logs').'/access';
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return;
        }
        file_put_contents($dir.'/access-'.gmdate('Y-m-d').'.log', $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
