<?php declare(strict_types=1);

/**
 * Webkernel autoloader for codebase subpackages.
 *
 *   Webkernel\WebApp            → src/WebApp.php
 *   Webkernel\{Pkg}\{Rest}      → src/{Pkg}/src/{Rest}.php
 *
 * Hits are cached. Lifecycle may also write vendor/composer/webkernel_classmap.php.
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
                $cache = dirname($installed).'/webkernel_classmap.php';
                if (is_file($cache)) {
                    $loaded = require $cache;
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
