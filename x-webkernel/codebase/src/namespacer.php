<?php declare(strict_types=1);

/**
 * Webkernel autoloader. Classmap + files from lifecycle dump
 * (vendor/composer/webkernel_classmap.php, webkernel_files.php).
 */
const WEBKERNEL_NS = 'Webkernel\\';
const WEBKERNEL_NS_LEN = 10;

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

    if (! $booted) {
        $booted = true;
        $base = __DIR__.'/';
        if (class_exists(\Composer\InstalledVersions::class)) {
            $installed = (new \ReflectionClass(\Composer\InstalledVersions::class))->getFileName();
            if (is_string($installed) && $installed !== '') {
                $file = dirname($installed).'/webkernel_classmap.php';
                if (is_file($file)) {
                    $loaded = require $file;
                    if (is_array($loaded)) {
                        $map = $loaded;
                    }
                }
            }
        }
    }

    $file = $map[$class] ?? null;
    if (is_string($file) && $file !== '' && is_file($file)) {
        $hit[$class] = $file;
        require $file;

        return true;
    }

    if (strncmp($class, WEBKERNEL_NS, WEBKERNEL_NS_LEN) !== 0) {
        $hit[$class] = '';

        return false;
    }

    $rest = substr($class, WEBKERNEL_NS_LEN);
    $slash = strpos($rest, '\\');
    if ($slash === false) {
        $file = $base.$rest.'.php';
    } else {
        $pkg = substr($rest, 0, $slash);
        $rel = str_replace('\\', '/', substr($rest, $slash + 1));
        $file = $base.$pkg.'/src/'.$rel.'.php';
    }

    if (is_file($file)) {
        $hit[$class] = $file;
        require $file;

        return true;
    }

    $hit[$class] = '';

    return false;
}

spl_autoload_register('webkernel_autoload');

if (class_exists(\Composer\InstalledVersions::class)) {
    $installed = (new \ReflectionClass(\Composer\InstalledVersions::class))->getFileName();
    if (is_string($installed) && $installed !== '' && ! str_starts_with($installed, 'phar://')) {
        $files = dirname($installed).'/webkernel_files.php';
        if (is_file($files)) {
            require $files;
        }
    }
}
