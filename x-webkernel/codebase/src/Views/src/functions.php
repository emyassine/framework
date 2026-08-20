<?php declare(strict_types=1);

if (! function_exists('view')) {
    /**
     * Render a template to string.
     *
     * @param  array<string, mixed>  $data
     */
    function view(string $name, array $data = []): string
    {
        return \Webkernel\Views\Engine::render($name, $data);
    }
}

if (! function_exists('view_path')) {
    function view_path(string $path): void
    {
        \Webkernel\Views\Engine::add_path($path);
    }
}
