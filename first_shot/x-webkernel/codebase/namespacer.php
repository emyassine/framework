<?php declare(strict_types=1);

/**
 * Webkernel autoloader. Classmap + dumped function files from lifecycle
 * (vendor/composer/webkernel_classmap.php, webkernel_files.php).
 *
 * Short names (Route, View, Js) alias on first use — class_alias at boot
 * would load the target class on every request.
 *
 * Path helpers stay dumped. Route/view composables load on webapp()->{name}().
 */
const WEBKERNEL_NS = 'Webkernel\\';
const WEBKERNEL_NS_LEN = 10;

/** @var array<string, class-string> */
const WEBKERNEL_CLASS_ALIAS = [
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
    if (! is_string($file) || $file === '') {
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
                $loaded = require $file;
                if (is_array($loaded)) {
                    $map = $loaded;
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

$webkernel_composer_dir = webkernel_composer_dir();
if ($webkernel_composer_dir !== null) {
    $webkernel_files = $webkernel_composer_dir.'/webkernel_files.php';
    if (is_file($webkernel_files)) {
        require $webkernel_files;
    }
}

if (! function_exists('webapp')) {
    function webapp(): \Webkernel\WebApp
    {
        return \Webkernel\WebApp::get();
    }
}
