<?php declare(strict_types=1);

use Webkernel\View\View;

if (! function_exists('view')) {
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $merge_data
     */
    function view(?string $view = null, array $data = [], array $merge_data = []): View|\Webkernel\View\Compiler
    {
        if ($view === null) {
            return View::compiler();
        }

        return View::make($view, $data, $merge_data);
    }
}

if (! function_exists('e')) {
    function e(mixed $value, bool $double_encode = true): string
    {
        if ($value instanceof \Stringable) {
            $value = (string) $value;
        } elseif ($value === null) {
            return '';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '';
        } elseif (is_scalar($value)) {
            $value = (string) $value;
        } else {
            $value = '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double_encode);
    }
}

if (! class_exists('View', false)) {
    class_alias(View::class, 'View');
}

if (! class_exists('Js', false)) {
    class_alias(\Webkernel\View\Js::class, 'Js');
}
