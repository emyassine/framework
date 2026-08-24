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
  'instance_file_path' => 'platform/storage/instance',
  'created' => '2026-08-21T00:00:00+00:00',
  'autoload' => 'platform/dependencies/packagist/autoload.php',
  'platform' => 
  array (
    'path' => 'platform',
    'config_path' => 'config',
    'bootstrap_path' => 'platform/bootstrap',
    'cache_path' => 'platform/cache',
    'settings_path' => 'platform/settings',
    'storage_path' => 'platform/storage',
    'telemetry_path' => 'platform/telemetry',
    'temporary_path' => 'platform/temporary',
  ),
  'dependencies' => 
  array (
    'path' => 'platform/dependencies',
    'packagist_path' => 'platform/dependencies/packagist',
    'node_modules_path' => 'platform/dependencies/node_modules',
    'package_json' => 'platform/dependencies/package.json',
  ),
  'modules' => 
  array (
    'path' => 'modules',
    'manifest_path' => 'platform/temporary/modules_manifest.php',
  ),
  'public' => 
  array (
    'path' => 'public',
    'index' => 'public/index.php',
  ),
  'js' => 
  array (
    'manager' => 'npm',
    'package_json' => 'platform/dependencies/package.json',
    'node_modules_path' => 'platform/dependencies/node_modules',
  ),
  'telemetry' => 
  array (
    'enabled' => true,
    'logs_path' => 'platform/telemetry/logs',
  ),
);
