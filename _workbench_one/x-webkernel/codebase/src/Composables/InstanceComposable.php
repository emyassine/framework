<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Instance\InstanceId;

final class InstanceComposable
{
    public function machine_uuid(): string
    {
        $stored = webapp()->config('uuid');

        return is_string($stored) && $stored !== '' && ! str_starts_with($stored, 'xxxxxxxx')
            ? $stored
            : InstanceId::machine_uuid();
    }

    public function macs(): string
    {
        $stored = webapp()->config('macs');

        return is_string($stored) && $stored !== '' && $stored !== '00:00:00:00:00:00'
            ? $stored
            : InstanceId::macs();
    }

    public function file_path(): string
    {
        $stored = webapp()->config('instance_file_path');
        $rel = is_string($stored) && $stored !== '' ? $stored : 'platform/storage/instance';

        return webapp_path($rel);
    }

    public function fingerprint(): string
    {
        $stored = webapp()->config('id');
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return InstanceId::fingerprint(webapp_path());
    }

    /**
     * @return array{path: string, ip: string, host: string, machine_uuid: string, macs: string, fingerprint: string}
     */
    public function parts(): array
    {
        return InstanceId::parts(webapp_path());
    }
}
