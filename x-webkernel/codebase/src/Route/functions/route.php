<?php declare(strict_types=1);

use Webkernel\Route\Route;

if (! function_exists('route')) {
    /**
     * Generate a URI for a named route.
     *
     * @param  array<string, string>  $parameters
     */
    function route(string $name, array $parameters = []): string
    {
        return Route::url($name, $parameters);
    }
}

if (! class_exists('Route', false)) {
    class_alias(Route::class, 'Route');
}
