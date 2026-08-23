<?php declare(strict_types=1);

namespace Webkernel\Config;

/**
 * Global config helper function.
 * Retrieves configuration values from the compiled configuration store.
 */
function config(string $key = null, mixed $default = null): mixed
{
    return webapp()->config($key, $default);
}
