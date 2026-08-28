<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//> Generated. Do not edit.
//> Host moved? Run: composer dump-autoload

return array (
  0 => 
  array (
    'id' => 'billing',
    'path' => 'billing',
    'scope' => 'module',
    'default' => false,
    'pages' => 
    array (
    ),
    'widgets' => 
    array (
    ),
    'resources' => 
    array (
      0 => 'Acme\\Billing\\Presentation\\Resources\\Invoices\\InvoiceResource',
    ),
    'middleware' => 
    array (
    ),
    'auth_middleware' => 
    array (
    ),
    'branding' => 
    array (
      'favicon' => '/favicon.ico',
      'logo_light' => NULL,
      'logo_dark' => NULL,
      'logo_height' => '2rem',
      'colors' => 
      array (
        'primary' => 'blue',
      ),
      'dark_mode' => true,
    ),
    'provider' => 'Acme\\Billing\\Presentation\\BillingPanelProvider',
    'package_provider' => 'Acme\\Billing\\BillingProvider',
    'prefix' => 'billing',
  ),
  1 => 
  array (
    'id' => 'system',
    'path' => 'system',
    'scope' => 'platform',
    'default' => false,
    'pages' => 
    array (
      0 => 'Webkernel\\Platform\\Pages\\Dashboard',
    ),
    'widgets' => 
    array (
    ),
    'resources' => 
    array (
    ),
    'middleware' => 
    array (
    ),
    'auth_middleware' => 
    array (
      0 => 'Webkernel\\Platform\\Http\\Middleware\\Authenticate',
    ),
    'branding' => 
    array (
      'favicon' => '/favicon.ico',
      'logo_light' => NULL,
      'logo_dark' => NULL,
      'logo_height' => '2rem',
      'colors' => 
      array (
        'primary' => 'blue',
      ),
      'dark_mode' => true,
    ),
    'provider' => 'Webkernel\\Platform\\System\\SystemPanelProvider',
    'package_provider' => 'Webkernel\\CodebaseProvider',
    'prefix' => 'webkernel',
  ),
);
