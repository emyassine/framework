<?php declare(strict_types=1);

if (! function_exists('webkernel_branding_url')) {
    function webkernel_branding_url(string $key): string
    {
        return \Webkernel\Imagery\Branding::get()->url($key);
    }
}
