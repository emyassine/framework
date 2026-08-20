<?php declare(strict_types=1);
foreach (glob(__DIR__ . '/functions/*.php', GLOB_NOSORT) as $file) {
    require $file;
}
