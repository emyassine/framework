<?php declare(strict_types=1);

if (! function_exists('webapp')) {
    function webapp(): \Webkernel\WebApp
    {
        return \Webkernel\WebApp::get();
    }
}
