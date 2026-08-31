<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Instance;

/**
 * Host fingerprint: webapp path + machine identity.
 * Computed only by lifecycle (composer dump-autoload). Not on the request path.
 */
final class InstanceId
{
    public static function file_path(string $webapp_root): string
    {
        return \rtrim($webapp_root, '/\\').'/platform/storage/instance/data/instance_id';
    }

    public static function fingerprint(string $webapp_root): string
    {
        return self::parts($webapp_root)['fingerprint'];
    }

    public static function record(string $webapp_root): string
    {
        $id = self::fingerprint($webapp_root);
        $file = self::file_path($webapp_root);
        $dir = \dirname($file);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }
        \file_put_contents($file, $id, LOCK_EX);

        return $id;
    }

    public static function stored(string $webapp_root): ?string
    {
        $file = self::file_path($webapp_root);
        if (! \is_file($file)) {
            return null;
        }
        $id = \trim((string) \file_get_contents($file));

        return $id !== '' ? $id : null;
    }

    /**
     * @return array{path: string, ip: string, host: string, machine_uuid: string, macs: string, fingerprint: string}
     */
    public static function parts(string $webapp_root): array
    {
        $path = \rtrim(\str_replace('\\', '/', $webapp_root), '/');
        $host = (string) \gethostname();
        $ip = \gethostbyname($host);
        $machine_uuid = self::machine_uuid();
        $macs = self::macs();
        $base = $path.'|'.$ip.'|'.$host.'|'.$machine_uuid.'|'.$macs;

        return [
            'path' => $path,
            'ip' => $ip,
            'host' => $host,
            'machine_uuid' => $machine_uuid,
            'macs' => $macs,
            'fingerprint' => \substr(\hash('sha256', $base), 0, 32),
        ];
    }

    public static function macs(): string
    {
        $macs = [];
        $files = \glob('/sys/class/net/*/address');
        if ($files === false) {
            return '';
        }
        foreach ($files as $file) {
            $addr = @\file_get_contents($file);
            if ($addr !== false) {
                $macs[] = \trim($addr);
            }
        }

        return \implode(',', $macs);
    }

    public static function machine_uuid(): string
    {
        $uuid = @\file_get_contents('/sys/class/dmi/id/product_uuid');
        if (\is_string($uuid) && \trim($uuid) !== '') {
            return \trim($uuid);
        }
        $id = @\file_get_contents('/etc/machine-id');
        if (\is_string($id) && \trim($id) !== '') {
            return \trim($id);
        }

        return (string) \gethostname();
    }
}
