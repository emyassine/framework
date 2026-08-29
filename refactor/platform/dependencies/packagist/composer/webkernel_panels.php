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
    'href' => '/billing/invoices',
    'home_url' => '/billing/invoices',
    'label' => 'Billing',
    'icon' => 'receipt',
    'scope' => 'module',
    'default' => false,
    'pages' => 
    array (
      0 => 'Webkernel\\Platform\\Pages\\ManagePanel',
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
    'package' => 'acme/billing',
    'navigation' => 
    array (
      0 => 
      array (
        'label' => '',
        'items' => 
        array (
          0 => 
          array (
            'label' => 'Invoices',
            'href' => '/billing/invoices',
            'icon' => 'receipt',
          ),
        ),
      ),
      1 => 
      array (
        'label' => 'panel.settings',
        'icon' => 'folder',
        'items' => 
        array (
          0 => 
          array (
            'label' => 'panel.manage',
            'href' => '/billing/manage',
            'icon' => 'sliders',
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'id' => 'system',
    'path' => 'system',
    'href' => '/system',
    'home_url' => '/system',
    'label' => 'System',
    'icon' => 'settings',
    'scope' => 'platform',
    'default' => true,
    'pages' => 
    array (
      0 => 'Webkernel\\System\\Pages\\Dashboard',
      1 => 'Webkernel\\Platform\\Pages\\ManagePanel',
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
    'provider' => 'Webkernel\\System\\SystemPanelProvider',
    'package_provider' => 'Webkernel\\System\\SystemProvider',
    'prefix' => 'webkernel',
    'package' => 'webkernel/system',
    'navigation' => 
    array (
      0 => 
      array (
        'label' => '',
        'items' => 
        array (
          0 => 
          array (
            'label' => 'Overview',
            'href' => '/system',
            'icon' => 'layout-dashboard',
          ),
        ),
      ),
      1 => 
      array (
        'label' => 'panel.settings',
        'icon' => 'folder',
        'items' => 
        array (
          0 => 
          array (
            'label' => 'panel.manage',
            'href' => '/system/manage',
            'icon' => 'sliders',
          ),
        ),
      ),
    ),
  ),
);
