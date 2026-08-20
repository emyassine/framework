<?php declare(strict_types=1);

require __DIR__.'/src/namespacer.php';

foreach (glob(__DIR__.'/functions/*.php') ?: [] as $file) {
    require $file;
}
