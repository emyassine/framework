<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

//> Autoload + class_alias only. Functions (webapp, view, webapp_path)
//> live in dumped webkernel_files.php, loaded after this file.

const WEBKERNEL_NS = 'Webkernel\\';
const WEBKERNEL_NS_LEN = 10;

/** @var array<string, class-string> */
const WEBKERNEL_CLASS_ALIAS = [
    'Config' => 'Webkernel\\Config\\Config',
    'Route' => 'Webkernel\\Route\\Route',
    'View' => 'Webkernel\\View\\View',
    'Js' => 'Webkernel\\View\\Js',
];

function webkernel_composer_dir(): ?string
{
    static $dir = false;
    if ($dir !== false) {
        return $dir;
    }
    if (! class_exists(\Composer\Autoload\ClassLoader::class, false)) {
        return $dir = null;
    }
    $file = (new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName();
    if (! is_string($file) || $file === '' || str_starts_with($file, 'phar://')) {
        return $dir = null;
    }

    return $dir = dirname($file);
}

function webkernel_autoload(string $class): bool
{
    static $hit = [];
    static $map = [];
    static $booted = false;
    static $base = '';

    if (isset($hit[$class])) {
        if ($hit[$class] === '') {
            return false;
        }
        require $hit[$class];

        return true;
    }

    foreach (WEBKERNEL_CLASS_ALIAS as $short => $fqcn) {
        if (strcasecmp($class, $short) !== 0) {
            continue;
        }
        if (! class_exists($fqcn, true)) {
            $hit[$class] = '';

            return false;
        }
        class_alias($fqcn, $class);

        return true;
    }

    if (! $booted) {
        $booted = true;
        $base = __DIR__.'/src/';
        $composer_dir = webkernel_composer_dir();
        if ($composer_dir !== null) {
            $file = $composer_dir.'/webkernel_classmap.php';
            if (is_file($file)) {
                $loaded = \function_exists('webkernel_include') ? \webkernel_include($file) : require $file;
                if (is_array($loaded)) {
                    $map = $loaded;
                }
            }
        }
    }

    $file = $map[$class] ?? null;
    if (is_string($file) && $file !== '' && is_file($file)) {
        $hit[$class] = $file;
        if (\function_exists('webkernel_include')) {
            \webkernel_include($file);
        } else {
            require $file;
        }

        return true;
    }

    if (strncmp($class, WEBKERNEL_NS, WEBKERNEL_NS_LEN) !== 0) {
        $hit[$class] = '';

        return false;
    }

    $rest = substr($class, WEBKERNEL_NS_LEN);
    $file = $base.str_replace('\\', '/', $rest).'.php';

    if (is_file($file)) {
        $hit[$class] = $file;
        if (\function_exists('webkernel_include')) {
            \webkernel_include($file);
        } else {
            require $file;
        }

        return true;
    }

    $hit[$class] = '';

    return false;
}

spl_autoload_register('webkernel_autoload');

$webkernel_composer_dir = webkernel_composer_dir();
if ($webkernel_composer_dir !== null) {
    $webkernel_files = $webkernel_composer_dir.'/webkernel_files.php';
    if (is_file($webkernel_files)) {
        if (\function_exists('webkernel_include')) {
            \webkernel_include($webkernel_files);
        } else {
            require $webkernel_files;
        }
    }
}
