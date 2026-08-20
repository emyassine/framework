<?php declare(strict_types=1);

/**
 * Host fast-boot: load Composer autoload (third_party or vendor).
 * CLI installs PHP dependencies when autoload is missing.
 */

$root = dirname(__DIR__);
$autoload = null;
foreach (['third_party/autoload.php', 'vendor/autoload.php'] as $rel) {
    $candidate = $root.'/'.$rel;
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    if (PHP_SAPI !== 'cli') {
        throw new \RuntimeException('Vendor autoload missing. Run: composer install');
    }
    passthru('composer install --no-interaction --working-dir='.escapeshellarg($root), $code);
    foreach (['third_party/autoload.php', 'vendor/autoload.php'] as $rel) {
        $candidate = $root.'/'.$rel;
        if (is_file($candidate)) {
            $autoload = $candidate;
            break;
        }
    }
    if ($code !== 0 || $autoload === null) {
        throw new \RuntimeException('Dependency installation failed. Run: composer install');
    }
}

require $autoload;
