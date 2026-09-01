<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Commands\DumpAutoloadCommand;

use Webkernel\Config\ConfigWriter;
use Webkernel\Instance\InstanceId;

trait CanStampPlatform
{
    use _DumpAutoloadCommand;

    private function stamp_platform_config(string $root, string $vendor_rel, string $instance_id): void
    {
        $config_path = $root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'platform.php';
        if (! is_file($config_path)) {
            return;
        }
        $current = require $config_path;
        if (! is_array($current)) {
            $current = [];
        }
        $parts = InstanceId::parts($root);
        $writes = [
            'hostname' => $parts['host'],
            'ip' => $parts['ip'],
            'uuid' => $parts['machine_uuid'],
            'macs' => $parts['macs'],
            'instance_file_path' => 'platform/storage/instance',
            'autoload' => $vendor_rel.'/autoload.php',
        ];
        $id = $current['id'] ?? null;
        if (! is_string($id) || $id === '') {
            $writes['id'] = $instance_id;
        }
        $created = $current['created'] ?? null;
        if (! is_string($created) || $created === '') {
            $writes['created'] = gmdate('c');
        }
        ConfigWriter::atomic_rewrite($config_path, $writes);
    }
}
