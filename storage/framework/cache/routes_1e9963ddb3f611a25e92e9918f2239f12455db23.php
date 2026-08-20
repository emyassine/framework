<?php declare(strict_types=1);

return array (
  0 => 
  array (
    'GET' => 
    array (
      '/v1/dashboard' => 
      array (
        0 => 
        array (
          0 => 'DashboardController',
          1 => 'show',
        ),
        1 => 
        array (
          '_route' => '/v1/dashboard/{tenant_id?}',
          '_name' => 'admin.dashboard',
        ),
      ),
      '/v1/overview' => 
      array (
        0 => 
        \Webkernel\Route\Action\ViewAction::__set_state(array(
           'view' => 'dashboard',
           'data' => 
          array (
            'title' => 'Webkernel — Dashboard Overview',
          ),
           'status' => 200,
        )),
        1 => 
        array (
          '_route' => '/v1/overview',
          '_view' => 'dashboard',
          '_name' => 'admin.overview',
        ),
      ),
    ),
    'HEAD' => 
    array (
      '/v1/dashboard' => 
      array (
        0 => 
        array (
          0 => 'DashboardController',
          1 => 'show',
        ),
        1 => 
        array (
          '_route' => '/v1/dashboard/{tenant_id?}',
          '_name' => 'admin.dashboard',
        ),
      ),
      '/v1/overview' => 
      array (
        0 => 
        \Webkernel\Route\Action\ViewAction::__set_state(array(
           'view' => 'dashboard',
           'data' => 
          array (
            'title' => 'Webkernel — Dashboard Overview',
          ),
           'status' => 200,
        )),
        1 => 
        array (
          '_route' => '/v1/overview',
          '_view' => 'dashboard',
          '_name' => 'admin.overview',
        ),
      ),
    ),
  ),
  1 => 
  array (
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/v1/dashboard/([0-9]+)(*MARK:a))$~',
        'routeMap' => 
        array (
          'a' => 
          array (
            0 => 
            array (
              0 => 'DashboardController',
              1 => 'show',
            ),
            1 => 
            array (
              'tenant_id' => 'tenant_id',
            ),
            2 => 
            array (
              '_route' => '/v1/dashboard/{tenant_id?}',
              '_name' => 'admin.dashboard',
            ),
          ),
        ),
      ),
    ),
    'HEAD' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/v1/dashboard/([0-9]+)(*MARK:a))$~',
        'routeMap' => 
        array (
          'a' => 
          array (
            0 => 
            array (
              0 => 'DashboardController',
              1 => 'show',
            ),
            1 => 
            array (
              'tenant_id' => 'tenant_id',
            ),
            2 => 
            array (
              '_route' => '/v1/dashboard/{tenant_id?}',
              '_name' => 'admin.dashboard',
            ),
          ),
        ),
      ),
    ),
  ),
);
