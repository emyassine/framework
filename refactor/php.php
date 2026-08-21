<?php

require __DIR__ . '/bootstrap/app.php';

use Webkernel\Instance\InstanceId;

echo InstanceId::machine_uuid() . PHP_EOL;
echo InstanceId::macs() . PHP_EOL;
echo InstanceId::filepath() . PHP_EOL;
