<?php
// webkernel/codebase/namespacer.php

// preload namespace map once
static $namespace_map = [
    'Webkernel\\'            => __DIR__ . '/Webkernel/',
//    'Webkernel\\Lifecycle\\' => __DIR__ . '/src/lifecycle/',
//    'Webkernel\\UI\\'        => __DIR__ . '/src/ui/',
];


function webkernel_autoload($class) {
    global $namespace_map;

    foreach ($namespace_map as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (is_file($file)) {
            require $file;
            return true;
        }
    }
    return false;
}

spl_autoload_register('webkernel_autoload');
