<?php declare(strict_types=1);

if (! function_exists('webkernel_instance_id')) {
    function webkernel_instance_id(): string
    {
        return (string) (webkernel_boot()['instance_id'] ?? '');
    }
}
