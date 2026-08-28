<?php declare(strict_types=1);

if (! function_exists('webterminal')) {
    function webterminal(): \Webkernel\Console\Terminal
    {
        static $terminal = null;

        return $terminal ??= new \Webkernel\Console\Terminal();
    }
}
