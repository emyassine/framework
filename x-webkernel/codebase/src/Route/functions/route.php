<?php declare(strict_types=1);

if (! function_exists('route')) {
    /**
     * Generate a URI for a named route.
     *
     * @param  array<string, string>  $parameters
     */
    function route(string $name, array $parameters = []): string
    {
        return \Webkernel\Route\Route::url($name, $parameters);
    }
}
