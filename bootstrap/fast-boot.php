<?php declare(strict_types=1);

/**
 * Host fast-boot: load Composer autoload (third_party or vendor).
 * CLI installs PHP dependencies when autoload is missing.
 */

$root = dirname(__DIR__);
$composer_json = $root.'/composer.json';
if (! is_file($composer_json)) {
    throw new \RuntimeException('composer.json not found.');
}

/** @var array{config?: array{vendor-dir?: string}} $composer */
$composer = json_decode((string) file_get_contents($composer_json), true, 512, JSON_THROW_ON_ERROR);
$vendor = (string) (($composer['config']['vendor-dir'] ?? null) ?: 'vendor');
$autoload = $root.'/'.$vendor.'/autoload.php';

if (! is_file($autoload)) {
    if (PHP_SAPI !== 'cli') {
        throw new \RuntimeException('Vendor autoload missing. Run: composer install');
    }
    passthru('composer install --no-interaction --working-dir='.escapeshellarg($root), $code);
    if ($code !== 0 || ! is_file($autoload)) {
        throw new \RuntimeException('Dependency installation failed. Run: composer install');
    }
}

require $autoload;
