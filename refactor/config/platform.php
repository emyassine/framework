<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
// WARNING: Some keys in this file are written by the platform itself (see
// "platform-managed" comments). Do not edit those keys by hand — your changes
// will be overwritten on next boot if the platform detects a drift.

return array (
  'id' => 'bf0b7a6fe1dc0f33e62c091b9c7fe6e9',
  'hostname' => 'xdv',
  'ip' => '127.0.1.1',
  'uuid' => '18171914e030401586f89cae55151312',
  'macs' => '88:a4:c2:f3:ac:22,00:00:00:00:00:00,10:66:6a:00:00:00,d8:80:83:06:45:d5',
  'instance_file_path' => 'internal/storage/instance',
  'created' => '2026-08-21T00:00:00+00:00',
  'autoload' => 'internal/dependencies/packagist/autoload.php',
  'internal' =>
  array (
    'path' => 'internal',
    'config_path' => 'config',
    'bootstrap_path' => 'internal/bootstrap',
    'cache_path' => 'internal/cache',
    'settings_path' => 'internal/settings',
    'storage_path' => 'internal/storage',
    'telemetry_path' => 'internal/telemetry',
    'temporary_path' => 'internal/temporary',
  ),
  'dependencies' =>
  array (
    'path' => 'internal/dependencies',
    'packagist_path' => 'internal/dependencies/packagist',
    'node_modules_path' => 'internal/dependencies/node_modules',
    'package_json' => 'internal/dependencies/package.json',
  ),
  'modules' =>
  array (
    'path' => 'modules',
    'manifest_path' => 'internal/temporary/modules_manifest.php',
  ),
  'public' =>
  array (
    'path' => 'public',
    'index' => 'public/index.php',
  ),
  'js' =>
  array (
    'manager' => 'npm',
    'package_json' => 'internal/dependencies/package.json',
    'node_modules_path' => 'internal/dependencies/node_modules',
  ),
  'telemetry' =>
  array (
    'enabled' => true,
    'logs_path' => 'internal/telemetry/logs',
  ),
);
