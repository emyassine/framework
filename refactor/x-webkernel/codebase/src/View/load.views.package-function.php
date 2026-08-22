<?php declare(strict_types=1);

$files = glob(__DIR__.'/functions/*.php') ?: [];
sort($files, SORT_STRING);
foreach ($files as $file) {
    require $file;
}
